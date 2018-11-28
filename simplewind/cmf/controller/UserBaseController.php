<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use app\admin\model\WebMsgModel;
use app\user\model\ComplaintModel;

class UserBaseController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
        $web_msg = new WebMsgModel();
        $comPlaint = new ComplaintModel();
        $tips_my_msg = $web_msg->getTips();
        $tips_complaint = $comPlaint->getTip();
        $data=[
            'tips_my_msg'=>$tips_my_msg,
            'tips_complaint'=>$tips_complaint,
            'has_msg'=>($tips_my_msg||$tips_complaint)?1:0,
        ];
        $this->assign($data);
        $this->checkUserLogin();
    }


}