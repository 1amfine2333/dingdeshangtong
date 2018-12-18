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

class PlanOrderValidate extends Validate
{

    protected $regex = ['mobile'=>"^(13|14|15|17|18|19)[0-9]{9}$"];

    protected $rule = [
        'project_address' => 'require',
        'unit_name' => 'require',
        'project_name' => 'require',
       // 'salesman' => 'require',
        'pouring_type' => 'require',
        'pouring_part' => 'require',
        'pouring_label' => 'require',
        'slump' => 'require',
        'pouring_time' => 'require',
        'distance' => 'require',
        'plan_square' => 'require',
        'principal' => 'require',
        'mobile' => 'require|regex:mobile',
    ];
    protected $message = [

        'project_address' => '请输入工程地址',
        'unit_name' => '请输入单位名称',
        'project_name' => '请输入工程名',
       // 'salesman' => '请输入业务员姓名',
        'pouring_type' => '选择浇筑方式',
        'pouring_part' => '请选择浇筑部位',
        'pouring_label' => '请选择浇筑标号',
        'slump' => '请选择坍落度',
        'pouring_time' => '请选择浇筑时间',
        'distance' => '输输入商砼距离',
        'plan_square' => '提交失败,请重试',
        'principal' => '请填写现场负责人',
        'mobile.require' => '请输入现场负责人电话',
        'mobile.regex' => '请输入正确的手机号',
        //'number' => '请填写回复内容',
        //'manager' => '请填写回复内容',
        //'manager_id' => '请填写回复内容',

    ];

    protected $scene = [
    ];
}