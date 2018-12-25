<?php
namespace Ibogood\Alipay\Wap;

use Ibogood\Alipay\Payment;

class SdkPayment extends Payment
{
	private $service = 'alipay.wap.create.direct.pay.by.user';
	private $it_b_pay;
	private $exter_invoke_ip;
	private $app_pay = 'Y';

	public function __construct()
	{
		$this->cacert = getcwd() . DIRECTORY_SEPARATOR . 'cacert.pem';
	}

	/**
	 * 取得支付链接
	 */
	public function getPayLink()
	{
		$parameters = array(
			'service' => $this->service,
			'partner' => $this->partner,
			'payment_type' => $this->payment_type,
			'notify_url' => $this->notify_url,
			'return_url' => $this->return_url,
			'seller_id' => $this->seller_id,
			'out_trade_no' => $this->out_trade_no,
			'subject' => $this->subject,
			'total_fee' => $this->total_fee,
			'body' => $this->body,
			'it_b_pay' => $this->it_b_pay,
			'show_url' => $this->show_url,
			'exter_invoke_ip' => $this->exter_invoke_ip,
			'app_pay' => $this->app_pay,
			'_input_charset' => strtolower($this->_input_charset)
		);

        $params = $this->buildRequestParams($parameters);

		return $this->__gateway_new . $this->connectParams($params,true);
	}

	public function setItBPay($it_b_pay)
	{
		$this->it_b_pay = $it_b_pay;
		return $this;
	}


	public function setExterInvokeIp($exter_invoke_ip)
	{
		$this->exter_invoke_ip = $exter_invoke_ip;
		return $this;
	}

	public function setAppPay($app_pay)
	{
		$this->app_pay = $app_pay;
		return $this;
	}


}
