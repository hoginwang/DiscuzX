<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify_order.php 34251 2021-05-05 03:10:11Z aphly $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(preg_match('/^sys_[a-zA-Z0-9_]+/',$origin)){
	if($origin=='sys_home_credit_order'){
		DB::begin_transaction();
		$order = C::t('home_credit_order')->fetchForUpdate($notify['out_trade_no']);
		$order = array_merge($order, C::t('common_member')->fetch_by_username($order['uid']));
		if($notify['out_trade_no']==$order['orderid']){
			if($order['status'] == 1) {
				C::t('home_credit_order')->update($order['orderid'], array('status' => '2', 'trade_no' => $trade_no,'buyer' => $buyer, 'confirmdate' => $_G['timestamp']));
				updatemembercount($order['uid'], array($_G['setting']['creditstrans'] => $order['amount']), 1, 'AFD', $order['uid']);
				DB::commit();
				C::t('home_credit_order')->delete_by_submitdate($_G['timestamp']-60*86400);
				notification_add($order['uid'], 'credit', 'addfunds', array(
					'orderid' => $order['orderid'],
					'price' => $order['price'],
					'value' => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'].' '.$order['amount'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit']
				), 1);
				$pay->success();
			}
		}
		DB::rollback();
		$pay->fail();
	}
}else if(preg_match('/^plugin_[a-zA-Z0-9_]+/',$origin)){
	$plugin_id = str_replace("plugin_","",$origin);
	$pNotify = DISCUZ_ROOT.'./source/plugin/'.$plugin_id.'/notify/notify.php';
	if(file_exists($pNotify)){
		require_once $pNotify;
	}else{
		$pay->fail();
	}
}
$pay->fail();