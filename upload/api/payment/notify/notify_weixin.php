<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify_weixin.php 36342 2021-05-17 14:15:04Z dplugin $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');
define('DISABLEXSSCHECK', true);

require '../../../source/class/class_core.php';
require '../payment_weixin.php';

$discuz = C::app();
$discuz->init();

$payment = new payment_weixin();
if ($_SERVER['HTTP_WECHATPAY_SIGNATURE']) {
	$data = $payment->v3_weixin_sign_verify();
	if ($data) {
		$data = json_decode($data, true);
	}
	if ($data && $data['trade_state'] == 'SUCCESS') {
		$out_biz_no = $data['out_trade_no'];
		$payment_time = strtotime($data['success_time']);
		require_once libfile('function/payment');
		$is_success = payment_finish_order('weixin', $out_biz_no, $data['transaction_id'], $payment_time);
		if ($is_success) {
			exit('{"code":"SUCCESS","message":"ok"}');
		} else {
			writelog('errorpayment', '[ERROR] weixin-notify out_trade_no: ' . $out_biz_no . ', data: ' . json_encode($data));
		}
	}
	exit('{"code":"fail","message":"fail"}');
} else {
	$data = $payment->weixin_sign_verify();
	if ($data) {
		$out_biz_no = $data['out_trade_no'];
		$payment_time = strtotime(preg_replace('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', '$1-$2-$3 $4:$5:$6', $data['time_end']));
		require_once libfile('function/payment');
		$is_success = payment_finish_order('weixin', $out_biz_no, $data['transaction_id'], $payment_time);
		if ($is_success) {
			echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
			exit();
		} else {
			writelog('errorpayment', '[ERROR] weixin-notify out_trade_no: ' . $out_biz_no . ', data: ' . json_encode($data));
		}
	}
	echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
	exit();
}

?>