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
        'content' => 'require|length:1,300',
       // 'plan_number' => 'number|length:14',
    ];
    protected $message = [
        'type.require' => '请选择类型',
        'type.in' => '类型错误',
        'content.require' => '请输入反馈内容',
        'content.length' => '反馈内容最多不超过300字',
        'plan_number' => '请填写正确的计划单号码',
    ];
    protected $scene = [
    ];

}