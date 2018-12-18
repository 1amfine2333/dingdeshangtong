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

use app\admin\model\WebMsgModel;
use app\user\model\ComplaintModel;
use app\user\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminIndexController
 * @package app\user\controller
 */
class ComplaintController extends AdminBaseController
{
    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '投诉建议列表',
     *     'parent' => 'user/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '投诉建议列表',
     *     'param'  => ''
     * )
     * @throws
     */
    public function index()
    {
        $param = $this->request->param();
        $where   =[];
        $request = input('request.');

        //时间段查询
        $startTime = empty($request['start_time']) ? 0 : strtotime($request['start_time']);
        $endTime   = empty($request['end_time']) ? 0 : strtotime($request['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['c.create_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {

            if (!empty($startTime)) {
                $where['c.create_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['c.create_time'] = ['<= time', $endTime];
            }

        }
        if(!empty($request['type'])){
            $type = $request['type'];
            $where['c.type']    = $type;
        }

        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['content']    =   ['like', "%$keyword%"];
        }


        $list = Db::name('complaint')
            ->alias("c")
            ->field('c.id,c.create_time,c.status,u.user_nickname,u.mobile,c.*')
            ->join('user u', 'c.user_id = u.id')
            ->order("c.create_time DESC")
            ->where($where)
            ->paginate(10);
        $list->appends($param);

        $this->assign("data",$list->toArray()['data']);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        $this->assign('type', input('type'));

        $this->assign('start_time', isset($request['start_time']) ? $request['start_time'] : '');
        $this->assign('end_time', isset($request['end_time']) ? $request['end_time'] : '');
        // 渲染模板输出
        return $this->fetch();
    }





    /**
     * 详情信息
     * @adminMenu(
     *     'name'   => '详情信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '详情信息',
     *     'param'  => ''
     * )
     * @throws
     */
    public function info()
    {
        $user = new ComplaintModel();
        $data = $user->alias("c")
            ->field('u.user_nickname,u.mobile,c.status,c.*')
            ->join('user u','c.user_id=u.id')
            ->where("c.id",intval(input('id')))
            ->order("c.create_time DESC")->find();
        // 获取分页显示

        $this->assign('data', $data);
        return $this->fetch();
    }


    /**
     * 处理投诉建议
     * @adminMenu(
     *     'name'   => '处理投诉建议 post提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '处理投诉建议',
     *     'param'  => ''
     * )
     */
    public function replyPost()
    {

        if ($this->request->isPost()) {
            $complaint = new ComplaintModel();
            $result = $this->validate($this->request->param(), 'Complaint');
            if ($result !== true) {
                $this->error($result);
            } else {


                $id = input("id",0,'intval');

                $data = $complaint::get($id);
                if ($data && $data->status===0){
                    $_POST['status']=1;
                    $_POST['is_read']=1;
                    $result = $complaint->isUpdate(true)->save($_POST);

                    if ($result) {
                        if($data['type']!==3){
                            (new WebMsgModel())->setMsg([$data['user_id']],2);
                        }
                        addLogs("管理员回复","回复一条投诉建议");
                        $this->success("回复成功！", url("complaint/index"));
                    } else {
                        $this->error("计划单不存在或已回复！");
                    }
                }else{
                    $this->error("计划单不存在或已回复！");
                }

            }
        }
    }


    /**
     * 删除多条处理投诉建议
     * 处理投诉建议
     * @adminMenu(
     *     'name'   => '删除多条处理投诉建议',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除多条处理投诉建议',
     *     'param'  => ''
     * )
     */

    public function delMultiple()
    {
        $id = $this->request->param('id', "", 'string');
        $len  = model('Complaint')->whereIn('id',$id)->delete();
        if ( $len) {
            addLogs("管理员删除","删除{$len}条投诉建议");
            $this->success("删除成功！",url("complaint/index"),$id);
        } else {

            $this->error("删除失败！",null,$id);
        }
    }


    public function getMsg(){
        $complaint = new ComplaintModel();
        $key = 'COMPLAINT_IDS';
        $cacheId = cache($key)?:0;
        $count =$complaint->whereNotIn('id',$cacheId)->where('status',0)->count('id');
        return json(['count'=>$count]);
    }

    public function setMsg(){
        $complaint = new ComplaintModel();
        $key = 'COMPLAINT_IDS';
        $ids = $complaint->where('status',0)->column('id');
        if (count($ids)>0){
            cache($key,$ids);
        }
        return json(['set_length'=>count($ids)]);
    }


}
