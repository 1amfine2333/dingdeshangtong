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

class CategoryValidate extends Validate
{
    protected $rule = [
        'parent_id'  => 'require',
        'name'       => 'require',
    ];

    protected $message = [
        'parent_id.require'  => '一级分类不能为空',
        'name.require'       => '分类名称不能为空',
    ];
}