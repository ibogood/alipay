<?php
namespace Ibogood\Alipay\Web;

use Carbon\Carbon;
use Ibogood\Alipay\Payment;

class SdkPayment extends Payment
{

    private $service = 'create_direct_pay_by_user';
	private $seller_email;
	private $seller_account_name;
	private $it_b_pay;
	private $anti_phishing_key;
	private $exter_invoke_ip;
	private $qr_pay_mode;

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
			'seller_id' => $this->partner,
			'out_trade_no' => $this->out_trade_no,
			'subject' => $this->subject,
			'total_fee' => $this->total_fee,
			'body' => $this->body,
			'it_b_pay' => $this->it_b_pay,
			'show_url' => $this->show_url,
			'anti_phishing_key' => $this->anti_phishing_key,
			'exter_invoke_ip' => $this->exter_invoke_ip,
			'_input_charset' => strtolower($this->_input_charset),
			'qr_pay_mode' => $this->qr_pay_mode
		);

		$params = $this->buildRequestParams($parameters);
        return $this->__gateway_new . $this->connectParams($params,true);
	}
	public function setSellerEmail($seller_email)
	{
		$this->seller_email = $seller_email;
		return $this;
	}

	public function setSellerAccountName($seller_account_name)
	{
		$this->seller_account_name = $seller_account_name;
		return $this;
	}


	public function setItBPay($it_b_pay)
	{
		// 超时时间需要进行格式化。


		// 该笔订单允许的最晚付款时间，逾期将关闭交易。
		// 取值范围：1m～15d。
		// m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。
		// 该参数数值不接受小数点，如1.5h，可转换为90m。
		// 该参数在请求到支付宝时开始计时。
		if (! $it_b_pay instanceof Carbon) {
			$it_b_pay = Carbon::parse($it_b_pay);
		}

		$this->it_b_pay = Carbon::now()->diffInMinutes($it_b_pay, false);
		if ($this->it_b_pay < 0) {
			$this->it_b_pay = 0;
		}
		$this->it_b_pay .= 'm';

		return $this;
	}


	public function setExterInvokeIp($exter_invoke_ip)
	{
		$this->exter_invoke_ip = $exter_invoke_ip;
		return $this;
	}

	public function setQrPayMode($qr_pay_mode)
	{
		$this->qr_pay_mode = $qr_pay_mode;
		return $this;
	}

}
