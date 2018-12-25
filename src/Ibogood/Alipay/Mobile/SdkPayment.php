<?php
namespace Ibogood\Alipay\Mobile;

use Ibogood\Alipay\Payment;

class SdkPayment extends Payment
{

	private $service = 'mobile.securitypay.pay';
	protected $sign_type = 'RSA';
	private $private_key_path;
	private $public_key_path;
	private $anti_phishing_key;
	private $exter_invoke_ip;

	public function __construct()
	{
		$this->cacert = getcwd() . DIRECTORY_SEPARATOR .'cacert.pem';
	}

	/**
	 * 取得支付链接参数
	 */
	public function getPayPara()
	{
		$parameters = array(
			'service' => $this->service,
			'partner' => trim($this->partner),
			'payment_type' => $this->payment_type,
			'notify_url' => $this->notify_url,
			'seller_id' => $this->seller_id,
			'out_trade_no' => $this->out_trade_no,
			'subject' => $this->subject,
			'total_fee' => $this->total_fee,
			'body' => $this->body,
			'show_url' => $this->show_url,
			'anti_phishing_key' => $this->anti_phishing_key,
			'exter_invoke_ip' => $this->exter_invoke_ip,
			'_input_charset' => trim(strtolower($this->_input_charset))
		);
		$params = $this->buildRequestParams($parameters);
		return $this->connectParams($params,true);
	}

	public function setPrivateKeyPath($private_key_path)
	{
		$this->private_key_path = $private_key_path;
		return $this;
	}

	public function setPublicKeyPath($public_key_path)
	{
		$this->public_key_path = $public_key_path;
		return $this;
	}





}
