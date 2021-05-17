<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_payment_pay.php 36342 2021-05-17 15:27:15Z dplugin $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$order_id = intval($_GET['order_id']);
if (!$order_id) {
	showmessage('payment_order_no_exist', '', array(), array('showdialog' => true));
}
$order = C::t('common_payment_order')->fetch($order_id);
if (!$order || $order['expire_time'] < time() || $_G['uid'] != $order['uid']) {
	showmessage('payment_order_no_exist', '', array(), array('showdialog' => true));
}
if ($order['status']) {
	$return_url = $order['return_url'];
	if (!$return_url) {
		$return_url = $_G['siteurl'] . 'home.php?mod=spacecp&ac=payment';
	}
	showmessage('payment_succeed', $return_url, array(), array('alert' => 'right'));
}

if (submitcheck('paysubmit')) {
	$pay_channel = daddslashes($_GET['pay_channel']);
	require_once libfile('function/payment');
	$payclass = payment_newinstance($pay_channel);
	if (!$payclass) {
		showmessage('payment_type_no_exist', $_G['siteurl'] . 'home.php?mod=spacecp&ac=payment&op=pay&order_id=' . $order_id, array(), array('showdialog' => true, 'locationtime' => 3));
	}

	$result = $payclass->pay($order);
	if ($result['code'] != 200) {
		showmessage($result['message'], $_G['siteurl'] . 'home.php?mod=spacecp&ac=payment&op=pay&order_id=' . $order_id, array(), array('showdialog' => true, 'locationtime' => 3));
	}
	$pay_url = $result['url'];

	include template('home/spacecp_payment_redirect');
} else if ($_GET['sop'] == 'wxjsapi') {
	$code = daddslashes($_GET['code']);
	$state = daddslashes($_GET['state']);
	if (!$code || !$state || !$order_id) {
		exit('Access Denied');
	}

	require_once libfile('function/payment');
	$payment = payment_newinstance('weixin');
	$result = $payment->weixin_access_token_by_code($code);
	$result = json_decode($result, true);
	if (!$result['openid']) {
		if (strtoupper($_G['charset']) != 'UTF-8') {
			$result['errmsg'] = diconv($result['errmsg'], 'UTF-8', $_G['charset']);
		}
		showmessage($result['errmsg'], $order['referer_url'], array(), array('showdialog' => true, 'locationtime' => 3));
	}

	$result = $payment->pay_jsapi($order, $result['openid']);
	if ($result['code'] != 200) {
		showmessage($result['message'], $order['referer_url'], array(), array('showdialog' => true, 'locationtime' => 3));
	}

	$jsapidata = $payment->weixin_jsapidata($result['url']);
	$title = dhtmlspecialchars($order['subject']);
	include template('home/spacecp_payment_wxjsapi');
} else if ($_GET['sop'] == 'status') {
	exit();
} else {
	$order['subject'] = dhtmlspecialchars($order['subject']);
	$order['description'] = dhtmlspecialchars($order['description']);
	if ($order['amount_fee']) {
		$order['total_amount'] = number_format((intval($order['amount']) + intval($order['amount_fee'])) / 100, '2', '.', ',');
	}
	$order['amount'] = number_format($order['amount'] / 100, '2', '.', ',');

	$payment_settings = C::t('common_setting')->fetch_all_setting(array('ec_weixin', 'ec_alipay'), true);

	require_once libfile('function/payment');
	$pay_channel_list = payment_channels();

	include template('home/spacecp_payment_pay');
}

?>