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
namespace app\index\controller;

use app\user\model\ComplaintModel;
use app\user\model\PlanOrderModel;
use app\user\model\UserModel;
use cmf\controller\UserBaseController;
use think\Db;
use think\Validate;

class ComplaintController extends UserBaseController
{

    /**
     *投诉建议列表
     * @throws
     */
    public function index()
    {
        $plan  = new ComplaintModel();
        $request = $this->request->param();
        $where = ['user_id'=>cmf_get_current_user_id()];

        //如果传入状态参数 则根据状态查询
        if (!empty($request['status'])){
            $where['status'] = intval($request['status']);
        }

        $list = $plan->where($where)->select();

        $status = [1=>'待处理',2=>'已处理',3=>'已拒绝',4=>'已取消'];

        foreach ($list as $k => $value)
        {
            $value['pouring_time']=date("Y-m-d H:i:s",$value['pouring_time']);
            $value['status_text']= @$status[$value['status']];
            $list[$k]= $value;
        }

        $data['list'] =  $list;
        $data['pages']=ceil( $plan->where($where)->count()/10 );


        if (request()->isAjax()){
            $this->success('success',null,$data);
        }

        $this->assign('data',$data);
        return $this->fetch(":plan");
    }




    /**
     * 前台用户首页
     */
    public function addPost()
    {

        if ($this->request->isPost())
        {
            $data = input('request.');

            $result = $this->validate($data,"Complaint");

            if ($result!==false)
            {
                $this->error($result);
            }
            $plan = new ComplaintModel();
            // $data['content'] = htmlspecialchars($data['content']);
            $data['user_id']=cmf_get_current_user_id();
            //新增计划单数据

            $res  = $plan::create($data,"type,content,plan_number");

            if ($res!==false){
                $this->success("计划单提交成功!");
            }else{
                $this->error("计划单提交失败!");
            }
        }

    }



}
