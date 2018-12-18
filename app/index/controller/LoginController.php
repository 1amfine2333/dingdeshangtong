<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 11:19
 */

namespace app\index\controller;


use app\index\lib\AliSms;
use app\index\model\UserModel;
use cmf\controller\HomeBaseController;

class LoginController extends HomeBaseController
{


    /**
     * 登录
     * @throws
     */
    public function index()
    {

        $user = new UserModel();
        $code = input('get.code');
        if(input('state') == 'loginOut'){
            return view(":login");
        }

        if (request()->isAjax()){
            $auth = input("auth");
            if (cmf_is_user_login($auth)){
                $this->success("success","index/index");
            }
        }

        if (!empty($code)) {
             $user->login($code);
        }else{
            $this->redirect($user->redirect_auth());
        }

        if(cmf_is_user_login()){
             $this->redirect(url("index/index"));
        }

        return view(":login");
    }


    /**
     * 退出登录
     */
    function loginOut()
    {
        cmf_update_current_user(null);
        $this->redirect(url("login/index",['state'=>'loginOut']));
    }

    /**
     * 登录提交
     * @throws
     */
    public function loginPost()
    {

        if (request()->isPost()) {
            $data = input('request.');

            $userModel = new UserModel();

            $result = $this->validate($data, 'Index.login');
            if ($result !== true) {
                $this->error($result);
            }

            $mobile = $data['mobile'];
            $sms_code = $data['sms_code'];
            /**
             * 短信验证码此处为固定  工作环境中删除第二条件
             */
            if (!AliSms::checkVerify($mobile, $sms_code) ) { //&& $sms_code != '123456'

                $this->error("请输入正确验证码!");
            }

            $user = $userModel->where(['mobile'=> $mobile,'user_type'=>2])->find();

            $url = url('index/index');

            //通过账号未找到用户
            if (empty($user))
            {

                $user_session = session('user_wx');
                if ($user_session)
                {
                    //openId 查找数据库是否存在用户
                    $result = $userModel->where(['open_id'=>$user_session['openid'],'user_type'=>2 ])->find() ;
                    if (empty($result))
                    {

                        //不存在数据库 新增用户
                        $res =  $userModel->register($mobile);

                        if ($res)
                        {
                            $auth = encrypt(json_encode(['id'=>cmf_get_current_user_id()]));
                            $this->success('登录成功', $url, $auth, 1);
                        }else{
                            $this->error('登录失败,无法获取用户相关数据!');
                        }

                    }else{

                        $this->error('请使用当前微信已绑定手机号登录!');
                    }

                } else{
                    $this->error('登录失败,无法获取微信授权!');
                }

            } else {


                if ($user['user_status']==0)
                {

                    $this->error("您的账号已冻结,解冻请联系平台!");
                }elseif ($user['user_status']==1){

                    $user_session = session('user_wx');
                    if ($user_session){

                        //openId相同则登录成功
                        if ($user['open_id']==$user_session['openid'])
                        {

                             $userModel->where(['id'=>$user['id']])->update([
                                'last_login_ip' => get_client_ip(0, true),
                                'last_login_time' => time(),
                            ]) ;
                            cmf_update_current_user($user);
                            $auth = encrypt(json_encode(['id'=>$user['id']]));
                            $this->success('登录成功', $url, $auth, 1);

                        }else{
                            $this->error('请使用当前微信已绑定手机号登录!');
                        }

                    } else{
                        $this->error('登录失败,无法获取微信授权');
                    }

                }else{
                    $this->error('账号状态异常!');
                }
            }
        }
    }


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

            $sms = new AliSms();

            //限制发送时间
          /*  if ($cache && ($cache + $time) > time()) {
                $this->error("操作太频繁,请[" . ( ($cache + $time) -time())  . ']秒后再试!');
            }
            $cache = cache($mobile);
          cache($mobile, time(), $time);
          */

            //发送验证码
            $result = $sms->send_verify($mobile);
            if ($result === 1) {

                $this->success("验证码发送成功!");
            } else {
                $this->error($sms->error);
            }
        }

        $this->error("请输入正确的手机号!");
    }
}