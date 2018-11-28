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

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class UserController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   => '操作员管理',
 *     'action' => 'default',
 *     'parent' => 'user/System/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   => '',
 *     'remark' => '管理组'
 * )
 */
class UserController extends AdminBaseController
{

    /**
     * 管理员列表
     * @adminMenu(
     *     'name'   => '管理员列表',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员管理',
     *     'param'  => ''
     * )
     * @throws
     */
    public function index()
    {
        $content = hook_one('admin_user_index_view');

        if (!empty($content)) {
            return $content;
        }
        $param = $this->request->param();

        $where = ["user_type" => 1];
        /**搜索条件**/
        $keyword = trim($this->request->param('keyword'));
        if ($keyword) {
            $where['u.user_login|u.user_name'] = ['like', "%$keyword%"];
        }
        $role_id = input('request.role_id',0,'intval');
        if($role_id){
            $where['ru.role_id'] = array('eq',$role_id);
        }

        $filedName = "u.id,u.user_name,u.user_login,rl.name role_name,ru.role_id";

        $users = Db::name('user')->alias("u")
            ->field($filedName)
            ->join("role_user ru",'ru.user_id = u.id')
            ->join("role rl",'ru.role_id = rl.id')
            ->where($where)
            ->order("id DESC")
            ->paginate(10);
        $users->appends($param);

        $role = Db::name('role')->where(['status' => 1])->order("id DESC")->select();
        $roles    = [];
        foreach ($role as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }

        $this->assign("page", $users->render());
        $this->assign("roles", $roles);
        $this->assign("role", $role);
        $this->assign("users", $users);
        $this->assign("role_id",$role_id);
        $this->assign("data",$users->toArray()['data']);
        return $this->fetch();
    }



    /**
     * 管理员添加提交
     * @adminMenu(
     *     'name'   => '管理员添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            if (!empty($_POST['role_id'])  ) {
                $role_id = $_POST['role_id'];
                unset($_POST['role_id']);

                //设置为1 为管理员身份
                $_POST['user_type']=1;

                $result = $this->validate($this->request->param(), 'User');
                if ($result !== true) {
                    $this->error($result);
                } else {

                    if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                        $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                    }

                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                    $_POST['create_time']=time();

                    $result    = DB::name('user')->insertGetId($_POST);

                    if ($result !== false) {

                        Db::name('RoleUser')->insert(["role_id" => $role_id, "user_id" => $result]);
                        $this->success("添加成功！", url("user/index"));

                    } else {
                        $this->error("添加失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }



    /**
     * 管理员编辑提交
     * @adminMenu(
     *     'name'   => '管理员编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑提交',
     *     'param'  => ''
     * )
     * @throws
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            if (!empty($_POST['role_id']) ) {
                if (empty($_POST['user_pass'])) {
                    unset($_POST['user_pass']);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                }
                $role_id = $this->request->param('role_id');
                unset($_POST['role_id']);
                $result = $this->validate($this->request->param(), 'User.edit');

                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    $result = DB::name('user')->update($_POST);
                    if ($result !== false) {

                        $uid = $this->request->param('id', 0, 'intval');

                       // DB::name("RoleUser")->where(["user_id" => $uid])->delete();

                        if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                            $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                        }
                        //更新数据增到权限关联表
                        DB::name("RoleUser")->where([ "user_id" => $uid])->update(["role_id" => $role_id]);
                        $this->success("保存成功！");
                    } else {
                        $this->error("保存失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
        $uid = $this->request->param('id', 0, 'intval');
        $data=  model('user')->get($uid);
        $data['role_id'] = model("role_user")->where('user_id',$uid)->value("role_id");
        return $data;
    }

    /**
     * 管理员个人信息修改
     * @ adminMenu(
     *     'name'   => '个人信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改',
     *     'param'  => ''
     * )
     */
    public function userInfo()
    {
        $id   = cmf_get_current_admin_id();
        $user = Db::name('user')->where(["id" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员个人信息修改提交
     * @adminMenu(
     *     'name'   => '管理员个人信息修改提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改提交',
     *     'param'  => ''
     * )
     */
    public function userInfoPost()
    {
        if ($this->request->isPost()) {

            $data             = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['id']       = cmf_get_current_admin_id();
            $create_result    = Db::name('user')->update($data);;
            if ($create_result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    /**
     * 管理员删除
     * @adminMenu(
     *     'name'   => '管理员多选删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员删除',
     *     'param'  => ''
     * )
     */
    public function delMultiple()
    {
        $id = $this->request->param('id', "", 'string');
        if (in_array(1,explode(",",$id))) {
            $this->error("最高管理员不能删除！");
        }

        if ($len  = model('user')->where('id','in',$id)->delete() !== false) {
            Db::name("RoleUser")->where("user_id" ,"in", $id)->delete();
            $this->success("删除成功！",url("user/index"));
        } else {
            $this->error("删除失败！");
        }
    }

    public function resetPass(){
        $id = $this->request->param('id', "", 'string');
        $pass = cmf_password("123456");
        if ($len  = model('user')->where('id','in',$id)->update(['user_pass'=>$pass]) !== false) {
            $this->success("重置密码成功！",url("user/index"));
        } else {
            $this->error("重置密码失败！");
        }
    }



    /**
     * 停用管理员
     * @adminMenu(
     *     'name'   => '停用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '停用管理员',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '0');
            if ($result !== false) {
                $this->success("管理员停用成功！", url("user/index"));
            } else {
                $this->error('管理员停用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 启用管理员
     * @adminMenu(
     *     'name'   => '启用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用管理员',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '1');
            if ($result !== false) {
                $this->success("管理员启用成功！", url("user/index"));
            } else {
                $this->error('管理员启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }
}