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
namespace app\sales\validate;

use think\Validate;

class  IndexValidate extends Validate
{

    protected $regex = ['mobile'=>"^(13|14|15|17|18|19)[0-9]{9}$"];

    protected $rule = [
        'mobile' => 'require|regex:mobile',
        'edit_mobile' => 'require|regex:mobile',
        'password' => 'require',
    ];
    protected $message = [
        'mobile.require' => '请输入手机号',
        'mobile.regex' => '请输入正确的手机号',

        'edit_mobile.require' => '请输入手机号',
        'edit_mobile.regex'   => '请输入正确的手机号',
        'edit_mobile.unique'  => '手机号已存在!',

        'password.require' => '请输入密码',
    ];

    protected $scene = [
        'salesLogin'=>['password','mobile'],
        'edit'=>['edit_mobile','sms_mobile']
    ];
}