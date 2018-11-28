<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 16:20
 */

/**
 * @param $value
 * @param $array
 * @return bool|array
 */
function deep_in_array($value, $array)
{
    foreach ($array as $item) {
        if (!is_array($item)) {
            if ($item == $value) {
                return true;
            } else {
                continue;
            }
        }

        if (in_array($value, $item)) {
            return $item;
        } else if (deep_in_array($value, $item)) {
            return $item;
        }
    }
    return false;
}

/**
 * 截取前20位
 * @param string $text
 * @param int $len
 * @return bool|string
 */
function html($text = "", $len = 20)
{
    return substr($text, 0, $len);
}

/**
 * 过滤字段
 * @param $keys
 * @param array $data
 * @return array
 */
function check_field($keys, $data = [])
{
    $d = [];
    $keys = is_array($keys)?$keys:explode(",",$keys);
    foreach ($data as $key => $val) {
        if (in_array($key, $keys)) {
            $d[$key]=$val;
        }
    }
    return $d;
}