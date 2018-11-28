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

class WebMsgValidate extends Validate
{
    protected $rule = [
        'title' => 'require|max:15',
        'content' => 'require',
    ];

    protected $message = [
        'title.require' => '请输入标题',
        'title.max' => '标题不能超过15字',
        'content' => '请输入内容',
    ];

}