<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 11:19
 */

namespace app\sales\controller;


use app\admin\model\UserLogModel;
use app\index\lib\AliSms;
use app\sales\model\UserModel;
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

        if (input('state') == 'loginOut') {
            return view(":login");
        }

        if ($code) {
            $res =$user->login($code);

            if($res){
                return $res;
            }

        } else {
            $this->redirect($user->redirect_auth());
        }

        return view(":login");
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


            $result = $this->validate($data, 'Index.salesLogin');
            if ($result !== true) {
                $this->error($result);
            }

            $mobile = $data['mobile'];
            $password = $data['password'];

            $user = $userModel->where(['mobile' => $mobile,'user_pass'=>cmf_password($password), 'user_type' => 3])-> find();

            if (empty($user)) {
                $this->error("请输入正确的账号/密码!");
            }

            if ($user) {

                if ($user['user_status'] == 1) {

                    cmf_update_current_user($user);
                    $this->success("登录成功", url('user/index'), [], 1);

                } else if ($user['user_status'] == 2) {

                    $result = $userModel->register($mobile);

                    if ($result === true) {
                        $this->success('登录成功!',   url('user/index'));
                    } else {
                        $this->error("登录失败!");
                    }

                } else {
                    $this->error(" 您的账号已冻结 解冻请联系平台！");
                }

            } else {
                //在为微信未授权登录的状态下
                $this->error('该手机号尚未认证!', url("login/index"), [], 1);
            }
        }

        $this->success('login', url("login/index", ['state' => 'sales']));
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
        $this->redirect(url("login/index", ['state' => 'loginOut']));
    }
}