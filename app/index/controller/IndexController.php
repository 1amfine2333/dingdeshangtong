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

use app\user\model\PlanOrderModel;
use app\user\model\UserModel;
use cmf\controller\UserBaseController;
use think\Db;

class IndexController extends UserBaseController
{


    /**
     * 前台用户首页
     * @throws
     */
    public function index()
    {
            //类目
            $category = Db::name('category')->select();
            //数据处理
            $category_data = [];

            foreach ($category as $item)
            {
                if ($item['parent_id']==0)
                {
                    foreach ($category as $v)
                    {

                        $category_data[$item['type']]['name']=$item['name'];

                        if ($v['parent_id']!==0 && $item['id'] == $v['parent_id'])
                        {
                            $sub['text']=$v['name'];
                            $sub['value']=$v['id'];
                            $category_data[$item['type']]['subsets'][] =$sub;
                        }

                    }

                }
            $this->assign('category',$category_data);
        }

        return $this->fetch();
    }


    /**
     * 前台首页提交
     *
     */
    public function indexPost()
    {

        $data = input('request.');

        $result = $this->validate($data,"PlanOrder");

        if ($result!==true)
        {
            $this->error($result);
        }

        $plan = new PlanOrderModel();
        $data = check_field("project_address,unit_name,project_name,salesman,pouring_type,pouring_part,pouring_label,slump,pouring_time,distance,plan_square,principal,mobile,manager,manager_id",$data);
        $data['user_id']=cmf_get_current_user_id();
        // 创建订单号

        $data['number']= time().rand(1000,1222);
        /*$data['pouring_time'] = preg_replace("/(年|月)/u","-",$data['pouring_time']);
        $data['pouring_time'] = preg_replace("/日/u","",$data['pouring_time']);*/
        $data['pouring_time'] = strtotime($data['pouring_time']);

        if($data['pouring_time']<time()){
            $this->error("浇筑时间不能小于当前时间, 请重新填写");
        }
        //新增计划单数据
       $res  = $plan::create($data);

       if ($res!==false){
           $this->success("计划单提交成功!",url("index/successPage"));
       }else{
           $this->error("计划单提交失败!");
       }


    }


    /**
     * 提交成功页面
     * @return \think\response\View
     */
    public function successPage(){
        return view(":success");
    }


}
