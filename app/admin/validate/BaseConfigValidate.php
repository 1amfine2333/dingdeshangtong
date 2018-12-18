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

class BaseConfigValidate extends Validate
{
    protected $regex = [ 'tel' => '\d{11}'];

    protected $rule = [
        'tel_no' => 'require|regex:tel',
        'title' => 'require',
    ];

    protected $message = [
        'tel_no.require' => '请输入电话号码',
        'tel_no' => '请输入正确的电话号码',
        'title.require' => '请输入名称',
    ];

}