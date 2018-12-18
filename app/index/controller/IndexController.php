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

use app\index\lib\Curl;
use app\user\model\PlanOrderModel;
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

        foreach ($category as $item) {
            if ($item['parent_id'] == 0) {
                foreach ($category as $v) {

                    $category_data[$item['type']]['name'] = $item['name'];

                    if ($v['parent_id'] !== 0 && $item['id'] == $v['parent_id']) {
                        $sub['text'] = $v['name'];
                        $sub['value'] = $v['id'];
                        $category_data[$item['type']]['subsets'][] = $sub;
                    }
                }
            }
        }

        $project_name = Db::name("project")->order('create_time desc')->select();
        $project = [];
        foreach ($project_name as $item){
            $project[]=[
                "text"=>$item['name'],
                "value"=>$item['salesman'],
            ];
        }

        $this->assign("project",$project);
        $this->assign('category',$category_data);

        return $this->fetch();
    }


    /**
     * 前台首页提交
     *
     */
    public function indexPost()
    {

        $data = input('request.');

        $result = $this->validate($data, "PlanOrder");
        if ($result !== true) {
            $this->error($result);
        }
        $plan = new PlanOrderModel();

        if (!is_numeric($data['plan_square']) || !is_numeric($data['distance']) ){
            $this->error("提交失败，请重试");
        }

        $data['plan_square'] = doubleval($data['plan_square'])  ;
        $data['distance'] = doubleval($data['distance'])  ;

        $data['user_id'] = cmf_get_current_user_id();
        // 创建订单号
        $data['number'] = time() . rand(1000, 1222);
        /*$data['pouring_time'] = preg_replace("/(年|月)/u","-",$data['pouring_time']);
          $data['pouring_time'] = preg_replace("/日/u","",$data['pouring_time']);
        */
        $data['pouring_time'] = strtotime($data['pouring_time']);
        $data['create_time']=time();
        if ($data['pouring_time'] < time()) {
            $this->error("提交失败，请重试");
        }

        //新增计划单数据

        $res = $plan->except("id,manager,manager_id,status,update_time,delete_time")->save($data);

        if ($res) {
            $this->success("计划单提交成功!", url("index/successPage"), [],1);
        } else {
            $this->error("计划单提交失败!");
        }


    }


    /**
     * 提交成功页面
     * @return \think\response\View
     */
    public function successPage()
    {
        return view("success");
    }

    public function getAddress()
    {
        $map = "http://api.map.baidu.com/location/ip?ak=cLx2ByWCm7OxxO65d8jKLp7iudhVNbtB&coor=bd09ll&ip=222.182.197.0";
        $_SERVER['REMOTE_ADDR'];
        $curl = new Curl();
        $data = $curl->get($map, [], 'http://test.678down.com/');
        $this->success('result', null, json_decode($data));
    }


}
