<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 12:03
 */

namespace app\admin\controller;


use app\admin\model\WebMsgModel;
use cmf\controller\AdminBaseController;
use FontLib\Table\Type\name;
use think\Db;

/**
 * 站内信
 * Class WebMsgController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   =>'站内信',
 *     'action' =>'index',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 0,
 *     'icon'   =>'cogs',
 *     'remark' =>'基础配置设置'
 * )
 */
class WebMsgController extends AdminBaseController
{
    /**
     * 站内信
     * @adminMenu(
     *     'name'   => '站内信',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'param'  => ''
     * )
     * @throws
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
            $keywordComplex['title']    =   ['like', "%$keyword%"];
        }
        $Query= Db::name('web_msg');

        $list = $Query->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        $list->appends($param);

        $this->assign("data",$list->toArray()['data']);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('start_time', isset($request['start_time']) ? $request['start_time'] : '');
        $this->assign('end_time', isset($request['end_time']) ? $request['end_time'] : '');
        // 渲染模板输出
        return $this->fetch();
    }

    public function send(){
        return $this->fetch();
    }



    /**
     * 发送站内信
     * @adminMenu(
     *     'name'   => '发送站内信',
     *     'parent' => 'site',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '发送站内信',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {

            $data      = $this->request->param('post/a');
            $config    = model("web_msg");

            if(empty($data['create_time'])){
                $data['create_time'] = time();
            }else{
                $data['create_time']=strtotime($data['create_time']);

                if ( $data['create_time'] <time()){
                    $this->error("发布时间不能小于当前时间！");
                }
            }

            $result = $config->validate(true)->allowField(true)->save($data);

            if ($result === false) {
                $this->error($config->getError());
            }
            $this->success("发送成功！", url("web_msg/index"));
        }
    }



    /**
     * 删除站内信
     * @adminMenu(
     *     'name'   => '删除站内信',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除站内信',
     *     'param'  => ''
     * )
     * @throws
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        $res =  Db::name("web_msg")->delete($id);

        $this->success("删除".($res?"成功":"失败")."！", url("web_msg/index"));
    }

    /**
     * 删除站内信
     * @adminMenu(
     *     'name'   => '删除站内信',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除站内信',
     *     'param'  => ''
     * )
     * @throws
     */
    public function info()
    {
        $id = $this->request->param('id', 0, 'intval');
        $data =  WebMsgModel::get($id);
        if(empty($data)){
            $this->error("此站内信已被删除或者不存在!");
        }
        $this->assign('data',$data);
        return $this->fetch();
    }


}