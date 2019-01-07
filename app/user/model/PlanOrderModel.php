<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/15
 * Time: 17:12
 */

namespace app\user\model;


use think\helper\Time;
use think\Model;

class PlanOrderModel extends Model
{

    protected $autoWriteTimestamp=true;
    public function getStatusTextAttr($value,$data)
    {
        $status = [1=>'待处理',2=>'已处理',3=>'已拒绝',4=>'已取消'];

        return $status[$data['status']];
    }


    function getNumber(){
        $number = date("Ymd");

        $today = Time::today()[0];
        $orderNum = $this->where(["create_time"=>['>= time',$today]])->count()+1;
        if ($orderNum<10){
            $orderNum = "00".$orderNum;
        }elseif($orderNum<100){
            $orderNum ="0".$orderNum;
        }
        return $number.$orderNum;
    }

}