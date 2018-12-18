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
    $string =  mb_substr(html_text($text), 0, $len,'utf-8');
    if(mb_strlen(html_text($text)) > $len){
        $string = $string.'...';
    }
    return $string;
}

function html_text($params){
    if($params==""|!strlen($params)){
        return $params;
    }
    $params=htmlspecialchars_decode($params);
    $params=strip_tags($params);
    $params=str_replace("&nbsp;","",$params);
    $params=trim($params);
    return $params;
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

/**
 * 添加日志
 * @param $type
 * @param $content
 * @return \app\admin\model\UserLogModel|bool
 * @throws
 */
function addLogs($type,$content){
    $admin_id = cmf_get_current_admin_id();
    if (!$admin_id){
        return false;
    }
    $user = \app\admin\model\UserModel::get($admin_id);
    return \app\admin\model\UserLogModel::addLog(@$user['user_login'], $type, $content);
}



/**
 * 图片压缩
 * @param $filename
 * @param array $config
 * q=质量 default 90
 * w=像素宽度 default 800
 * @return bool
 */
function image($filename, $config = [])
{
    if (!is_file($filename)) {
        return false;
    };
    $info = getimagesize($filename);
    $w = $info[0];
    $h = $info[1];
    $channels = $info[2];
    switch ($channels) {
        case 1:
            $im = imagecreatefromgif($filename);
            break;
        case 2:
            $im = imagecreatefromjpeg($filename);
            break;
        case 3:
            $im = imagecreatefrompng($filename);
            break;
        default:
            $im = false;
    }
    $new_w = 800;
    $quality = @$config['q'] ?: 90;
    $srcW = @ImageSX($im);
    $srcH = @ImageSY($im);
    $to_width = @$config['w'] ?: $new_w;

    $b = $to_width / $srcW;

    //计算出图片缩放后的宽高
    // floor 舍去小数点部分，取整

    $new_h = floor($srcH * $b);
    if ($w <= $new_w || $h <= $new_h) {
        return true;
    }
    $ni = @imageCreateTrueColor($new_w, $new_h);
    @ImageCopyResampled($ni, $im, 0, 0, 0, 0, $new_w, $new_h, $srcW, $srcH);
    // 以 $quality% 的质量转换成 jpeg 格式
    $res = ImageJpeg($ni, $filename, $quality);
    @imagedestroy($im);
    @imagedestroy($ni);
    return $res;
}




define('SECRETKEY', 'xly862d21d3ceafba1b88e5f28650d35');

/**
 * 加密方法
 * @param string $str
 * @return string
 */
function encrypt($str) {
    //AES, 128 ECB模式加密数据
    $str = addPKCS7Padding($str);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
    $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, SECRETKEY, $str, MCRYPT_MODE_CBC, '0000000000000000');
    return base64_encode($encrypt_str);
}

/**
 * 解密方法
 * @param string $str
 * @return string
 */
function decrypt($str) {
    //AES, 128 CBC模式加密数据
    $str = base64_decode($str);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
    $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, SECRETKEY, $str, MCRYPT_MODE_CBC, '0000000000000000');
    $encrypt_str = stripPKSC7Padding($encrypt_str);
    return $encrypt_str?trim($encrypt_str):$encrypt_str;
}

/**
 * 填充算法
 * @param string $source
 * @return string
 */
function addPKCS7Padding($source) {
    $source = trim($source);
    $block = mcrypt_get_block_size('rijndael-128', 'cbc');
    $pad = $block - (strlen($source) % $block);
    if ($pad <= $block) {
        $char = chr($pad);
        $source .= str_repeat($char, $pad);
    }
    return $source;
}

/**
 * 移去填充算法
 * @param string $source
 * @return string
 */
function stripPKSC7Padding($source) {
    $char = substr($source, -1);
    $num = ord($char);
    $source = substr($source, 0, -$num);
    return $source;
}




