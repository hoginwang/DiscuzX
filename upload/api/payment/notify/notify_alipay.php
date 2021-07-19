<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify_alipay.php 36342 2021-05-17 14:14:54Z dplugin $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');
define('DISABLEXSSCHECK', true);

require '../../../source/class/class_core.php';
require '../payment_alipay.php';

$discuz = C::app();
$discuz->init();

if(!$_POST['sign'] || !$_POST['sign_type']) exit('fail');

$sign = $_POST['sign'];
unset($_POST['sign']);

$payment = new payment_alipay();
$isright = $payment->alipay_sign_verify($sign, $_POST);
if(!$isright){
	writelog('errorpayment', '[ERROR] alipay-notify sign error. sign: ' . $_POST['sign_type'] . ': ' . $sign . 'data: ' . json_encode($_POST));
	$_POST['sign'] = $sign;
	payment::paymentlog('alipay', 0, 0, 0, 50001, $_POST);
	exit('fail');
}

if($_POST['trade_status'] == 'TRADE_SUCCESS'){
	$out_biz_no = $_POST['out_trade_no'];
	$payment_time = strtotime($_POST['gmt_payment']);

	require_once libfile('function/payment');
	$is_success = payment_finish_order('alipay', $out_biz_no, $_POST['trade_no'], $payment_time);
	$is_success = payment::finish_order('alipay', $out_biz_no, $_POST['trade_no'], $payment_time);
	if($is_success){
		exit('success');
	} else{
		writelog('errorpayment', '[ERROR] alipay-notify out_trade_no: ' . $out_biz_no . ', data: ' . json_encode($_POST));
	}
}

exit('fail');

?>