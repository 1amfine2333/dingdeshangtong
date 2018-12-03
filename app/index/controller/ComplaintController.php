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
        $plan = new ComplaintModel();
        $request = $this->request->param();
        $page = $this->request->param('page');
        $where = ['user_id' => cmf_get_current_user_id()];
        $limit = 10;
        if (request()->isAjax()) {

            //如果传入状态参数 则根据状态查询
            if (!empty($request['status'])) {
                $where['status'] = intval($request['status']);
            }
            $list = $plan->field('user_id,reply,update_time,plan_number',true)->where($where)->order("create_time desc")->page($page, $limit)->select();

            $status = [0 => '未回复', 1 => '已回复'];

            foreach ($list as $k => $value) {
                $value['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
                $value['status_text'] = @$status[$value['status']];
                $value['content'] = html($value['content'], 50);// htmlspecialchars_decode($value['content']);

                $list[$k] = $value;
            }

            $data['list'] = $list;
            $data['pages'] = ceil($plan->where($where)->count() / $limit);

            $this->success('success', null, $data);
        }

        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws
     */
    public function detail()
    {
        $id = input("id", 0, 'intval');

        $status = ['待处理', '已处理'];

        if ($id) {
            $data = ComplaintModel::get(['id' => $id, 'user_id' => cmf_get_current_user_id()]);
            if($data){

                if($data->is_read==1){
                    $data->is_read=2;
                }
                $data->save();
                $data['status_text'] = @$status[$data['status']];
            }
            $this->assign('data', $data);

        }
        return $this->fetch();
    }


    /**
     *提交计划单
     */
    public function addPost()
    {

        if ($this->request->isPost()) {
            $data = input('request.');

            $result = $this->validate($data, "Complaint");

            if ($result !== true) {
                $this->error($result);
            }
            $Complain = new ComplaintModel();
            $data['user_id'] = cmf_get_current_user_id();

            //新增计划单数据

            if($data['type']==3){

                if (empty($data['plan_number'])){
                    $this->error("未获取到计划单号,提交失败!");
                }

                $plan = new  PlanOrderModel();
                $planCount =  $plan->where(['user_id'=>$data['user_id'], 'number'=>$data['plan_number'] ])->count();
                $ComplainCount =  $Complain->where(['plan_number'=>$data['plan_number'] ])->count();

                if($planCount<1 || $ComplainCount>0){
                    $this->error('提交失败,请刷新后重试');
                }
            }
            if(isset($_POST['image'])){
                $images=[];
                foreach ($_POST['image'] as $v){
                    $v = DS . 'upload'.DS.'user'.DS.$v;
                    $images[] = "<img src='$v' alt='' style='object-fit: cover;'/>";
                }
                $data['content']=$data['content'].join('',$images);
            }

            $res = $Complain::create($data, "user_id,type,content,plan_number");

            if ($res !== false) {
                $this->success("提交成功!");
            } else {
                $this->error("提交失败!");
            }
        }

    }


    /**
     * 删除已上传图片
     */
    public function del(){
        $img = trim(input('request.filename'));
        $filename =ROOT_PATH . 'public'.DS.'upload'.DS.'user' .DS. $img;
        if($img && is_file($filename)){
            if (@fileatime($filename)+600>time()){
                @unlink($filename);
            }
            $this->success('delete image success');
        }
        $this->error('this file is Non-existent');
    }


    /**
     * 上传tcp.xiaomiqiu.cn
     * ngrok -config=ngrok.cfg -hostname test.678down.com 8080
     * ngrok -config=ngrok.cfg -subdomain http://test.678down.com/ 80 //(xxx 是你自定义的域名前缀)。
     */
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');

        if($file){

            if (!$file->checkImg()){
                $this->error('仅支持上传图片文件!');
            }

            $info = $file
                ->validate(['size'=>156780,'ext'=>'jpg,png,gif'])
                ->move(ROOT_PATH . 'public' . DS . 'upload'.DS.'user');

            if($info){
                // 成功上传后 获取上传信息
                $res = [
                    'src' => cmf_get_image_preview_url('user/'.$info->getSaveName()),
                    'saveName' => $info->getSaveName(),
                ];

                $this->success('success','complaint/index',$res);
            }else{
                // 上传失败获取错误信息
                $this->error($file->getError()) ;
            }
        }
    }

}
