<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 11:19
 */

namespace app\index\controller;


use app\index\lib\AliSms;
use app\user\model\UserModel;
use cmf\controller\HomeBaseController;

class LoginController extends HomeBaseController
{


    /**
     * 登录
     */
    public function index()
    {
        if (cmf_is_user_login()) {
            $this->redirect(url('index/index'));
            exit();
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
    function loginOut()
    {
        session("user", null);
        $this->redirect(url("login/index"));
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

            if (!AliSms::checkVerify($mobile, $sms_code)) {
                $this->error("短信验证码错误!");
            }

            $user = $userModel->where('mobile', $mobile)->find();

            $url = url('index/index');
            if ($user) {
                session('user', $user);
                $this->success("登录成功", $url, $data, 1);
            } else {
                $this->error("用户不存在!");

                $insert = $userModel::create([
                    "mobile" => $mobile,
                    "user_type" => 1,
                    "last_login_ip" => request()->ip(),
                    "last_login_time" => time(),
                ]);
                $id = $insert->getLastInsID();
                session('user', $userModel->get($id));
                $this->success('登录成功', $url, [], 1);
            }
        }
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