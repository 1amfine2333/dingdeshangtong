<?php

namespace app\index\lib;

/**
 * 阿里云短信验证码发送类
 * @author Administrator
 */

class AliSms
{

    /**
     * @var
     *
     * AccessKey ID  LTAIOzhRiKRIWqKM
     *
     * Access Key Secret  1ge5bTKvqiP0mdjpOpsVdLVdAwRh9h
     *
     * 短信模板code  SMS_151795670
     *
     */
    // 保存错误信息

    public $error;

    // Access Key ID

    private $accessKeyId = 'LTAIOzhRiKRIWqKM';

    // Access Access Key Secret

    private $accessKeySecret = '1ge5bTKvqiP0mdjpOpsVdLVdAwRh9h';

    // 签名

    private $signName = 'xxxxxxxx';

    // 模版ID

    private $templateCode = 'SMS_151795670';

    public function __construct($cofig = array(), $templateCode = '')
    {
        define('SESSION_KEY', 'user_mobile_code');

        $cofig = array(

            'accessKeyId' => 'LTAIOzhRiKRIWqKM',

            'accessKeySecret' => '1ge5bTKvqiP0mdjpOpsVdLVdAwRh9h',

            'signName' => '鼎德商砼',

            'templateCode' => $templateCode

        );

        // 配置参数

        $this->accessKeyId = $cofig ['accessKeyId'];

        $this->accessKeySecret = $cofig ['accessKeySecret'];

        $this->signName = $cofig ['signName'];

        $this->templateCode = 'SMS_151795670';

    }


    private function percentEncode($string)
    {

        $string = urlencode($string);

        $string = preg_replace('/\+/', '%20', $string);

        $string = preg_replace('/\*/', '%2A', $string);

        $string = preg_replace('/%7E/', '~', $string);

        return $string;

    }

    /**
     * 签名
     *
     * @param unknown $parameters
     * @param unknown $accessKeySecret
     * @return string
     */

    private function computeSignature($parameters, $accessKeySecret)
    {

        ksort($parameters);

        $canonicalizedQueryString = '';

        foreach ($parameters as $key => $value) {

            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);

        }

        $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));

        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));

        return $signature;

    }


    /**
     * @param $mobile
     * @param $verify_code
     * @return bool
     */
    public static function checkVerify($mobile, $verify_code)
    {

        $session = session(SESSION_KEY);
        if ($session) {

            if ($session['mobile'] == $mobile && $session['code'] == $verify_code && $session['check']<5) {
                session(SESSION_KEY,null);
                return true;
            }else{

                $session['check']++; // 验证次数限制

                session(SESSION_KEY,$session);
            }

            if ($session['check']>=5){
                session(SESSION_KEY,null);
            }
        }
        return false;
    }


    /**
     * @param unknown $mobile
     * @param unknown $code
     *
     */

    public function send_verify($mobile, $code='')
    {

        //$verify_code="撤单成功!";
        $verify_code = $code?:rand(100000,666666);

        $params = array(   //此处作了修改

            'SignName' => $this->signName,

            'Format' => 'JSON',

            'Version' => '2017-05-25',

            'AccessKeyId' => $this->accessKeyId,

            'SignatureVersion' => '1.0',

            'SignatureMethod' => 'HMAC-SHA1',

            'SignatureNonce' => uniqid(),

            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),

            'Action' => 'SendSms',

            'TemplateCode' => $this->templateCode,

            'PhoneNumbers' => $mobile,

            'TemplateParam' => '{"code":"' . $verify_code . '"}' //替换成自己的模板

        );


        //var_dump($params);die;

        // 计算签名并把签名结果加入请求参数

        $params ['Signature'] = $this->computeSignature($params, $this->accessKeySecret);

        // 发送请求（此处作了修改）

        //$url = 'https://sms.aliyuncs.com/?' . http_build_query ( $params );

        $url = 'http://dysmsapi.aliyuncs.com/?' . http_build_query($params);


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($ch);

        curl_close($ch);

        $result = json_decode($result, true);

        //var_dump($result['Code']);die;

        if (isset($result ['Code'])) {

            $this->error = $this->getErrorMessage($result['Code']);

            if ($result['Code'] == 'OK') {
                session(SESSION_KEY, ['mobile' => $mobile, 'code' => $verify_code,'check'=>0]);
                return 1;

            } else {

                return 0;

            }

        }

        return 0;

    }

    /**
     * 获取详细错误信息
     *
     * @param unknown $status
     */

    public function getErrorMessage($status)
    {

        // 阿里云的短信 乱八七糟的(其实是用的阿里大于)

        // https://api.alidayu.com/doc2/apiDetail?spm=a3142.7629140.1.19.SmdYoA&apiId=25450

        $message = array(

            'InvalidDayuStatus.Malformed' => '账户短信开通状态不正确',

            'InvalidSignName.Malformed' => '短信签名不正确或签名状态不正确',

            'InvalidTemplateCode.MalFormed' => '短信模板Code不正确或者模板状态不正确',

            'InvalidRecNum.Malformed' => '目标手机号不正确，单次发送数量不能超过100',

            'InvalidParamString.MalFormed' => '短信模板中变量不是json格式',

            'InvalidParamStringTemplate.Malformed' => '短信模板中变量与模板内容不匹配',

            'InvalidSendSms' => '触发业务流控',

            'InvalidDayu.Malformed' => '变量不能是url，可以将变量固化在模板中'

        );

        if (isset ($message [$status])) {

            return $message [$status];

        }

        return $status;

    }
}