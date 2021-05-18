<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: pay_aliconfig.php 30871 2021-05-01 09:32:37Z Aphly $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class pay_aliconfig
{
	protected $protocol = 'https';
	protected $gatewayHost = 'openapi.alipay.com';
	protected $signType = 'RSA2';
	protected $charset = 'utf-8';
	protected $alipayPublicKey;
	protected $merchantPrivateKey;
	public $appId;
	public $seller_id;
	protected $version='1.0';
	protected $method;
	protected $product_code;
	
	public $merchantCertPath;
	public $alipayCertPath;
	public $alipayRootCertPath;
	public $merchantCertSN;
	public $alipayCertSN;
	public $alipayRootCertSN;
	public $encryptKey;
	public $httpProxy;
	public $ignoreSSL;

	public $status = 0;
	public $returnUrl;
	public $notifyUrl;
	public $total_amount;
	public $outTradeNo;
	public $subject;
	public $totalAmount;
	public $passback_params;
	public $refund_amount;

	public function __construct()
	{
		global $_G;
		if($_G['mobile']){
			$this->method = 'alipay.trade.wap.pay';
			$this->product_code = 'QUICK_WAP_WAY';
		}else{
			$this->method = 'alipay.trade.page.pay';
			$this->product_code = 'FAST_INSTANT_TRADE_PAY';
		}
		$this->status = intval($_G['setting']['pay_alipay_status']);
		$this->notifyUrl = $_G['siteurl'].'api/pay/notify.php';
		$this->appId = $_G['setting']['pay_alipay_appid'];
		$this->seller_id = $_G['setting']['pay_alipay_seller_id'];
		$this->alipayPublicKey = $_G['setting']['pay_alipay_publickey'];
		$this->merchantPrivateKey = $_G['setting']['pay_alipay_merchantprivatekey'];
	}

	public function getGatewayServerUrl()
	{
		return $this->protocol . '://' . $this->gatewayHost . '/gateway.do';
	}

	public function setReturnUrl($returnUrl)
	{
		$this->returnUrl = $returnUrl;
	}

	public function setNotifyUrl($notifyUrl='')
	{
		$this->notifyUrl = $notifyUrl;
	}

	public function setOutTradeNo($outTradeNo)
	{
		$this->outTradeNo = $outTradeNo;
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	public function setPassbackParams($passback_params)
	{
		$this->passback_params = urlencode(trim($passback_params));
	}

	public function price($num)
	{
		$num = ceil(floatval($num)*100)/100;
		return $num>0?$num:0;
	}

	public function setTotalAmount($totalAmount)
	{
		return $this->totalAmount = $this->price($totalAmount);
	}

	public function setRefundAmount($refund_amount)
	{
		return $this->refund_amount = $this->price($refund_amount);
	}

}