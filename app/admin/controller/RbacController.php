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

use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;
use tree\Tree;
use app\admin\model\AdminMenuModel;

class RbacController extends AdminBaseController
{

    /**
     * 角色管理列表
     * @adminMenu(
     *     'name'   => '角色管理',
     *     'parent' => 'admin/User/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('admin_rbac_index_view');

        if (!empty($content)) {
            return $content;
        }
        $param = $this->request->param();

        $where = array();
        $keyword = input('request.keyword');
        if($keyword){
            $where['name'] = array('like',"%$keyword%");
        }

        $data = Db::name('role')->where($where)->order(["list_order" => "ASC", "id" => "DESC"])->paginate(10);
        $data->appends($param);

        $this->assign("roles", $data);
        $this->assign('page', $data->render());
        $this->assign("keyword",$keyword);
        $this->assign("data",$data->toArray()['data']);
        return $this->fetch();
    }



    /**
     * 添加角色提交
     * @adminMenu(
     *     'name'   => '添加角色提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加角色提交',
     *     'param'  => ''
     * )
     * @throws
     */
    public function roleAddPost()
    {

        $data = $this->request->param();
        if ($this->request->isPost()) {
            $user_login = UserModel::get(cmf_get_current_admin_id())->user_login;
            $result = $this->validate($data, 'role.add');

            if($result !== true) {
                $this->error($result);
            }else {

                if (sizeof(input("menuId/a"))<1){
                    $this->error("请至少选择一项权限");
                }

                $roleId = Db::name("role")->insertGetId([
                    'name' => $this->request->param("name")
                    , 'status' => 1
                    , 'create_time' => time()
                    , 'user_login' => $user_login
                ]);
                foreach (input("menuId/a") as $menuId) {
                    $menu = Db::name("adminMenu")->where(["id" => $menuId])->field("app,controller,action")->find();
                    if ($menu) {
                        $app = $menu['app'];
                        $model = $menu['controller'];
                        $action = $menu['action'];
                        $name = strtolower("$app/$model/$action");
                        Db::name("authAccess")->insert(["role_id" => $roleId, "rule_name" => $name, 'type' => 'admin_url']);
                    }
                }

                if ($result) {
                    addLogs("管理员添加","添加角色 {$data['name']} 成功");
                    $this->success("添加角色成功", url("rbac/index"));
                } else {
                    $this->error("添加角色失败");
                }
            }

        }
    }



    /**
     * 删除角色
     * @adminMenu(
     *     'name'   => '删除角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除角色',
     *     'param'  => ''
     * )
     */
    public function roleDelete()
    {
        $id = $this->request->param("id", '', 'string');
        if (in_array(1,explode(",",$id))) {
            $this->error("超级管理员角色不能被删除！");
        }
        $count = Db::name('RoleUser')->where('role_id' ,"in", $id)->count();
        if ($count > 0) {
            $this->error("所选角色中已有用户绑定");
        } else {
            $status = Db::name('role')->where('id' ,"in", $id)->delete();
            if (!empty($status)) {
                addLogs("管理员删除","删除 {$status} 条角色成功");
                $this->success("删除成功！", url('rbac/index'));
            } else {
                $this->error("删除失败！");
            }
        }
    }



    /**
     * 设置角色权限Json
     * @adminMenu(
     *     'name'   => '设置角色权限',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色权限',
     *     'param'  => ''
     * )
     */
    public function authorize_json()
    {
        $AuthAccess     = Db::name("AuthAccess");
        $adminMenuModel = new AdminMenuModel();
        $role_name = '';
        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId) && $roleId != 0) {
            $this->error("参数错误！");
        }
        if($roleId > 0){
            $role = Db::name('Role')->where(array('id'=>$roleId))->find();
            if($role){
                $role_name = $role['name'];
            }
        }

        $tree       = new Tree();
        $tree->icon = ['│ ', '├─ ', '└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $result = $adminMenuModel->menuCache();

        $newMenus      = [];
        $privilegeData = $AuthAccess->where(["role_id" => $roleId])->column("rule_name");//获取权限表数据

        foreach ($result as $m) {
            $newMenus[$m['id']] = $m;
        }

        foreach ($result as $n => $t) {
            $result[$n]['checked']      = ($this->_isChecked($t, $privilegeData)) ? ' checked' : '';
            $result[$n]['level']        = $this->_getLevel($t['id'], $newMenus);
            $result[$n]['style']        = empty($t['parent_id']) ? '' : 'display:none;';
            $result[$n]['parentIdNode'] = ($t['parent_id']) ? ' class="child-of-node-' . $t['parent_id'] . '"' : '';
        }

        $str = "<tr id='node-\$id'\$parentIdNode  style='\$style'>
                   <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuId[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name</td>
    			</tr>";
        $tree->init($result);

        $category = $tree->getTree(0, $str);

        return json(["html"=>$category,'name'=>$role_name]);
    }


    /**
     * 角色修改授权提交
     * @adminMenu(
     *     'name'   => '角色修改授权提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色修改授权提交',
     *     'param'  => ''
     * )
     */
    public function authorizePost()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->param("id", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
            }
            $data = $this->request->param();
            $result = $this->validate($data, 'role.add');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            }

            if (is_array($this->request->param('menuId/a')) && count($this->request->param('menuId/a')) > 0) {
                Db::name("role")->where("id",$roleId)->update(['name'=>$data['name'],'update_time'=>time()]);
                Db::name("authAccess")->where(["role_id" => $roleId, 'type' => 'admin_url'])->delete();
                foreach ($_POST['menuId'] as $menuId) {
                    $menu = Db::name("adminMenu")->where(["id" => $menuId])->field("app,controller,action")->find();
                    if ($menu) {
                        $app    = $menu['app'];
                        $model  = $menu['controller'];
                        $action = $menu['action'];
                        $name   = strtolower("$app/$model/$action");
                        Db::name("authAccess")->insert(["role_id" => $roleId, "rule_name" => $name, 'type' => 'admin_url']);
                    }
                }
                cache(null, 'admin_menus');// 删除后台菜单缓存
                addLogs("管理员编辑","编辑 {$data['name']} 角色权限成功");
                $this->success("授权成功！");
            } else {
                //当没有数据时，清除当前角色授权
               // Db::name("authAccess")->where(["role_id" => $roleId])->delete();
                $this->error("请至少选择一项权限");
            }
        }
    }

    /**
     * 检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param $privData
     * @return bool
     */
    private function _isChecked($menu, $privData)
    {
        $app    = $menu['app'];
        $model  = $menu['controller'];
        $action = $menu['action'];
        $name   = strtolower("$app/$model/$action");
        if ($privData) {
            if (in_array($name, $privData)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * 获取菜单深度
     * @param $id
     * @param array $array
     * @param int $i
     * @return int
     */
    protected function _getLevel($id, $array = [], $i = 0)
    {
        if ($array[$id]['parent_id'] == 0 || empty($array[$array[$id]['parent_id']]) || $array[$id]['parent_id'] == $id) {
            return $i;
        } else {
            $i++;
            return $this->_getLevel($array[$id]['parent_id'], $array, $i);
        }
    }

    //角色成员管理
    public function member()
    {
        //TODO 添加角色成员管理

    }

}

