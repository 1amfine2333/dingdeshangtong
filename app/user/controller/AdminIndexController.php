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

namespace app\user\controller;

use app\user\model\PlanOrderModel;
use app\user\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 */
class AdminIndexController extends AdminBaseController
{

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '会员',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '会员',
     *     'param'  => ''
     * )
     * @throws
     */
    public function index()
    {
        $content = hook_one('user_admin_index_view');

        if (!empty($content)) {
            return $content;
        }
        $param = $this->request->param();

        $where   = ['user_type'=>2];
        $request = input('request.');

        //时间段查询
        $startTime = empty($request['start_time']) ? 0 : strtotime($request['start_time']);
        $endTime   = empty($request['end_time']) ? 0 : strtotime($request['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['create_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['create_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['create_time'] = ['<= time', $endTime];
            }
        }


        //微信昵称手机号模糊查询
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['user_nickname|mobile']    =   ['like', "%$keyword%"];
        }
        $usersQuery = Db::name('user');
        $plan = new PlanOrderModel();
        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        foreach ($list as $k=>$v){
            $v['create']= $plan->where(['user_id'=> $v['id']  ,'status'=>1])->count();
            $v['success']=$plan->where(['user_id'=> $v['id']  ,'status'=>2])->count();
            $v['refuse']= $plan->where(['user_id'=> $v['id']  ,'status'=>3])->count();
            $v['cancel']= $plan->where(['user_id'=> $v['id']  ,'status'=>4])->count();
            $list[$k] = $v;
        }
        $list->appends($param);
        $this->assign('data',$list->toArray()['data']);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('start_time', isset($request['start_time']) ? $request['start_time'] : '');
        $this->assign('end_time', isset($request['end_time']) ? $request['end_time'] : '');
        // 渲染模板输出
        return $this->fetch();
    }


    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '会员详情',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '会员',
     *     'param'  => ''
     * )
     * @throws
     */
    public function info()
    {
        $type = input("type")?:1;
        $w = ['status'=>$type];
        $id = intval(input("id"));

        $user = new UserModel();
        $plan = new PlanOrderModel();

        $list = $plan->where('user_id',$id)->where($w)->order("create_time DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();

        $count=[
            'create'=> $plan->where(['user_id'=>$id  ,'status'=>1])->count(),
            'success'=>$plan->where(['user_id'=>$id  ,'status'=>2])->count(),
            'refuse'=>$plan->where(['user_id'=>$id  ,'status'=>3])->count(),
            'cancel'=>$plan->where(['user_id'=>$id  ,'status'=>4])->count(),
        ];
        $this->assign("info",$list->toArray()['data']);
        $this->assign('page', $page);
        $this->assign('count', $count);
        $this->assign('data', $user::get($id));
        $this->assign('list',$list);
        return $this->fetch();
    }


    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("user")->where(["id" => $id, "user_type" => ['in','2,3']])->setField('user_status', 0);
            if ($result) {
                $this->success("会员冻结成功！", "adminIndex/index");
            } else {
                $this->error('会员冻结失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据错误,操作失败！');
        }
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            Db::name("user")->where(["id" => $id, "user_type" => 2])->setField('user_status', 1);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
}
