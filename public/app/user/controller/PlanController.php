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
use cmf\controller\AdminBaseController;
use think\Db;

class PlanController extends AdminBaseController
{

    /**
     * 计划单管理
     */
    public function index(){
        $where = array();
        $param = $this->request->param();
        if(!empty($param['keyword'])){
            $keyword = $param['keyword'];
            $where['u.user_name|p.unit_name|p.project_name|p.principal|p.salesman'] = array('like',"%$keyword%");
        }
        if(!empty($param['mobile'])){
            $mobile = $param['mobile'];
            $where['u.mobile|p.mobile'] = array('like',"%$mobile%");
        }
        //下单时间
        if(!empty($param['begin_time']) && !empty($param['end_time'])){
            $where['p.create_time'] = array('between',[strtotime($param['begin_time']),strtotime($param['end_time'])]);
        }elseif (!empty($param['begin_time'])){
            $where['p.create_time'] = array('egt',strtotime($param['begin_time']));
        }elseif (!empty($param['end_time'])){
            $where['p.create_time'] = array('elt',strtotime($param['end_time']));
        }
        //浇筑时间
        if(!empty($param['start_time']) && !empty($param['finish_time'])){
            $where['p.pouring_time'] = array('between',[strtotime($param['start_time']),strtotime($param['finish_time'])]);
        }elseif (!empty($param['start_time'])){
            $where['p.pouring_time'] = array('egt',strtotime($param['start_time']));
        }elseif (!empty($param['finish_time'])){
            $where['p.pouring_time'] = array('elt',strtotime($param['finish_time']));
        }

        $plan = Db::name('PlanOrder')
            ->alias('p')
            ->join('user u' ,'u.id=p.user_id')
            ->where($where)
            ->field('p.*,u.user_name,u.mobile phone')
            ->order('p.create_time desc')
            ->paginate(10);
        $plan->appends($param);
        //总计划方量
        $total_square = Db::name('PlanOrder')->alias('p')
            ->join('user u' ,'u.id=p.user_id')
            ->where($where)
            ->sum('plan_square');
        //今日方量
        $today_square = Db::name('PlanOrder')
            ->whereTime('create_time', 'between', [strtotime(date('Y-m-d')),strtotime(date('Y-m-d 23:59:59'))])
            ->sum('plan_square');
        $this->assign("data",$plan->toArray()['data']);
        $this->assign("plan",$plan);
        $this->assign("page",$plan->render());
        $this->assign("today_square",$today_square);
        $this->assign("total_square",$total_square);
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('mobile', isset($param['mobile']) ? $param['mobile'] : '');
        $this->assign('begin_time', isset($param['begin_time']) ? $param['begin_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('finish_time', isset($param['finish_time']) ? $param['finish_time'] : '');
        return $this->fetch();
    }

    /**
     * 批量真删除计划单
     */
    public function deleteTrue()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $list_array = $data['list'];
            if(empty($list_array) || !is_array($list_array)){
                $this->error("请选择至少一条计划单");
            }
            foreach ($list_array as $v){
                Db::name('PlanOrder')->delete($v);
            }
            $this->success("删除成功");
        }
    }

    /**
     * 批量假删除计划单
     */
    public function deleteFalse()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $list_array = $data['list'];
            if(empty($list_array) || !is_array($list_array)){
                $this->error("请选择至少一条计划单");
            }
            $save_array = array();
            foreach ($list_array as $v){
                $save_array[] = ['id'=>$v,'delete_time'=>time()];
            }
            $planOrderModel = new PlanOrderModel();
            if($planOrderModel->saveAll($save_array) !== false){
                $this->success("前端删除成功");
            }else{
                $this->error("前端删除失败");
            }
        }
    }

    //查看详情
    public function info(){
        $param = $this->request->param();
        $planOrderModel = new PlanOrderModel();
        $plan = $planOrderModel->alias('p')
            ->join('user u','u.id=p.user_id')
            ->where('p.id',$param['id'])
            ->field('p.*,u.user_name,u.mobile phone')
            ->find();
        $this->assign("plan",$plan);
        return $this->fetch();
    }

    //处理计划单
    public function actionOrder(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            if(!in_array($data['status'],array('2','3','4'))){
                $data['status'] = 2;
            }
            if(Db::name('PlanOrder')->where(['id' => $data['id']])->update(["status"=>$data['status'],'update_time'=>time()]) !== false){
                $this->success("提交成功");
            }else{
                $this->error("提交失败");
            }
        }
    }
}
