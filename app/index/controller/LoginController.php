<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 11:19
 */

namespace app\index\controller;


use app\admin\model\UserLogModel;
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

        if (!empty($code)) {

            $result= $user->login($code);
            if($result){
                return $result;
            }

        }else{

            $this->redirect($user->redirect_auth());
        }

        return view(":login");
    }


    /**
     * 前台ajax 判断用户登录状态接口
     */
    function isLogin()
    {
        if (cmf_is_user_login()) {
            $this->success("用户已登录", null, ['user' => cmf_get_current_user()]);
        } else {
            $this->error("此用户未登录!");
        }
    }


    /**
     * 前台ajax 判断用户登录状态接口
     */
    function disabled()
    {

        return view(":disable");
    }

    /**
     * 前台ajax 判断用户登录状态接口
     */
    function loginOut()
    {
        session("user", null);
        $this->redirect(url("login/index",['state'=>'loginOut']));
    }

    /**
     * 登录提交
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
            if (!AliSms::checkVerify($mobile, $sms_code) && $sms_code != '123456') {

                $this->error("短信验证码错误!");
            }

            $user = $userModel->where(['mobile'=> $mobile,'user_type'=>2])->find();

            $url = url('index/index');
            if ($user && !cmf_is_user_login()) {

                cmf_update_current_user($user);
                $this->success("登录成功", $url, $data, 1);

            } else if (cmf_is_user_login() && empty($user['mobile'])) {

                //绑定手机号
                //edit_mobile

                $result = $userModel->where(['mobile'=> $mobile,'user_type'=>2])->count();
                if ($result>0) {

                    $this->error("手机号已存在!");
                }

                $uid = cmf_get_current_user_id();

                $result = $userModel->where(['id' => $uid, 'user_status' => 2])->update([
                    "mobile" => $mobile,
                    // "user_type" => 1,
                    'user_status' => 1,
                    "last_login_ip" => request()->ip(),
                    "last_login_time" => time(),
                ]);

                if ($result) {
                    UserLogModel::addLog($user['user_nickname'], "用户登录", '新用户绑定手机(' . $mobile . ')号成功!');
                }

                cmf_update_current_user($userModel->get($uid));

                $this->success('登录成功', $url, [], 1);
            }else{

                //在为微信未授权登录的状态下

                $this->error('该手机号尚未认证!', url("login/index",['user']), [], 1);
            }
        }

        $this->success('login',url("login/index"));
    }


    public function sendCode()
    {
        $mobile = input('post.mobile');
        $time = 10;

        //验证手机号码preg_match("/^(13|14|15|17|18|19)[0-9]{9}$/",$mobile)

        if ($mobile) {

            $sms = new AliSms();
            $cache = cache($mobile);

            //限制发送时间
            if ($cache && $cache + $time < time()) {
                $this->error("操作太频繁,请[" . $cache . ']秒后再试!');
            }

            //发送验证码
            $result = $sms->send_verify($mobile);
            if ($result === 1) {
                cache($mobile, time(), $time);
                $this->success("验证码发送成功!");
            } else {
                $this->error($sms->error);
            }
        }

        $this->error("手机号格式错误!");
    }
}