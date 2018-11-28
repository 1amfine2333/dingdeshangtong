<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\admin\model\BaseConfigModel;
use app\admin\model\WebMsgModel;
use app\user\model\ComplaintModel;
use app\user\model\PlanOrderModel;
use app\user\model\UserModel;
use cmf\controller\UserBaseController;
use think\Db;
use think\Validate;

class UserController extends UserBaseController
{

    /**
     * 计划单列表
     * @throws
     */
    public function index()
    {

        $BaseConfig= new BaseConfigModel();
        $tel =  $BaseConfig->select();//热线电话

        $data=[
            'tel'=>$tel,
        ];

        $this->assign('data',$data);
        return $this->fetch(":user");
    }

    public function msg(){

        return view();
    }

}
