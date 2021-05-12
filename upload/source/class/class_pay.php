<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_pay.php 30871 2021-05-01 09:32:37Z Aphly $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Class pay
{
	public $apitype;

	public function __construct(string $apitype=''){
		$this->apitype = $apitype;
	}

	public function getData(){
		$headers = getallheaders();
		if($headers){
			$notify['nonce'] = isset($headers['Wechatpay-Nonce'])?$headers['Wechatpay-Nonce']:'';
			$notify['sign'] = isset($headers['Wechatpay-Signature'])?$headers['Wechatpay-Signature']:'';
			$notify['timestamp'] = isset($headers['Wechatpay-Timestamp'])?$headers['Wechatpay-Timestamp']:'';
			$notify['serialNo'] = isset($headers['Wechatpay-Serial'])?$headers['Wechatpay-Serial']:'';
			$notify['body'] = file_get_contents('php://input');
			if($notify['nonce'] && $notify['sign'] && $notify['timestamp'] && $notify['serialNo'] && $notify['body']){
				$this->apitype='wechat';
				return $notify;
			}else{
				$notify = $_POST;
				if($notify['passback_params']){
					$this->apitype='alipay';
					return $notify;
				}
			}
		}
		exit('Access Denied');
	}

	public function checkPlugin(string $str = ''){
		$flag = false;
		if ($str && preg_match("/^[a-zA-Z0-9_]+$/", $str)){
			$flag = true;
		}
		return  $flag;				
	}

	public function success(){
		if($this->apitype=='alipay'){
			exit('success');
		}else{
			exit('{"code":"SUCCESS","message":"ok"}');
		}
	}

	public function fail(string $msg='fail'){
		if($this->apitype=='alipay'){
			exit('fail');
		}else{
			exit('{"code":"fail","message":"'.$msg.'"}');
		}
	}

/*
	$data=array(
		'title'=>'sadad',
		'plugin_id'=>'forum_order',
		'out_trade_no'=>'asdasdad',
		'amount'=>'0.01',
		//'notify_url'=>'asdsad',
		'return_url'=>'asdazzz',
		'openid'=>'asdazzz'
	);
*/
	public function order(array $data=array()){
		global $_G;
		if(!$this->apitype){
			exit('apitype_error');
		}
		if($this->apitype=='alipay'){
			$orderid = dgmdate(TIMESTAMP, 'YmdHis').random(18);
			$pay_ali = new pay_ali;
			if(!$pay_ali->status()){
				exit('setting_error');
			}
			$pay_ali->setReturnUrl($data['return_url']);
			$pay_ali->setTotalAmount($data['amount']);
			$pay_ali->setOutTradeNo($data['out_trade_no']);
			$pay_ali->setPassbackParams($data['plugin_id']);
			$pay_ali->setSubject($data['title']);
			if(!empty($data['notify_url'])){
				$pay_ali->setNotifyUrl($data['notify_url']);
			}
			return $pay_ali->pay();
		}else if($this->apitype=='wechat'){
			$wxPay = new pay_wechat();
			if(!$wxPay->status()){
				exit('setting_error');
			}
			$wxPay->setTotalAmount($data['amount']);
			$wxPay->setOutTradeNo($data['out_trade_no']);
			$wxPay->setDescription($data['title']);
			$wxPay->setAttach($data['plugin_id']);
			if(!empty($data['notify_url'])){
				$wxPay->setNotifyUrl($data['notify_url']);
			}
			if($_G['mobile']){
				if(!empty($data['openid'])){
					$res = $wxPay->jsapi($data['openid']);
					$res['type'] ='jsapi';
					$res['data'] = $data;
					return $res;
				}else{
					$res = $wxPay->h5();
					$res['type'] ='h5';
					$res['data'] = $data;
					$res['h5_url']=$res['h5_url'].'&redirect_url='.urlencode($data['return_url']);
					return $res;
				}
			}else{
				$res = $wxPay->native();
				$res['type'] ='native';
				$res['data'] = $data;
				return $res;
			}
		}
		return;
	}

	
	public function query(string $out_trade_no){
		if(!$this->apitype){
			exit('apitype_error');
		}
		if($this->apitype=='alipay'){
			$pay_ali = new pay_ali;
			return $pay_ali->query($out_trade_no);
		}else if($this->apitype=='wechat'){
			$wxPay = new pay_wechat();
			return $wxPay->query($out_trade_no);
		}
		return;
	}

	public function refund(string $out_trade_no,float $refund_amount){
		if(!$this->apitype){
			exit('apitype_error');
		}
		if($this->apitype=='alipay'){
			$alipay = new pay_ali();
			$result = $alipay->refund($out_trade_no,$refund_amount);
			if($result['code'] && $result['code']=='10000'){
				return true;
			}else{
				return false;
			}
		}else if($this->apitype=='wechat'){
			$outRefundNo= 'refund_'.dgmdate(TIMESTAMP, 'YmdHis').random(10);
			$wxPay = new pay_wechat();
			$wxPay->setTotalAmount($refund_amount);
			$wxPay->setOutTradeNo($out_trade_no);
			$wxPay->setOutRefundNo($outRefundNo);
			$result = $wxPay->refund();
			if($result['status']=='PROCESSING' || $result['status']=='SUCCESS'){
				return true;
			}else{
				return false;
			}
		}
		return;
	}
}