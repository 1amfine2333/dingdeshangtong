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
namespace app\index\validate;

use think\Validate;

class  IndexValidate extends Validate
{

    protected $regex = ['mobile'=>"^(13|14|15|17|18|19)[0-9]{9}$"];

    protected $rule = [
        'mobile' => 'require|regex:mobile',
        'sms_code' => 'require|length:6',
    ];
    protected $message = [
        'mobile' => '请输入手机号',
        'mobile.regex' => '请输入正确的手机号',
        'sms_code' => '请输入短信验证码',
    ];

    protected $scene = [
        'login'=>['sms_code','mobile']
    ];
}