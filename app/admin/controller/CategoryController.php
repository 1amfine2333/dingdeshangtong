<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\AdminMenuModel;
use cmf\controller\AdminBaseController;
use think\Db;
use tree\Tree;
use mindplay\annotations\Annotations;

class CategoryController extends AdminBaseController
{
    /**
     * 类目管理
     */
    public function index()
    {
        $result     = Db::name('Category')->order(["create_time" => "ASC"])->select()->toArray();
        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $newMenus = [];
        foreach ($result as $m) {
            $newMenus[$m['id']] = $m;
        }
        $first = array();
        foreach ($result as $key => $value) {

            $result[$key]['parent_id_node'] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
            $result[$key]['style']          = empty($value['parent_id']) ? '' : 'display:none;';
            if($value['parent_id'] == 0) {
                $first[] = $value;
                $result[$key]['str_manage'] = '<a data-id="'.$value['id'].'" class="addChild" href="#">添加子分类</a>';
            }else{
                $result[$key]['name'] = '&nbsp;&nbsp;&nbsp;'.$value['name'];
                $result[$key]['str_manage'] = '<a data-index="'.$value['parent_id'].'" data-id="'.$value['id'].'" data-name="'.$value['name'].'" class="editCate" href="#">' . lang('EDIT') . '</a>  <a class="js-ajax-delete" href="' . url("Category/delete", ["id" => $value['id']]) . '">' . lang('DELETE') . '</a> ';
            }
        }

        $tree->init($result);
        $str      = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                        <td>\$name</td>
                        <td style='text-align: center !important;'>\$str_manage</td>
                    </tr>";
        $category = $tree->getTree(0, $str);
        $this->assign("category", $category);
        $this->assign("first",json_encode($first));
        return $this->fetch();
    }

    /**
     * 添加分类
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'Category');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data = $this->request->param();
                $parent = Db::name('Category')->where(['id' => $data['parent_id']])->find();
                if(!$parent){
                    $this->error("不存在该一级分类");
                }
                $category = Db::name('Category')->where(array('parent_id'=>$parent['id'],'name'=>$data['name']))->find();
                if($category){
                    $this->error("分类名称已经存在");
                }
                Db::name('Category')->insert([
                    "parent_id"  => $parent['id'],
                    "name"   => $data['name'],
                    "create_time"  => time(),
                    "type" => $parent['type'],
                ]);
                addLogs("管理员添加","添加类目 {$data['name']} 成功");
                $this->success("添加成功");
            }
        }
    }

    /**
     * 编辑分类
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'Category');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data = $this->request->param();
                $parent = Db::name('Category')->where(['id' => $data['parent_id']])->find();
                if(!$parent){
                    $this->error("不存在该一级分类");
                }
                $where['id'] = array('neq',$data['id']);
                $where['parent_id'] = array('eq',$parent['id']);
                $where['name'] = array('eq',$data['name']);
                $category = Db::name('Category')->where($where)->find();
                if($category){
                    $this->error("分类名称已经存在");
                }
                Db::name('Category')->where(['id' => $data['id']])->update([
                    "parent_id"  => $parent['id'],
                    "name"   => $data['name'],
                    "type" => $parent['type'],
                ]);
                addLogs("管理员修改","编辑类目 {$data['name']} 成功");
                $this->success("更新成功");
            }
        }
    }

    /**
     * 删除分类
     */
    public function delete()
    {
        $id = $this->request->param("id", 0, 'intval');
        $category = Db::name('Category')->find($id);
        if (Db::name('Category')->delete($id) !== false) {
            addLogs("管理员删除","删除类目 {$category['name']} 成功");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}