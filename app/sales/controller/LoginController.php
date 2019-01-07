<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 11:19
 */

namespace app\sales\controller;


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
        if (request()->isAjax()){
            $auth = input("auth");
            if (cmf_is_user_login($auth)){
                $this->success("success",url("user/index"));
            }
        }

        if ($code) {

            $user->login($code);

        } else {
            $this->redirect($user->redirect_auth());
        }

        if (cmf_is_user_login()) {
            $this->redirect(url("user/index"));
        }

        return view(":login");
    }

    /**
     * 登录提交
     * @throws
     */
    public function loginPost()
    {

        if (request()->isPost())
        {
            $data = input('request.');
			file_put_contents('a32323.txt',var_export($data,true),FILE_APPEND);
            $userModel = new UserModel();


            $result = $this->validate($data, 'Index.salesLogin');
            if ($result !== true) {
                $this->error($result);
            }

            $mobile = $data['mobile'];
            $password = $data['password'];

            $user = $userModel->where(['mobile' => $mobile, 'user_type' => 3])->find();
			file_put_contents('a3.txt',var_export($user,true),FILE_APPEND);
            //通过账号未找到用户
            if (empty($user))
            {
                $this->error("请输入正确的账号!");
            }else
			{
                if ($user['user_pass'] !== cmf_password($password)) {
                    $this->error("请输入正确的密码!");
                }
                $sales = session("sales_wx");
                //未获取到
                if (empty($sales)) {

                    $this->error("获取微信授权失败!");
                }

                //用户状态正常
                if ($user['user_status'] == 1  )
                {
                    //此用户微信ID 和此微信匹配 登录成功
                    if($sales['openid'] == $user['open_id']){
						file_put_contents('a327.txt',$sales['openid'],FILE_APPEND);
                        cmf_update_current_user($user);
                        $auth = encrypt(json_encode(['id'=>cmf_get_current_user_id()]));
                        $this->success("登录成功", url('user/index'), $auth, 1);
                    }else{

                        $this->error("请使用当前微信绑定的账号登录!");
                    }


                } else if ($user['user_status'] == 2) {

                 //用户状态未微信认证时

                    //查找此微信是否已被注册
                    $userinfo = $userModel->where(["open_id" => $sales['openid'], 'user_type' => 3])->find();
					file_put_contents('a32.txt',var_export($userinfo,true),FILE_APPEND);
                    //此微信已注册并且已绑定手机 则不允许登录
                    if ($userinfo)
                    {
                        $this->error("请使用当前微信绑定的账号登录!");
                    }else{

                        //此微信为注册  则绑定此手机号码
                        $result = $userModel->register($mobile);
						file_put_contents('a326.txt',$data['mobile'].'|'.$result,FILE_APPEND);
                        if ($result === true) {
                            $auth = encrypt(json_encode(['id'=>cmf_get_current_user_id()]));
                            $this->success('登录成功!', url('user/index'),$auth);
                        } else {
                            $this->error("获取用户数据失败,登录失败!");
                        }
                    }
                } else {
                    //用户被管理员冻结
                    $this->error(" 您的账号已冻结 解冻请联系平台！");
                }
            }
        }
    }

    /**
     * 前台ajax 判断用户登录状态接口
     */
    function loginOut()
    {
        cmf_update_current_user( null);
        $this->redirect(url("login/index", ['state' => 'loginOut']));
    }
}