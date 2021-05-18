<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: pay_wechatconfig.php 30871 2021-05-01 09:32:37Z Aphly $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class pay_wechatconfig
{
	protected $gateWay='https://api.mch.weixin.qq.com/v3';
	protected $appid;
	protected $mchid;
	protected $key;
	protected $appsecret ;
	protected $auth_type ='WECHATPAY2-SHA256-RSA2048';
	protected $apiKey3; 
	protected $certificateSerialNumber; 
	protected $privateKeyPath;
	protected $publicKeys=array();
	protected $privateKey;
	protected $cert; 
	
	public $status = 0;
	public $description;
	public $out_trade_no;
	public $attach;
	public $notifyUrl;
	public $amount;
	public $transaction_id='';
	public $out_refund_no;
	public $refund=0;
	public $refund_amount;
	
	public function __construct(){
		global $_G;
		$this->notifyUrl = $_G['siteurl'].'api/pay/notify.php';
		$this->appid = $_G['setting']['pay_wechat_appid'];
		$this->mchid = $_G['setting']['pay_wechat_mchid'];
		$this->status = intval($_G['setting']['pay_wechat_status']);
		//$this->privateKeyPath = 'api/pay/apiclient_key.pem';
		$this->privateKey = $_G['setting']['pay_wechat_privateKey'];
		$this->certificateSerialNumber=$_G['setting']['pay_wechat_certNo'];
		$this->apiKey3 =$_G['setting']['pay_wechat_key'];
		$this->appsecret = $_G['setting']['pay_wechat_appsecret'];
		$this->cert = $_G['setting']['pay_wechat_cert'];
	}

	public function setOutTradeNo($out_trade_no)
	{
		$this->out_trade_no = $out_trade_no;
	}
	
	public function setOutRefundNo($out_refund_no)
	{
		$this->out_refund_no = $out_refund_no;
	}
	
	public function setTransactionId($transaction_id)
	{
		$this->transaction_id = $transaction_id;
	}

	public function setRefund($refund)
	{
		$this->refund = $refund;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function setAttach($attach)
	{
		$this->attach = $attach;
	}

	public function setNotifyUrl($notifyUrl='')
	{
		$this->notifyUrl = $notifyUrl;
	}
	
	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	public function price($num)
	{
		$num = ceil(floatval($num)*100);
		return $num>0?$num:0;
	}

	public function setTotalAmount($totalAmount)
	{
		return $this->amount = $this->price($totalAmount);
	}

	public function setRefundAmount($refund_amount)
	{
		return $this->refund_amount = $this->price($refund_amount);
	}
}