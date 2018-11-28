<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/15
 * Time: 17:12
 */

namespace app\user\model;


use think\Model;

class PlanOrderModel extends Model
{

    protected $autoWriteTimestamp=true;
    public function getStatusTextAttr($value,$data)
    {
        $status = [1=>'待处理',2=>'已处理',3=>'已拒绝',4=>'已取消'];

        return $status[$data['status']];
    }

}