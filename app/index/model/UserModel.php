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
namespace app\index\model;

use app\admin\model\UserLogModel;
use app\index\lib\Curl;
use think\Db;
use think\Model;

/**
 * Class UserModel
 * @package app\index\model
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
     * @throws \think\exception\DbException
     */

    public function login($code = null)
    {

        $state = input('get.state');
        $code = $code ?: input('get.code');
        $session_key = "access_token";

        if ($code) {
            $userAccess = session($session_key);//session 缓存用户OpenID

            //过期则重新获取
            if (!$userAccess || $userAccess['expires_in'] < time()) {
                $userAccess = $this->getUserAccessToken($code);
                if ($userAccess['expires_in'] < 10000) {
                    $userAccess['expires_in'] = time() + $userAccess['expires_in'] - 200;
                }
                session($session_key, $userAccess);
            }



            $user_type = 2; //用户
            if ($userAccess !== false) {
                //登录获取access_token 成功

                $openid = $userAccess['openid'];
                $access_token = $userAccess['access_token'];
                $refresh_token = $userAccess['refresh_token'];

                $user = $this->get(['open_id' => $openid, 'user_type' => $user_type]);

                if (empty($user)) {

                    $userInfo = $this->getUser($openid, $access_token);
                    $id = $this->insertGetId([
                        'open_id' => $openid,
                        'user_nickname' => $userInfo['nickname'],
                        'sex' => $userInfo['sex'],
                        'avatar' => $userInfo['headimgurl'],
                        'user_status' => 2,
                        'user_type' => $user_type,
                        'refresh_token' => $refresh_token,
                        'access_token' => $access_token,
                        'last_login_ip' => request()->ip(),
                        'create_time' => time(),
                    ]);

                    $user = $this->get($id);
                    UserLogModel::addLog("", "用户登录", '新用户获取信息成功! OpenId:' . $openid);
                    //首次进入新用户
                    cmf_update_current_user($user);
                    return redirect(url('index/index'));

                } elseif ($user->user_status == 2) {

                    //未认证用户

                    UserLogModel::addLog($user->user_nickname, "用户登录", '未认证用户进入成功!');
                    cmf_update_current_user($user);

                } else if ($user->user_status == 1) {

                    //正常账号登录

                    $user->where(['open_id'=>$openid,'user_type'=>$user_type])->update([
                        'access_token' => $access_token,
                        'refresh_token' => $refresh_token,
                        'last_login_ip' => request()->ip(),
                        'last_login_time' => time(),
                    ]);

                    //正常用户
                    UserLogModel::addLog($user->user_nickname, "用户登录", '登录成功!');
                    cmf_update_current_user($user);
                    return redirect(url('index/index'));

                } else {
                    return redirect(url('login/disable'));
                }

            } else {

                return redirect(url("login/index"));
            }
        }

        return false;

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
            UserLogModel::addLog('', '用户登录', '获取Access Token失败 ' . @$json['errmsg']);
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
    public function redirect_auth($state = 'login')
    {
        $AppId = config('AppId');
        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . "/index/login/index.html";
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
     * "headimgurl":    "http://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
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


    public function doMobile($user)
    {
        $result = $this->where('mobile', $user['mobile'])->find();

        if (!empty($result)) {

            $comparePasswordResult = cmf_compare_password($user['user_pass'], $result['user_pass']);
            $hookParam = [
                'user' => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                //拉黑判断。
                if ($result['user_status'] == 0) {
                    return 3;
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip' => get_client_ip(0, true),
                ];
                $this->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user' => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    public function doName($user)
    {
        $result = $this->where('user_login', $user['user_login'])->find();
        if (!empty($result)) {
            $comparePasswordResult = cmf_compare_password($user['user_pass'], $result['user_pass']);
            $hookParam = [
                'user' => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                //拉黑判断。
                if ($result['user_status'] == 0) {
                    return 3;
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip' => get_client_ip(0, true),
                ];
                $result->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user' => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    public function doEmail($user)
    {

        $result = $this->where('user_email', $user['user_email'])->find();

        if (!empty($result)) {
            $comparePasswordResult = cmf_compare_password($user['user_pass'], $result['user_pass']);
            $hookParam = [
                'user' => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {

                //拉黑判断。
                if ($result['user_status'] == 0) {
                    return 3;
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip' => get_client_ip(0, true),
                ];
                $this->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user' => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    public function register($user, $type)
    {
        switch ($type) {
            case 1:
                $result = Db::name("user")->where('user_login', $user['user_login'])->find();
                break;
            case 2:
                $result = Db::name("user")->where('mobile', $user['mobile'])->find();
                break;
            case 3:
                $result = Db::name("user")->where('user_email', $user['user_email'])->find();
                break;
            default:
                $result = 0;
        }

        $userStatus = 1;

        if (cmf_is_open_registration()) {
            $userStatus = 2;
        }

        if (empty($result)) {
            $data = [
                'user_login' => empty($user['user_login']) ? '' : $user['user_login'],
                'user_email' => empty($user['user_email']) ? '' : $user['user_email'],
                'mobile' => empty($user['mobile']) ? '' : $user['mobile'],
                'user_nickname' => '',
                'user_pass' => cmf_password($user['user_pass']),
                'last_login_ip' => get_client_ip(0, true),
                'create_time' => time(),
                'last_login_time' => time(),
                'user_status' => $userStatus,
                "user_type" => 2,//会员
            ];
            $userId = Db::name("user")->insertGetId($data);
            $data = Db::name("user")->where('id', $userId)->find();
            cmf_update_current_user($data);
            $token = cmf_generate_user_token($userId, 'web');
            if (!empty($token)) {
                session('token', $token);
            }
            return 0;
        }
        return 1;
    }

    /**
     * 通过邮箱重置密码
     * @param $email
     * @param $password
     * @return int
     */
    public function emailPasswordReset($email, $password)
    {
        $result = $this->where('user_email', $email)->find();
        if (!empty($result)) {
            $data = [
                'user_pass' => cmf_password($password),
            ];
            $this->where('user_email', $email)->update($data);
            return 0;
        }
        return 1;
    }

    /**
     * 通过手机重置密码
     * @param $mobile
     * @param $password
     * @return int
     */
    public function mobilePasswordReset($mobile, $password)
    {
        $userQuery = Db::name("user");
        $result = $userQuery->where('mobile', $mobile)->find();
        if (!empty($result)) {
            $data = [
                'user_pass' => cmf_password($password),
            ];
            $userQuery->where('mobile', $mobile)->update($data);
            return 0;
        }
        return 1;
    }

    public function editData($user)
    {
        $userId = cmf_get_current_user_id();

        if (isset($user['birthday'])) {
            $user['birthday'] = strtotime($user['birthday']);
        }

        $field = 'user_nickname,sex,birthday,user_url,signature,more';

        if ($this->allowField($field)->save($user, ['id' => $userId])) {
            $userInfo = $this->where('id', $userId)->find();
            cmf_update_current_user($userInfo->toArray());
            return 1;
        }
        return 0;
    }

    /**
     * 用户密码修改
     * @param $user
     * @return int
     */
    public function editPassword($user)
    {
        $userId = cmf_get_current_user_id();
        $userQuery = Db::name("user");
        if ($user['password'] != $user['repassword']) {
            return 1;
        }
        $pass = $userQuery->where('id', $userId)->find();
        if (!cmf_compare_password($user['old_password'], $pass['user_pass'])) {
            return 2;
        }
        $data['user_pass'] = cmf_password($user['password']);
        $userQuery->where('id', $userId)->update($data);
        return 0;
    }

    public function comments()
    {
        $userId = cmf_get_current_user_id();
        $userQuery = Db::name("Comment");
        $where['user_id'] = $userId;
        $where['delete_time'] = 0;
        $favorites = $userQuery->where($where)->order('id desc')->paginate(10);
        $data['page'] = $favorites->render();
        $data['lists'] = $favorites->items();
        return $data;
    }

    public function deleteComment($id)
    {
        $userId = cmf_get_current_user_id();
        $userQuery = Db::name("Comment");
        $where['id'] = $id;
        $where['user_id'] = $userId;
        $data['delete_time'] = time();
        $userQuery->where($where)->update($data);
        return $data;
    }

    /**
     * 绑定用户手机号
     */
    public function bindingMobile($user)
    {
        $userId = cmf_get_current_user_id();
        $data ['mobile'] = $user['username'];
        Db::name("user")->where('id', $userId)->update($data);
        $userInfo = Db::name("user")->where('id', $userId)->find();
        cmf_update_current_user($userInfo);
        return 0;
    }

    /**
     * 绑定用户邮箱
     */
    public function bindingEmail($user)
    {
        $userId = cmf_get_current_user_id();
        $data ['user_email'] = $user['username'];
        Db::name("user")->where('id', $userId)->update($data);
        $userInfo = Db::name("user")->where('id', $userId)->find();
        cmf_update_current_user($userInfo);
        return 0;
    }
}
