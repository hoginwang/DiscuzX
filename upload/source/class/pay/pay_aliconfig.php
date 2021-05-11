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
	public $protocol = 'https';
	public $gatewayHost = 'openapi.alipay.com';
	public $signType = 'RSA2';
	public $charset = 'utf-8';
	
	public $merchantCertPath;
	public $alipayCertPath;
	public $alipayRootCertPath;
    public $merchantCertSN;
    public $alipayCertSN;
    public $alipayRootCertSN;
    
    public $encryptKey;
    public $httpProxy;
    public $ignoreSSL;

	public $appId;
	public $alipayPublicKey;
	public $merchantPrivateKey;
	public $seller_id;
    public $returnUrl;
	public $notifyUrl;
	
	public $total_amount;
	public $outTradeNo;
	public $subject;
	public $totalAmount;
	public $passback_params;
	public $method;
	public $product_code;
	public $version='1.0';
	public $status = 0;

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
		$this->appId = trim($_G['setting']['pay_alipay_appid']);
		$this->seller_id = trim($_G['setting']['pay_alipay_seller_id']);
		$this->alipayPublicKey = trim($_G['setting']['pay_alipay_publickey']);
		$this->merchantPrivateKey = trim($_G['setting']['pay_alipay_merchantprivatekey']);
    }

	public function getGatewayServerUrl()
    {
        return $this->protocol . '://' . $this->gatewayHost . '/gateway.do';
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function setNotifyUrl(string $notifyUrl='')
    {
		$this->notifyUrl = $notifyUrl;
    }

    public function setTotalAmount($totalAmount)
    {
		$totalAmount = ceil(floatval($totalAmount)*100)/100;
		if($totalAmount>0){
			$this->totalAmount = $totalAmount;
		}
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setPassbackParams(string $passback_params='forum_order'){
		$this->passback_params = urlencode(trim($passback_params));
	}

}