<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25 0025
 * Time: 18:19
 */

namespace Ibogood\Alipay;
use Illuminate\Support\Facades\Input;

class Payment
{
    protected $__gateway_new = 'https://mapi.alipay.com/gateway.do?';
    protected $__https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    protected $__http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    protected $transport;
    protected $cacert;

    protected $partner;
    protected $_input_charset = 'UTF-8';
    protected $sign_type = 'MD5';
    protected $notify_url;
    protected $return_url;
    protected $out_trade_no;
    protected $payment_type = 1;
    protected $seller_id;
    protected $total_fee;
    protected $subject;
    protected $body;
    protected $show_url;
    protected $key;

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }
    public function setNotifyUrl($notify_url)
    {
        $this->notify_url = $notify_url;
        return $this;
    }
    public function setReturnUrl($return_url)
    {
        $this->return_url = $return_url;
        return $this;
    }

    public function setOutTradeNo($out_trade_no)
    {
        $this->out_trade_no = $out_trade_no;
        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function setSellerId($seller_id)
    {
        $this->seller_id = $seller_id;
        return $this;
    }
    public function setTotalFee($total_fee)
    {
        $this->total_fee = $total_fee;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    public function setSignType($sign_type)
    {
        $this->sign_type = $sign_type;
        return $this;
    }
    public function setShowUrl($show_url)
    {
        $this->show_url = $show_url;
        return $this;
    }
    public function setCacert($cacert)
    {
        $this->cacert = $cacert;
        return $this;
    }
    /**
     * 连接参数
     * @param $params
     * @param bool $isEncodeUrl 是否编码参数
     * @return string 返回字符串
     */
    protected function connectParams($params,$isEncodeUrl=false){

        $string = '';
        foreach($params as $key=>$val){
            $string .= $key . '=' . ($isEncodeUrl ? urlencode($val) : $val) . '&';
        }
        //去掉最后一个&字符
        $string = substr($string, 0, strlen($string) - 1);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }

        return $string;
    }


    /**
     * 生产签名字符串
     * @param $connString 需要签名的字符串
     * @param $key 私钥
     * @return 签名结果
     */
    protected function md5Sign($connString, $key)
    {
        return md5( $connString . $key);
    }

    /**
     * 验证签名
     * @param $connString 需要签名的字符串
     * @param $sign 签名结果
     * @param $key 私钥
     * @return bool 签名结果
     */
    protected function md5Verify($connString, $sign, $key)
    {
        return $this->md5Sign($connString,$key) == $sign;
    }
    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * @return 验证结果
     */
    protected function rsaVerify($data, $public_key_path, $sign)
    {
        $pubKey = file_get_contents($public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * @return 签名结果
     */
    protected function rsaSign($data, $private_key_path)
    {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
    /**
     * 验证签名
     * @param $params 参数
     * @return bool
     */
    protected function verifySign($params){
        $sign = $params['sign'];
        $connString = $this->connectParams($this->sortParams($this->filterParams($params)));
        switch (strtoupper(trim($this->sign_type))) {
            case 'MD5':
                return $this->md5Verify($connString, $sign, $this->key);
                break;
            case 'RSA':
                return $this->rsaVerify($connString, $this->public_key_path, $sign);
                break;
        }
        return false;
    }



    /**
     * 对数组排序
     * @param $params array 排序前的数组
     * @return array 排序后的数组
     */
    protected function sortParams($params)
    {
        ksort($params);
        reset($params);
        return $params;
    }

    protected function buildSign($params){
        $connString = $this->connectParams($params);
        switch (strtoupper(trim($this->sign_type))) {
            case 'MD5':
                return $this->md5Sign($connString, $this->key);
                break;
            case 'RSA':
                return $this->rsaSign($connString, trim($this->private_key_path));
                break;
        }
        return '';
    }

    /**
     * 除去数组中的空值和签名参数
     * @$params $para 签名参数组
     * @return 去掉空值与签名参数后的新签名参数组
     */
    protected function filterParams($params)
    {
        $params = array_filter($params,function($val,$key){
            return  !in_array($key,['sign','sign_type']) && !empty($val);
        },ARRAY_FILTER_USE_BOTH);
        return $params;
    }
    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    protected function buildRequestParams($params){
        //除去待签名参数数组中的空值和签名参数
        $params = $this->sortParams($this->filterParams($params));
        $params['sign'] = $this->buildSign($params);
        $params['sign_type'] = strtoupper(trim($this->sign_type));

        return $params;
    }

    /**
     * 验证消息是否是支付宝发出的合法消息
     */
    public function verify()
    {
        $data = Input::all();
        // 获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
        $response = 'true';
        if (! empty($data['notify_id'])) {
            $response = $this->getResponse($data['notify_id']);
        }
        // 验证
        // $response_txt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
        // isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
        return preg_match('/true$/i', $response) && $this->verifySign($data);
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    protected function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->transport));
        $partner = trim($this->partner);
        $url = $transport === 'https' ? $this->__https_verify_url: $this->__http_verify_url;
        $url .= sprintf( 'partner=%s&notify_id=%s' ,$partner, $notify_id);
        return $this->getHttpResponseGET($url, $this->cacert);

    }
    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * @return 远程输出的数据
     */
    protected function getHttpResponseGET($url, $cacert_url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); //证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }
}