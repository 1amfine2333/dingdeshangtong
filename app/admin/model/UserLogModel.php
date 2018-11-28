<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 16:28
 */

namespace app\admin\model;


use think\Model;

/**
 * Class UserLogModel
 * @package app\admin\model
 * @property $id
 * @property $user_login
 * @property $content
 * @property $action_type
 * @property $create_time
 */
class UserLogModel extends  Model
{
    protected $autoWriteTimestamp=true;


    public static function addLog($user="",$type="",$content=""){
            static::create([
                "user_login"=>$user,
                "content"=>$content,
                "action_type"=>$type,
            ]);
    }


}