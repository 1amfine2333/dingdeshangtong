<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class WebMsgModel extends Model
{

    /**
     * 获取未读消息数
     * @return int
     */
    public function getTips()
    {
        $w = [];

        $w['to_user_id'] = cmf_get_current_user_id();
        $w['create_time'] = ["<=", time()];
        $w['is_read'] = 0;

        return $this->where($w)->count();
    }

    /**
     * 消息加入已读
     * @param string $id
     */
    public function joinRead($id = "")
    {
        if ($id) {
            $this->where("id", $id)->setField("is_read", '1');
        }
    }

    /**
     * @param $page
     * @param $limit
     * @param $w
     * @return false|\PDOStatement|string|\think\Collection
     * @throws
     */

    public function getMsgList($w, $page, $limit)
    {

        $this->clearMsg();
        return $this->where($w)
            ->order("create_time desc")
            ->page($page, $limit)
            ->select();
    }


    /**刷新用户端消息
     * @param array $ids
     * @param int $type
     * @return int|mixed
     */
    public function setMsg($ids = [], $type = 1)
    {

        $key = "HAS_MSG_";
        if (sizeof($ids)>0)
        {

          /*  foreach ($ids as $id)
            {
                $key = $key . $id;
                if ($type)
                {
                   cache($key, 1);
                } else if (is_null($type)){
                    cache($key, null);
                }
            }*/

            if ($type==1){
                UserModel::update(["has_msg"=>1],['id'=>['in',$ids]]);
            }else if ($type==2){
                UserModel::update(["has_complaint_msg"=>1],['id'=>['in',$ids]]);
            }

            return 0;
        } else {
            $key =$key . cmf_get_current_user_id();
            return cache($key);
        }

    }

    /**
     * 清除消息提醒
     * @param $type
     * @throws
     */
    public function clearMsg($type=1)
    {
        if (!cmf_get_current_user_id()) return;

        $user = UserModel::get(cmf_get_current_user_id());
        if ($type==1){
            $user->has_msg = 0;
        }elseif($type==2){
            $user->has_complaint_msg = 0;
        }
        $user->save();
        cmf_update_current_user($user);
    }

    /**
     * @return bool|int
     * @throws
     */
    public function hasMsg()
    {
        $user = UserModel::get(cmf_get_current_user_id());
        return $user ? ($user['has_msg'] ?: $user['has_complaint_msg']) : 0;
    }


}