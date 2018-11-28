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
namespace app\user\validate;

use think\Validate;

class ComplaintValidate extends Validate
{

    protected $rule = [
        'reply' => 'require|max:100',
    ];
    protected $message = [
        'reply.require' => '请填写回复内容',
        'reply.max'  => '回复内容最多输入100个字符',
    ];

    protected $scene = [

    ];
}