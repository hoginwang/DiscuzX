<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_pay.php 30969 2021-04-29 10:18:10Z aphly $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if(!defined('APPTYPEID')) {
	define('APPTYPEID', 2);
}
$checktype = $_GET['checktype'];
cpheader();

if($operation == 'alipay') {
	$settings = C::t('common_setting')->fetch_all_setting(array('pay_alipay_status', 'pay_alipay_appid', 'pay_alipay_seller_id',  'pay_alipay_publickey',  'pay_alipay_merchantprivatekey'));

	if(!submitcheck('alipaysubmit')) {
		shownav('extended', 'nav_pay');
		showsubmenu('nav_pay', array(
			array('nav_pay_config', 'setting&operation=pay', 0),
			array('nav_pay_alipay', 'pay&operation=alipay', 1),
			array('nav_pay_wechat', 'pay&operation=wechat', 0),
			array('nav_pay_orders', 'pay&operation=orders', 0)
		));

		showtips('pay_alipay_tips');
		showformheader('pay&operation=alipay');

		showtableheader('','nobottom');
		showtitle('pay_alipay_set');
		showsetting('pay_alipay_status', 'settingsnew[pay_alipay_status]', $settings['pay_alipay_status'], 'radio');
		showsetting('pay_alipay_appid', 'settingsnew[pay_alipay_appid]', $settings['pay_alipay_appid'], 'text');
		showsetting('pay_alipay_seller_id', 'settingsnew[pay_alipay_seller_id]', $settings['pay_alipay_seller_id'], 'text');
		$pay_alipay_publickey = $settings['pay_alipay_publickey'] ? $settings['pay_alipay_publickey'][0].'********'.substr($settings['pay_alipay_publickey'], -128) : '';
		showsetting('pay_alipay_publickey', 'settingsnew[pay_alipay_publickey]', $pay_alipay_publickey, 'textarea');
		$pay_alipay_merchantprivatekey = $settings['pay_alipay_merchantprivatekey'] ? $settings['pay_alipay_merchantprivatekey'][0].'********'.substr($settings['pay_alipay_merchantprivatekey'], -128) : '';
		showsetting('pay_alipay_merchantprivatekey', 'settingsnew[pay_alipay_merchantprivatekey]', $pay_alipay_merchantprivatekey, 'textarea');
		showtablefooter();
		
		showtableheader('', 'notop');
		showsubmit('alipaysubmit');
		showtablefooter();
		showformfooter();
	} else {
		$settingsnew = $_GET['settingsnew'];
		$settingsnew['pay_alipay_status'] = trim($settingsnew['pay_alipay_status']);
		$settingsnew['pay_alipay_appid'] = trim($settingsnew['pay_alipay_appid']);
		$settingsnew['pay_alipay_seller_id'] = trim($settingsnew['pay_alipay_seller_id']);
		$pay_alipay_publickey = $settings['pay_alipay_publickey'] ? $settings['pay_alipay_publickey'][0].'********'.substr($settings['pay_alipay_publickey'], -128) : '';
		$settingsnew['pay_alipay_publickey'] = $pay_alipay_publickey == $settingsnew['pay_alipay_publickey'] ? $settings['pay_alipay_publickey'] : $settingsnew['pay_alipay_publickey'];
		$settingsnew['pay_alipay_publickey'] = trim($settingsnew['pay_alipay_publickey']);
		$pay_alipay_merchantprivatekey = $settings['pay_alipay_merchantprivatekey'] ? $settings['pay_alipay_merchantprivatekey'][0].'********'.substr($settings['pay_alipay_merchantprivatekey'], -128) : '';
		$settingsnew['pay_alipay_merchantprivatekey'] = $pay_alipay_merchantprivatekey == $settingsnew['pay_alipay_merchantprivatekey'] ? $settings['pay_alipay_merchantprivatekey'] : $settingsnew['pay_alipay_merchantprivatekey'];
		$settingsnew['pay_alipay_merchantprivatekey'] = trim($settingsnew['pay_alipay_merchantprivatekey']);

		$data = array('pay_alipay_status' => $settingsnew['pay_alipay_status'],
			'pay_alipay_appid' => $settingsnew['pay_alipay_appid'],
			'pay_alipay_seller_id' => $settingsnew['pay_alipay_seller_id'],
			'pay_alipay_publickey' => $settingsnew['pay_alipay_publickey'],
			'pay_alipay_merchantprivatekey' => $settingsnew['pay_alipay_merchantprivatekey']);
		C::t('common_setting')->update_batch($data);
		updatecache('setting');
		cpmsg('pay_alipay_succeed', 'action=pay&operation=alipay', 'succeed');

	}

} elseif($operation == 'wechat') {
	if($_GET['cert']=='update'){
		$pay_wechat = new pay_wechat;
		$pay_wechat->getCertificates();
		cpmsg('pay_wechat_succeed', 'action=pay&operation=wechat', 'succeed');
	}
	$settings = C::t('common_setting')->fetch_all_setting(array('pay_wechat_status','pay_wechat_appid', 'pay_wechat_mchid', 'pay_wechat_key',  'pay_wechat_appsecret','pay_wechat_certNo','pay_wechat_privateKey','pay_wechat_cert'));
	
	if(!submitcheck('wechatsubmit')) {
		shownav('extended', 'nav_pay');
		showsubmenu('nav_pay', array(
			array('nav_pay_config', 'setting&operation=pay', 0),
			array('nav_pay_alipay', 'pay&operation=alipay', 0),
			array('nav_pay_wechat', 'pay&operation=wechat', 1),
			array('nav_pay_orders', 'pay&operation=orders', 0)
		));

		showtips('pay_wechat_tips');
		showformheader('pay&operation=wechat');
		showtableheader('','nobottom');
		showtitle('pay_wechat_set');
		showsetting('pay_wechat_status', 'settingsnew[pay_wechat_status]',$settings['pay_wechat_status'], 'radio');
		showsetting('pay_wechat_appid', 'settingsnew[pay_wechat_appid]', $settings['pay_wechat_appid'], 'text');
		$pay_wechat_appsecret = $settings['pay_wechat_appsecret'] ? $settings['pay_wechat_appsecret'][0].'********'.substr($settings['pay_wechat_appsecret'], -4) : '';
		showsetting('pay_wechat_appsecret', 'settingsnew[pay_wechat_appsecret]', $pay_wechat_appsecret, 'text');
		showsetting('pay_wechat_mchid', 'settingsnew[pay_wechat_mchid]', $settings['pay_wechat_mchid'], 'text');
		$pay_wechat_key = $settings['pay_wechat_key'] ? $settings['pay_wechat_key'][0].'********'.substr($settings['pay_wechat_key'], -4) : '';
		showsetting('pay_wechat_key', 'settingsnew[pay_wechat_key]', $pay_wechat_key, 'text');
		showsetting('pay_wechat_certNo', 'settingsnew[pay_wechat_certNo]', $settings['pay_wechat_certNo'], 'text');
		$pay_wechat_privateKey = $settings['pay_wechat_privateKey'] ? $settings['pay_wechat_privateKey'][0].'********'.substr($settings['pay_wechat_privateKey'], -128) : '';
		showsetting('pay_wechat_privateKey', 'settingsnew[pay_wechat_privateKey]', $pay_wechat_privateKey, 'textarea');
		showsetting('pay_wechat_cert', 'settingsnew[pay_wechat_cert]', $settings['pay_wechat_cert'], 'text');

		showtablefooter();
		showtableheader('', 'notop');
		showsubmit('wechatsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$settingsnew = $_GET['settingsnew'];
		$settingsnew['pay_wechat_status'] = trim($settingsnew['pay_wechat_status']);
		$settingsnew['pay_wechat_appid'] = trim($settingsnew['pay_wechat_appid']);
		$settingsnew['pay_wechat_mchid'] = trim($settingsnew['pay_wechat_mchid']);
		$pay_wechat_key = $settings['pay_wechat_key'] ? $settings['pay_wechat_key'][0].'********'.substr($settings['pay_wechat_key'], -4) : '';
		$settingsnew['pay_wechat_key'] = $pay_wechat_key == $settingsnew['pay_wechat_key'] ? $settings['pay_wechat_key'] : $settingsnew['pay_wechat_key'];
		$pay_wechat_appsecret = $settings['pay_wechat_appsecret'] ? $settings['pay_wechat_appsecret'][0].'********'.substr($settings['pay_wechat_appsecret'], -4) : '';
		$settingsnew['pay_wechat_appsecret'] = $pay_wechat_appsecret == $settingsnew['pay_wechat_appsecret'] ? $settings['pay_wechat_appsecret'] : $settingsnew['pay_wechat_appsecret'];
		$settingsnew['pay_wechat_certNo'] = trim($settingsnew['pay_wechat_certNo']);
		$pay_wechat_privateKey = $settings['pay_wechat_privateKey'] ? $settings['pay_wechat_privateKey'][0].'********'.substr($settings['pay_wechat_privateKey'], -128) : '';
		$settingsnew['pay_wechat_privateKey'] = $pay_wechat_privateKey == $settingsnew['pay_wechat_privateKey'] ? $settings['pay_wechat_privateKey'] : $settingsnew['pay_wechat_privateKey'];
		$settingsnew['pay_wechat_privateKey'] = trim($settingsnew['pay_wechat_privateKey']);
		$settingsnew['pay_wechat_cert'] = trim($settingsnew['pay_wechat_cert']);
		
		$data = array('pay_wechat_status' => $settingsnew['pay_wechat_status'],
			'pay_wechat_appid' => $settingsnew['pay_wechat_appid'],
			'pay_wechat_mchid' => $settingsnew['pay_wechat_mchid'],
			'pay_wechat_key' => $settingsnew['pay_wechat_key'],
			'pay_wechat_appsecret' => $settingsnew['pay_wechat_appsecret'],
			'pay_wechat_certNo' => $settingsnew['pay_wechat_certNo'],
			'pay_wechat_privateKey' => $settingsnew['pay_wechat_privateKey'],
			'pay_wechat_cert' => $settingsnew['pay_wechat_cert']);
		C::t('common_setting')->update_batch($data);
		updatecache('setting');
		cpmsg('pay_wechat_succeed', 'action=pay&operation=wechat', 'succeed');
	}

} elseif($operation == 'orders') {
	
	if(!$_G['setting']['creditstrans'] || !$_G['setting']['pay_ratio']) {
		cpmsg('pay_orders_disabled', '', 'error');
	}

	if($_GET['order_op']=='refund' && FORMHASH == $_GET['formhash'] && $_GET['orderid']) {
		$order = C::t('home_credit_order')->fetch($_GET['orderid']);
		if($order['status'] == 2 || $order['status'] == 3){
			$usercount = C::t('common_member_count')->fetch($order['uid']);
			if($order['amount']>$usercount['extcredits'.$_G['setting']['creditstrans']]){
				cpmsg('pay_orders_fail_refund', "action=pay&operation=orders&order_op=view&orderid={$_GET['orderid']}", 'error');
			}
			$pay = new pay($order['type']);
			$refund_res = $pay->refund($order['orderid'],$order['price']);
			if($refund_res){
				updatemembercount($order['uid'], array($_G['setting']['creditstrans'] => -$order['amount']), 1, '', $order['uid'],'',$lang['pay_orders_refund'],$lang['pay_orders_credit_refund']);
				C::t('home_credit_order')->update($order['orderid'], array('status' => '4', 'admin' => $_G['username'], 'confirmdate' => $_G['timestamp']));
				cpmsg('pay_orders_refund_succeed', "action=pay&operation=orders&order_op=view&orderid={$_GET['orderid']}", 'succeed');
			}else{
				cpmsg('pay_orders_refund_fail', "action=pay&operation=orders&order_op=view&orderid={$_GET['orderid']}", 'error');
			}
		}
	}

	if($_GET['order_op']=='view' && $_GET['orderid']) {
		$order = C::t('home_credit_order')->fetch($_GET['orderid']);
		if($order){
			shownav('extended', 'nav_pay');
			showsubmenu('nav_pay', array(
				array('nav_pay_config', 'setting&operation=pay', 0),
				array('nav_pay_alipay', 'pay&operation=alipay', 0),
				array('nav_pay_wechat', 'pay&operation=wechat', 0),
				array('nav_pay_orders', 'pay&operation=orders', 1),
			));
			showtableheader($lang['pay_orders_id'].' : '.$order['orderid'], 'nobottom');
			$order['submitdate'] = dgmdate($order['submitdate']);
			$order['confirmdate'] = $order['confirmdate'] ? dgmdate($order['confirmdate']) : 'N/A';
			switch($order['status']) {
				case 1: $order['orderstatus'] = $lang['pay_orders_search_status_pending']; break;
				case 2: $order['orderstatus'] = '<b>'.$lang['pay_orders_search_status_auto_finished'].'</b>'; break;
				case 3: $order['orderstatus'] = '<b>'.$lang['pay_orders_search_status_manual_finished'].'</b>'; break;
				case 4: $order['orderstatus'] = '<b>'.$lang['pay_orders_search_status_refund'].'</b>'; break;
			}
			$order['user'] = getuserbyuid($order['uid']);
			$order['type'] = $order['type']=='alipay' ? 'pay_alipay' : 'pay_wechat';
			showtablerow('', '', array($lang['pay_orders_id'],$order['orderid']));
			showtablerow('', '', array($lang['pay_orders_status1'],$order['orderstatus']));
			showtablerow('', '', array($lang['pay_orders_type'],$lang[$order['type']]));
			showtablerow('', '', array($lang['pay_orders_buyer'],$order['buyer']));
			showtablerow('', '', array($lang['pay_orders_admin'],$order['admin']));
			showtablerow('', '', array($lang['pay_orders_uid'],$order['user']['username'].'[uid:'.$order['uid'].']'));
			showtablerow('', '', array($lang['pay_orders_amount'],$order['amount']));
			showtablerow('', '', array($lang['pay_orders_price'],$order['price']));
			showtablerow('', '', array($lang['pay_orders_submitdate'],$order['submitdate']));
			showtablerow('', '', array($lang['pay_orders_confirmdate'],$order['confirmdate']));
			showtablerow('', '', array($lang['pay_orders_trade_no'],$order['trade_no']));

			if($order['status']==2){
				showtablerow('', '', array('','<a href="'.ADMINSCRIPT.'?action=pay&operation=orders&formhash='.FORMHASH.'&order_op=refund&orderid='.$order['orderid'].'">'.$lang['pay_orders_refund'].'</a>'));
			}
			showtablefooter();
		}
	}else{
		if(!submitcheck('ordersubmit')) {
			echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
			shownav('extended', 'nav_pay');
			showsubmenu('nav_pay', array(
				array('nav_pay_config', 'setting&operation=pay', 0),
				array('nav_pay_alipay', 'pay&operation=alipay', 0),
				array('nav_pay_wechat', 'pay&operation=wechat', 0),
				array('nav_pay_orders', 'pay&operation=orders', 1),
			));
			showtips('pay_orders_tips');
			showtagheader('div', 'ordersearch', !submitcheck('searchsubmit', 1));
			showformheader('pay&operation=orders');
			showtableheader('pay_orders_search');
			showsetting('pay_orders_search_status', array('orderstatus', array(
				array('', $lang['pay_orders_search_status_all']),
				array(1, $lang['pay_orders_search_status_pending']),
				array(2, $lang['pay_orders_search_status_auto_finished']),
				array(3, $lang['pay_orders_search_status_manual_finished']),
				array(4, $lang['pay_orders_search_status_refund'])
			)), intval($orderstatus), 'select');
			showsetting('pay_orders_search_id', 'orderid', $orderid, 'text');
			showsetting('pay_orders_search_users', 'users', $users, 'text');
			showsetting('pay_orders_search_buyer', 'buyer', $buyer, 'text');
			showsetting('pay_orders_search_admin', 'admin', $admin, 'text');
			showsetting('pay_orders_search_submit_date', array('sstarttime', 'sendtime'), array($sstarttime, $sendtime), 'daterange');
			showsetting('pay_orders_search_confirm_date', array('cstarttime', 'cendtime'), array($cstarttime, $cendtime), 'daterange');
			showsubmit('searchsubmit');
			showtablefooter();
			showformfooter();
			showtagfooter('div');

			if(submitcheck('searchsubmit', 1)) {
				$start_limit = ($page - 1) * $_G['tpp'];
				$ordercount = C::t('home_credit_order')->count_by_search(null, $_GET['orderstatus'], $_GET['orderid'], null, ($_GET['users'] ? explode(',', str_replace(' ', '', $_GET['users'])) : null), $_GET['buyer'], $_GET['admin'], strtotime($_GET['sstarttime']), strtotime($_GET['sendtime']), strtotime($_GET['cstarttime']), strtotime($_GET['cendtime']));
				$multipage = multi($ordercount, $_G['tpp'], $page, ADMINSCRIPT."?action=pay&operation=orders&searchsubmit=yes&orderstatus={$_GET['orderstatus']}&orderid={$_GET['orderid']}&users={$_GET['users']}&buyer={$_GET['buyer']}&admin={$_GET['admin']}&sstarttime={$_GET['sstarttime']}&sendtime={$_GET['sendtime']}&cstarttime={$_GET['cstarttime']}&cendtime={$_GET['cendtime']}");

				showtagheader('div', 'orderlist', TRUE);
				showformheader('pay&operation=orders');
				showtableheader('result');
				showsubtitle(array('','pay_orders_id','pay_type', 'pay_orders_status', 'pay_orders_buyer', 'pay_orders_amount', 'pay_orders_price', 'pay_orders_submitdate', 'pay_orders_confirmdate', 'pay_orders_op'));

				foreach(C::t('home_credit_order')->fetch_all_by_search(null, $_GET['orderstatus'], $_GET['orderid'], null, ($_GET['users'] ? explode(',', str_replace(' ', '', $_GET['users'])) : null), $_GET['buyer'], $_GET['admin'], strtotime($_GET['sstarttime']), strtotime($_GET['sendtime']), strtotime($_GET['cstarttime']), strtotime($_GET['cendtime']), $start_limit, $_G['tpp']) as $order) {
					switch($order['status']) {
						case 1: $order['orderstatus'] = $lang['pay_orders_search_status_pending']; break;
						case 2: $order['orderstatus'] = '<b>'.$lang['pay_orders_search_status_auto_finished'].'</b>'; break;
						case 3: $order['orderstatus'] = '<b>'.$lang['pay_orders_search_status_manual_finished'].'</b><br />(<a href="home.php?mod=space&username='.rawurlencode($order['admin']).'" target="_blank">'.$order['admin'].'</a>)'; break;
						case 4: $order['orderstatus'] = '<b>'.$lang['pay_orders_search_status_refund'].'</b>'; break;
					}
					$order['submitdate'] = dgmdate($order['submitdate']);
					$order['confirmdate'] = $order['confirmdate'] ? dgmdate($order['confirmdate']) : 'N/A';

					$apitype = $order['type'];
					$apitype = $apitype=='alipay' ? 'pay_alipay' : 'pay_wechat';
					showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"validate[]\" value=\"{$order['orderid']}\" ".($order['status'] != 1 ? 'disabled' : '').">",
						"{$order['orderid']}",
						$lang[$apitype],
						$order['orderstatus'],
						"<a href=\"home.php?mod=space&uid={$order['uid']}\" target=\"_blank\">{$order['username']}</a>",
						"{$_G[setting][extcredits][$_G[setting][creditstrans]]['title']} $order[amount] {$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit']}",
						"{$lang['rmb']} {$order['price']} {$lang['rmb_yuan']} ",
						$order['submitdate'],
						$order['confirmdate'],
						"<a href=\"".ADMINSCRIPT."?action=pay&operation=orders&order_op=view&orderid={$order['orderid']}\">{$lang['pay_orders_view']}</a>"
					));
				}

				showsubmit('ordersubmit', 'submit', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'validate\')" /><label for="chkall">'.cplang('pay_orders_validate').'</label>', '<a href="#" onclick="$(\'orderlist\').style.display=\'none\';$(\'ordersearch\').style.display=\'\';">'.cplang('research').'</a>', $multipage);
				showtablefooter();
				showformfooter();
				showtagfooter('div');
			}

		} else {
			$numvalidate = 0;
			if($_GET['validate']) {
				$orderids = array();
				$confirmdate = dgmdate(TIMESTAMP);
				foreach(C::t('home_credit_order')->fetch_all_order($_GET['validate'], '1') as $order) {
					updatemembercount($order['uid'], array($_G['setting']['creditstrans'] => $order['amount']));
					$orderids[] = $order['orderid'];

					$submitdate = dgmdate($order['submitdate']);
					notification_add($order['uid'], 'system', 'addfunds', array(
						'orderid' => $order['orderid'],
						'price' => $order['price'],
						'from_id' => 0,
						'from_idtype' => 'buycredit',
						'value' => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'].' '.$order['amount'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit']
					), 1);
				}
				if($orderids) {
					C::t('home_credit_order')->update($orderids, array('status' => '3', 'admin' => $_G['username'], 'confirmdate' => $_G['timestamp']));
				}
			}

			cpmsg('orders_validate_succeed', "action=pay&operation=orders&searchsubmit=yes&orderstatus={$_GET['orderstatus']}&orderid={$_GET['orderid']}&users={$_GET['users']}&buyer={$_GET['buyer']}&admin={$_GET['admin']}&sstarttime={$_GET['sstarttime']}&sendtime={$_GET['sendtime']}&cstarttime={$_GET['cstarttime']}&cendtime={$_GET['cendtime']}", 'succeed');

		}
	}
}

?>