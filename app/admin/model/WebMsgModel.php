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
use tree\Tree;

class WebMsgModel extends Model
{
    protected $autoWriteTimestamp = true;


    /**
     * 获取未读消息数
     * @param $module
     * @return int
     */
    public function getTips($module='index')
    {
        $cache = cache($this->msgKey());
        $w = [];

        $w['user_type'] =$module=='sales'? 3 : 2;

        if ($cache !== false) {

            $cache = is_array($cache) ? join(',', $cache) : $cache;
            $w = ['id' => ['not in', $cache]];

        }
       return  $this->where($w)->count();
    }

    /**
     * 消息加入已读
     * @param string $id
     */
    public function joinRead($id = "")
    {
        $key = $this->msgKey();
        $cache = cache($key)?:[];

        if ($id)
        {
            $ids = explode(',',$id);
            foreach ($ids as $v)
            {
                if ($v && !in_array($v,$cache))
                {
                    $cache[]=$v;
                }
            }
            cache($key,$cache);
        }
    }


    /**
     * 是否已读
     * @param $id
     * @return bool
     */
    public  function is_read($id=''){
        $cache = cache($this->msgKey())?:[];
        return in_array($id,$cache);
    }


    function msgKey()
    {
        return "MSG_TIPS_" . cmf_get_current_user_id();
    }
}