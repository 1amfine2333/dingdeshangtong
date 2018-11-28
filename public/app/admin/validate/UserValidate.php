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
namespace app\admin\validate;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'user_login' => 'require|unique:user,user_login',
        'user_pass'  => 'require',
        'user_name'  => 'require',
        'user_email' => 'email|unique:user,user_email',
    ];
    protected $message = [
        'user_login.require' => '登录账号不能为空',
        'user_login.unique'  => '登录账号名已存在',
        'user_pass.require'  => '密码不能为空',
        'user_name.require' => '真实姓名不能为空',

        'user_email.email'   => '邮箱不正确',
        'user_email.unique'  => '邮箱已经存在',
    ];

    protected $scene = [
        'add'  => ['user_login', 'user_pass', 'user_email'],
        'edit' => ['user_login', 'user_email'],
    ];
}