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
namespace app\user\model;

use think\Model;

/**
 * Class ComplaintModel
 * @package app\user\model
 * @property $id
 * @property $type
 * @property $user_id
 * @property $content
 * @property $reply
 * @property $create_time
 * @property $status
 * @property $is_read
 */
class ComplaintModel extends Model
{

    protected $autoWriteTimestamp = true;

    /**
     * 获取建议计划已回复信息数
     * @return int|string
     */
    public function getTip(){
      return  $this
          ->where('user_id',cmf_get_current_user_id())
          ->where('is_read',1)
          ->whereIn('type',[1,2])
          ->where('status',1)
          ->count();
    }


    /**
     * 标记已读
     * @param $id
     */
    public function setRead($id){
        $this->where('id',$id)->setField('is_read',2);
    }
}