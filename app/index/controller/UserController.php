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
use app\index\lib\Pinyin;
use app\user\model\ComplaintModel;
use app\user\model\PlanOrderModel;
use app\user\model\UserModel;
use cmf\controller\UserBaseController;
use think\Db;
use think\helper\Time;
use think\Validate;

class UserController extends UserBaseController
{

    /**
     * 计划单列表
     * @throws
     */
    public function index()
    {

        $BaseConfig = new BaseConfigModel();
        $tel = $BaseConfig->select();//热线电话

        $data = [
            'tel' => $tel,
        ];

        $this->assign('data', $data);
        return $this->fetch();
    }


    public function info()
    {
        return $this->fetch();
    }

    public function mobile()
    {

        if ($this->request->isPost()) {

            $data = input('post.');
            $result = $this->validate($data, "Index.edit");
            if ($result !== true) {
                $this->error($result);
            }


            $mobile = input('post.edit_mobile');
            $sms_code = input('post.sms_code');
            if ($sms_code!=123456){
                $this->error('验证码错误!');
            }

            $result = (new UserModel)->isUpdate(true)->save(['mobile' => $mobile], ['id' => cmf_get_current_user_id()]);
            if ($result) {
                $user = cmf_get_current_user();
                $user['mobile'] = $mobile;
                cmf_update_current_user($user);

                $this->success("修改成功!", url('user/index'));
            } else {
                $this->error('修改失败，请重新提交!');
            }


        }

        return $this->fetch();
    }


    /**
     * 消息列表
     * @return \think\response\View
     * @throws
     */
    public function msg()
    {

        $page = $this->request->param('page');
        $limit = 15;
        if (request()->isAjax()) {
            $web = new WebMsgModel();
            //如果传入状态参数 则根据状态查询
            $where['user_type'] = ['in', [0, cmf_get_current_user()['user_type']]];

            $list = $web->field("id,title,create_time")->where($where)->order("create_time desc")->page($page, $limit)->select();

            foreach ($list as $k => $value) {
                $value['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
                $value['is_read'] = $web->is_read($value['id']);
                $list[$k] = $value;
            }

            $data['list'] = $list;
            $data['pages'] = ceil($web->where($where)->count() / $limit);

            $this->success('success', null, $data);
        }
        return view();
    }

    /**
     * 消息详情
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function msg_detail()
    {
        $id = input("id", 0, 'intval');

        $status = ['待处理', '已处理'];
        if ($id) {
            $web = new WebMsgModel();
            $data = $web::get(['id' => $id, 'user_type' => ['in', [0, cmf_get_current_user()['user_type']]]]);
            if ($data) {
                $data['create_time'] = date("Y-m-d H:i:s", $data['create_time']);
                $web->joinRead($data['id']);
                // $data['content'] = htmlspecialchars($data['content']);
            }

            $this->assign('data', $data);
        }
        return $this->fetch();
    }
}
