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
use think\Db;

class ProjectValidate extends Validate
{
    protected $rule = [
        'name'       => 'require|unique:Project',
        'salesman'   => 'require',
    ];

    protected $message = [
        'name.require'       => '请输入工程名称',
        'name.unique'        => '工程名称已经存在',
        'salesman.require'  => '请输入业务员姓名',
    ];
}