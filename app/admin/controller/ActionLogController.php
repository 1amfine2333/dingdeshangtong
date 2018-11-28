<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 12:03
 */

namespace app\admin\controller;


use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class SettingController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   =>'操作日志',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 0,
 *     'icon'   =>'cogs',
 *     'remark' =>'操作日志入口'
 * )
 */
class ActionLogController extends AdminBaseController
{
    /**
     * 操作日志
     * @adminMenu(
     *     'name'   => '操作日志',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $where   = [];
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
            $keywordComplex['user_login']    =   ['like', "%$keyword%"];
        }
        $usersQuery = Db::name('user_log');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        $list->appends($param);

        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('start_time', isset($request['start_time']) ? $request['start_time'] : '');
        $this->assign('end_time', isset($request['end_time']) ? $request['end_time'] : '');
        $this->assign("data",$list->toArray()['data']);
        // 渲染模板输出
        return $this->fetch();
    }



}