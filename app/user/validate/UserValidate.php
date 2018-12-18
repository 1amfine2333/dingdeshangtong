<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\user\validate;

use think\Validate;

class UserValidate extends Validate
{

    protected $regex = ['mobile'=>"/^(1[3-9])[0-9]{9}$/"];
    protected $rule = [
        'user_login' => 'unique:user,user_login',
        'mobile' => 'require|regex:mobile',//|unique:user,mobile
        'user_name'  => 'require',
        'user_pass'  => 'require|length:6,12',
    ];
    protected $message = [
        'user_login.require' => '登录账号不能为空',
        'user_login.unique'  => '登录账号名已存在',

        'mobile.require'   => '请输入电话号码',
        'mobile.regex'   => '请输入正确的电话号码',

        'user_name.require' => '请输入真实姓名',

        'user_pass.require'  => '请输入密码',
        'user_pass.length'  => '请输入6~12位密码',
    ];

    protected $scene = [
        'add'  => ['user_login', 'user_pass', 'user_email'],
        'addSales'  => ['mobile', 'user_pass', 'user_name'],
        'edit' => ['user_name', 'mobile'],
    ];
}