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

class ProjectController extends AdminBaseController
{
    /**
     * 工程
     */
    public function index()
    {
        $project = Db::name('Project')->order('create_time desc')->paginate(10);
        $page = $project->render();
        $this->assign("data",$project->toArray()['data']);
        $this->assign('project', $project);
        $this->assign('page', $page);
        return $this->fetch();
    }

    /**
     * 添加工程
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'Project');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data = $this->request->param();
                Db::name('Project')->insert([
                    "name"   => $data['name'],
                    "salesman" => $data['salesman'],
                    "create_time"  => time(),
                ]);
                addLogs("管理员添加","添加工程 {$data['name']} 成功");
                $this->success("添加成功");
            }
        }
    }

    /**
     * 编辑工程
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'Project');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data = $this->request->param();
                Db::name('Project')->where(['id' => $data['id']])->update([
                    "name"   => $data['name'],
                    "salesman" => $data['salesman'],
                ]);
                addLogs("管理员编辑","编辑工程 {$data['name']} 成功");
                $this->success("更新成功");
            }
        }
    }

    /**
     * 删除工程
     */
    public function delete()
    {
        $id = $this->request->param("id", 0, 'intval');
        $project = Db::name('Project')->find($id);
        if (Db::name('Project')->delete($id) !== false) {
            addLogs("管理员删除","删除工程 {$project['name']} 成功");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     * 批量删除工程
     */
    public function deletePost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $list_array = $data['list'];
            if(empty($list_array) || !is_array($list_array)){
                $this->error("请选择至少一条工程信息");
            }
            foreach ($list_array as $v){
                Db::name('Project')->delete($v);
            }
            addLogs("管理员删除","删除".count($list_array)."条工程成功");

            $this->success("删除成功");
        }
    }
}