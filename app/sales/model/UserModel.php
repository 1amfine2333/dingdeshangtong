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
namespace app\sales\model;

use app\index\lib\Curl;
use think\Db;
use think\Model;

/**
 * Class UserModel
 * @package app\sales\model
 * @property $mobile
 * @property $open_id
 * @property $access_token
 * @property $refresh_token
 * @property $user_nickname
 * @property $avatar
 * @property $last_login_time
 * @property $create_time
 * @property $last_login_ip
 * @property $user_status
 */
class UserModel extends Model
{


    protected $type = [
        'more' => 'array',
    ];

    private $api = [
        'authorize' => "https://open.weixin.qq.com/connect/oauth2/authorize",
        'token' => "https://api.weixin.qq.com/cgi-bin/token",
        'access_token' => "https://api.weixin.qq.com/sns/oauth2/access_token",
        'refresh_token' => "https://api.weixin.qq.com/sns/oauth2/refresh_token",
        'updateremark' => 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark',
        'userinfo' => "https://api.weixin.qq.com/sns/userinfo",
        'auth' => "https://api.weixin.qq.com/sns/auth",
    ];

    /**
     * @param null $code
     * @return string
     * @throws \think\exception\DbException
     */

    public function login($code = null)
    {

        $state = input('get.state');
        $code = $code ?: input('get.code');
        $sale_key = 'sales_wx';

        if ($code && $state == 'sales') {
            $userAccess = session($sale_key);//session 缓存用户OpenID
            //过期则重新获取
            if (!$userAccess || $userAccess['expires_in'] < time()) {
                $userAccess = $this->getUserAccessToken($code);
                if ($userAccess['expires_in'] < 10000) {//判断过期则重新调用接口获取
                    $userAccess['expires_in'] = time() + $userAccess['expires_in'] - 200;
                }
                session($sale_key, $userAccess);
            }
            return false;
        }

        return false;
    }

    /**
     * 销售经理端授权注册  不存在数据库用户无权注册
     * @param $mobile
     * @return bool
     * @throws
     */
    public function register($mobile)
    {

        $sales = session("sales_wx");

        if (!$sales) {
            return false;
        }
        $openid = $sales['openid'];
        $access_token = $sales['access_token'];
        $refresh_token = $sales['refresh_token'];

        $userInfo = $this->getUser($openid, $access_token);
		file_put_contents('a321.txt',var_export($userInfo,true),FILE_APPEND);
        if (!$userInfo) {
            return false;
        }

        $where = ['mobile' => $mobile, 'user_type' => 3];
        $result = $this->where($where)
            ->update([

                'open_id' => $userInfo['openid'],
                'user_nickname' => $userInfo['nickname'],
                'sex' => $userInfo['sex'],
                'avatar' => $userInfo['headimgurl'],
                'user_status' => 1,
                'refresh_token' => $refresh_token,
                'access_token' => $access_token,
                'last_login_ip' => get_client_ip(0, true),
                'create_time' => time(),

            ]);

        if ($result) {
            cmf_update_current_user($this->where($where)->find());
            return true;
        } else {
            return false;
        }

    }


    /**
     * 获取token
     * @return bool
     */
    public function getAccessToken()
    {
        $AppId = config('AppId');
        $AppSecret = config('AppSecret');
        $api = $this->api['token'] . "?grant_type=client_credential&appid={$AppId}&secret={$AppSecret}";
        $curl = new Curl();
        $result = $curl->get($api);
        $json = json_decode($result, true);
        if (isset($json['errcode'])) {

        } else if (isset($json['access_token'])) {
            return $json['access_token'];
        }
        return false;
    }


    /**
     * 获取用户access_token
     * @param $code
     * @return mixed
     * { "access_token":"ACCESS_TOKEN",
     * "expires_in":7200,
     * "refresh_token":"REFRESH_TOKEN",
     * "openid":"OPENID",
     * "scope":"SCOPE" }
     */
    public function getUserAccessToken($code)
    {
        $AppId = config('AppId');
        $AppSecret = config('AppSecret');
        $api = $this->api['access_token'] . "?appid=$AppId&secret=$AppSecret&code=$code&grant_type=authorization_code";
        $curl = new Curl();
        $result = json_decode($curl->get($api), true);

        if (isset($result['errcode'])) {
            return false;
        }
        return $result;
    }

    /**
     * 刷新 用户access_token
     * @param $refresh_token
     * @return mixed
     */
    public function refresh_token($refresh_token)
    {
        $AppId = config('AppId');
        $api = $this->api['refresh_token'] . "?appid=$AppId&grant_type=refresh_token&refresh_token=$refresh_token";
        $curl = new Curl();
        return $curl->get($api);
    }


    /**
     * 重定向到登录
     * @param $state
     * @return string
     */
    public function redirect_auth($state = 'sales')
    {
        $AppId = config('AppId');
        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . url('login/index');
        $hash = "wechat_redirect";
        $param = [
            'appid' => $AppId,
            'redirect_uri' => $redirect_uri,
            'scope' => 'snsapi_userinfo',
            'response_type' => 'code',
            'state' => $state,
        ];

        return $this->api['authorize'] . "?" . http_build_query($param) . "#" . $hash;
    }


    /**
     * @param $openid
     * @param $access_token
     * @return mixed
     * {    "openid":" OPENID",
     * " nickname": NICKNAME,
     * "sex":"1",
     * "province":"PROVINCE"
     * "city":"CITY",
     * "country":"COUNTRY",
     * "headimgurl": "",
     * "privilege":[ "PRIVILEGE1" "PRIVILEGE2"     ],
     * "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     */
    public function getUser($openid, $access_token)
    {
        //http：GET（请使用https协议）
        $api = $this->api['userinfo'] . "?access_token=$access_token&openid=$openid&lang=zh_CN";
        $curl = new Curl();
        $result = $curl->get($api);
        if (isset($result['errcode'])) {
            return false;
        }

        return json_decode($result, true);
    }

    /**
     * 验证token
     * @param $openid
     * @param $access_token
     * @return mixed
     */
    public function checkToken($openid, $access_token)
    {
        //http：GET（请使用https协议）
        $api = $this->api['auth'] . "?access_token=$access_token&openid=$openid";
        $curl = new Curl();
        $result = $curl->get($api);
        //{ "errcode":0,"errmsg":"ok"}
        return $result;
    }

}
