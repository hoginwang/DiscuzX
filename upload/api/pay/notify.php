<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify.php 34251 2021-05-04 03:10:11Z aphly $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');
define('DISABLEXSSCHECK', true);
require '../../source/class/class_core.php';
$discuz = C::app();
$discuz->init();

$pay = new pay();
$notify = $pay->getData();

if($pay->apitype=='alipay'){
	$pay_ali = new pay_ali();
	$result = $pay_ali->notifyverify($notify);
	if($result===true && $notify['trade_status'] == 'TRADE_SUCCESS' && $notify['app_id']==$pay_ali->appId && strtolower($notify['seller_id'])==strtolower($pay_ali->seller_id)){
		$origin = $notify['passback_params'];
		$trade_no = $notify['trade_no'];
		$buyer = $notify['buyer_id'];
		require_once 'notify_order.php';
	}else{
		$pay->fail();
	}
}else if($pay->apitype=='wechat'){
	$pay_wechat = new pay_wechat();
	$result = $pay_wechat->validate($notify);
	if($result===false){
		$pay->fail('sign_error');
	}
	try{
		$res = $pay_wechat->decrypt($notify['body']);
	}catch(Exception $e){
		if($e->getMessage() == 'Error php>=7.1'){
			$pay_wechat->close();
			$pay->fail('pay_error');
		}
	}
	if ($res === false) {
		$pay->fail('pay_error');
	}
	if ($res['trade_state'] == 'SUCCESS') {
		$notify = $res;
		$origin = $notify['attach'];
		$trade_no = $notify['transaction_id'];
		$buyer = $notify['payer']['openid'];
		require_once 'notify_order.php';
	}
}else{
	exit('Access Denied');
}