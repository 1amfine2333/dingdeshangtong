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

use app\admin\model\BaseConfigModel;
use app\admin\model\UserLogModel;
use app\admin\model\WebMsgModel;
use app\index\lib\AliSms;
use app\index\model\UserModel;
use cmf\controller\UserBaseController;

class UserController extends UserBaseController
{

    /**
     * 计划单列表
     * @throws
     */
    public function index()
    {

        $BaseConfig = new BaseConfigModel();
        $tel = $BaseConfig->select();//热线电话

        $data = [
            'tel' => $tel,
        ];

        $this->assign('data', $data);
        return $this->fetch();
    }


    public function info()
    {
        return $this->fetch();
    }

    public function mobile()
    {

        if ($this->request->isPost()) {

            $data = input('post.');
            $result = $this->validate($data, "Index.edit");
            if ($result !== true) {
                $this->error($result);
            }


            $mobile = input('post.edit_mobile');
            $sms_code = input('post.sms_code');


            if (!AliSms::checkVerify($mobile,$sms_code) ){//&& $sms_code!=123456

                $this->error('请输入正确验证码!');
            }
            $model = new UserModel;

            if( $model->where(['mobile'=>$mobile, 'user_type'=>2])->count()>0 ){

                $this->error("修改失败，请重新提交!");
            }

            $result = $model->where("id",cmf_get_current_user_id())->setField('mobile',$mobile);

            if ($result) {
                $user = cmf_get_current_user();
                $user['mobile'] = $mobile;
                cmf_update_current_user($user);
                $this->success("修改成功!", url('user/index'));
            } else {

                $this->error('修改失败，请重新提交!');
            }

        }

        return $this->fetch();
    }


    /**
     * 消息列表
     * @return \think\response\View
     * @throws
     */
    public function msg()
    {

        $page = $this->request->param('page');
        $limit = 15;
        if (request()->isAjax()) {

            $web = new WebMsgModel();

            $where = ["to_user_id"=>cmf_get_current_user_id(),'create_time'=>['<=',time()]];

            $list = $web->getMsgList($where ,$page,$limit);


            foreach ($list as $k => $value) {
                $value['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
                $list[$k] = $value;
            }

            $data['list'] = $list;
            $data['pages'] = ceil($web->where($where)->count() / $limit);

            $this->success('success', null, $data);
        }
        return view();
    }

    /**
     * 消息详情
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function msg_detail()
    {
        $id = input("id", 0, 'intval');

        if ($id) {
            $web = new WebMsgModel();
            $data = $web::get($id);
            if ($data) {

                $data['create_time'] = date("Y-m-d H:i:s", $data['create_time']);
                $data['content'] = htmlspecialchars_decode($data['content']);
                $web->joinRead($data['id']);

            }

            $this->assign('data', $data);
        }
        return view();
    }


    /**
     *
     */

    public function sendCode()
    {
        $mobile = input('post.mobile');
        $time = 60;

        //验证手机号码preg_match("/^(13|14|15|17|18|19)[0-9]{9}$/",$mobile)

        if ($mobile) {


            $res = $this->validate(['mobile'=>$mobile],'Index.send');
            if($res!==true){
                $this->error($res);
            }

 /*

  $user = new UserModel;
              if( $user->where(['mobile'=>$mobile])->count() ){
                $this->error("发送失败,该手机号已被使用!");
            }*/

            $sms = new AliSms([]);
            $cache = cache($mobile);

            //限制发送时间
         /*   if ($cache && ($cache + $time) > time()) {
                $this->error("操作太频繁,请[" . ( ($cache + $time) -time())  . ']秒后再试!');
            }*/

            //发送验证码
            $result = $sms->send_verify($mobile);
            if ($result) {
                cache($mobile, time(), $time);
                $this->success("验证码发送成功!");
            } else {
                $this->error($sms->error);
            }
        }

        $this->error("请输入正确的手机号!");
    }

}
