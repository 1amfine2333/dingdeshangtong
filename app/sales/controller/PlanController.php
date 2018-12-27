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
namespace app\sales\controller;

use app\user\model\ComplaintModel;
use app\user\model\PlanOrderModel;
use app\user\model\UserModel;
use cmf\controller\UserBaseController;
use think\Db;
use think\Validate;

class PlanController extends UserBaseController
{

    /**
     * 计划单列表
     * @throws
     */
    public function index()
    {
        $plan = new PlanOrderModel();
        $request = input('request.');
        $page = intval(input('page'));
        $where = ['delete_time'=>0];
        $limit = 10;
        if (!empty($request['status'])) {
            $where['status'] = intval($request['status']);
            if ($where['status'] != 1) {
                $where['manager_id'] = cmf_get_current_user_id();
            }
        }
        $data['pages'] = ceil($plan->where($where)->count() / $limit);

        /**
         * ajax 请求
         */
        if (request()->isAjax())
        {

            $list = $plan->where($where)->order("create_time desc")->page($page, $limit)->select();

            $status = [1 => '待处理', 2 => '已处理', 3 => '已拒绝', 4 => '已取消'];

            foreach ($list as $k => $value) {
                $value['pouring_time'] = date("Y-m-d H:i:s", $value['pouring_time']);
                $value['status_text'] = @$status[$value['status']];
                $list[$k] = $value;
            }

            $data['list'] = $list;

            $data['page'] = $page;
            $this->success('success', null, $data);
         }




        $this->assign('data', $data);
        return $this->fetch();
    }


    /**
     * @return mixed
     * @throws
     */
    public function detail()
    {
        $id = input("id", 0, 'intval');

        if ($id) {
            $plan = new  PlanOrderModel();

            $data = $plan->where(['id'=>$id])->find();

            if (!empty($data)) {
                $user = UserModel::get($data['user_id']);
                if($user){
                    $data['avatar'] = $user['avatar'];
                    $data['nickname'] = $user['user_nickname'];
                    $data['user_mobile'] = $user['mobile'];
                }
            }
            $data['isLogin']=cmf_is_user_login();
            $this->assign('data', $data);
        }

        return $this->fetch();

    }

    public function edit()
    {
        $id = input("id", 0, 'intval');
        $status = input('status');
        if ($id && $status) {

            if (!in_array($status, [2, 3, 4])) {
                $this->error("状态异常,操作失败!");
            }
            $plan = new PlanOrderModel();
            $result = $plan->where(['id' => $id,   'status' => 1])
                ->update(["status" => $status,'manager_id'=>cmf_get_current_user_id(),'manager'=>cmf_get_current_user()['user_name']]);
            if ($result) {
                $status_text = ['2' => '安排', '3' => '拒绝', '4' => '取消'];
                $this->success("{$status_text[$status]}成功!");
            } else {
                $this->error("操作失败,此计划单状态已改变或不存在");
            }
        } else {
            $this->error("抱歉,参数错误操作失败!");
        }
    }


/*    public function delete()
    {
        $id = input("id", 0, 'intval');
        if ($id) {
            $plan = new PlanOrderModel();
            $result = $plan->where(['id' => $id, 'user_id' => cmf_get_current_user_id(), 'status' => ['in', '3,4']])->delete();
            if ($result) {
                $this->success("删除成功!");
            } else {
                $this->error("删除失败,此计划单您无权删除或不存在");
            }
        } else {
            $this->error("抱歉,参数错误删除失败!");
        }
    }*/

}
