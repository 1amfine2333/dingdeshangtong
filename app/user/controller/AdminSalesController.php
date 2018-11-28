<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\user\controller;

use app\user\model\PlanOrderModel;
use app\user\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )

 */
class AdminSalesController extends AdminBaseController
{

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '销售经理',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '销售经理',
     *     'param'  => ''
     * )
     * @throws
     */
    public function index()
    {
        $content = hook_one('user_admin_index_view');

        if (!empty($content)) {
            return $content;
        }
        $param = $this->request->param();

        $where   = ['user_type'=>3];
        $request = input('request.');

        //时间段查询
        $startTime = empty($request['start_time']) ? 0 : strtotime($request['start_time']);
        $endTime   = empty($request['end_time']) ? 0 : strtotime($request['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['create_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['create_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['create_time'] = ['<= time', $endTime];
            }
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['user_nickname|mobile']    =   ['like', "%$keyword%"];
        }


        $plan = new PlanOrderModel();
        $list = Db::name('user')
           ->field('id,create_time,user_nickname,mobile,user_name,user_status')
            ->whereOr($keywordComplex)
            ->where($where)
            ->order("create_time DESC")
            ->paginate(10);
        foreach ($list as $k=>$v){
            $v['success']= $plan->where(['manager_id'=>$v['id']  ,'status'=>2])->count();
            $v['refuse']= $plan->where(['manager_id'=>$v['id']  ,'status'=>3])->count();
            $v['cancel']= $plan->where(['manager_id'=>$v['id']  ,'status'=>4])->count();
            $list[$k] = $v;
        }
        $list->appends($param);

        $this->assign("data",$list->toArray()['data']);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('start_time', isset($request['start_time']) ? $request['start_time'] : '');
        $this->assign('end_time', isset($request['end_time']) ? $request['end_time'] : '');
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     * 销售经理详情
     * @adminMenu(
     *     'name'   => '销售经理详情',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '销售经理详情',
     *     'param'  => ''
     * )
     * @throws
     */
    public function info()
    {

        $w = [];
        $id = intval(input("id"));
        $type = input("type")?:2;

        $user = new UserModel();
        $plan = new PlanOrderModel();

        $w['status']=$type;
        $list = $plan->where('manager_id',$id)->where($w)->order("create_time DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();

        $count=[
            'success'=>$plan->where(['manager_id'=>$id  ,'status'=>2])->count(),
            'refuse'=>$plan->where(['manager_id'=>$id  ,'status'=>3])->count(),
            'cancel'=>$plan->where(['manager_id'=>$id  ,'status'=>4])->count(),
        ];

        $this->assign("info",$list->toArray()['data']);
        $this->assign('page', $page);
        $this->assign('count', $count);
        $this->assign('data', $user::get($id));
        $this->assign('list',$list);
        return $this->fetch();
    }



    /**
     * 添加销售经理添加提交
     * @adminMenu(
     *     'name'   => '添加销售经理 post提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加销售经理添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {


                $result = $this->validate($this->request->param(), 'User');

                if ($result !== true) {
                    $this->error($result);
                } else {
                    if (isset($_POST['user_pass'])){
                        if (@$_POST['user_pass'] !==@$_POST['password_confirm']){
                            $this->error("密码输入不一致!");
                        }
                    }else{
                        $_POST['user_pass']="123456";
                    }

                    unset($_POST['password_confirm']);
                    $_POST['user_type']    =   3;
                    $_POST['user_pass']    =   cmf_password($_POST['user_pass']);
                    $_POST['create_time']  =   time();

                    $result    = DB::name('user')->insertGetId($_POST);

                    if ($result !== false) {
                        $this->success("添加成功！", url("admin_sales/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                }
        }
    }



    /**
     * 编辑销售经理添加提交
     * @adminMenu(
     *     'name'   => '编辑销售经理 post提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑销售经理添加提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {

        $user = model("user");
        $id = input('id');
        if ($this->request->isPost()) {

            $result = $this->validate($this->request->param(), 'User');

            if ($result !== true) {

                $this->error($result);
            } else {

                isset($_POST['user_pass'] ) && $_POST['user_pass']    =   cmf_password($_POST['user_pass']);

                $result    = $user->isUpdate(true)->save($_POST);

                if ($result !== false) {
                    $this->success("保存成功！", url("admin_sales/index"));
                } else {
                    $this->error("保存失败！");
                }
            }
        }
         return $user::get($id);
    }


    /**
     * @throws \think\Exception
     * 删除多条
     * @adminMenu(
     *     'name'   => '删除多条 post提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除多条',
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
            $this->success("删除成功！",url("admin_sales/index"));
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     * 重置密码
     * @adminMenu(
     *     'name'   => '重置密码 post提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '重置密码',
     *     'param'  => ''
     * )
     */

    public function resetPass(){
        $id = $this->request->param('id', "", 'string');
        $pass = cmf_password("123456");
        if ($len  = model('user')->where('id','in',$id)->update(['user_pass'=>$pass]) !== false) {
            $this->success("重置密码成功！",url("admin_sales/index"));
        } else {
            $this->error("重置密码失败！");
        }
    }


    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '销售经理冻结',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '销售经理拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = input('id', 0, 'intval');
        if ($id) {
            $result =model("user")->where(["id" => $id, "user_type" => 3  ])->setField('user_status', 0);
            if ($result) {
                $this->success("冻结成功！", "admin_sales/index");
            } else {
                $this->error('冻结失败,该销售经理不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！'.$id);
        }
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '销售经理启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '销售经理启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            model("user")->where(["id" => $id, "user_type" =>3 ])->setField('user_status', 1);
            $this->success("启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
}
