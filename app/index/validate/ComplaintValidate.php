<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 14:35
 */

namespace app\index\validate;


use think\Validate;

class ComplaintValidate extends Validate
{
    protected $rule = [
        'type' => 'require|in:1,2,3',
        'content' => 'require',
        'plan_number' => 'number|length:14',
    ];
    protected $message = [
        'type.require' => '请选择类型',
        'type.in' => '类型错误',
        'content.require' => '请填写内容',
        'plan_number' => '请填写正确的计划单号码',
    ];
    protected $scene = [
    ];

}