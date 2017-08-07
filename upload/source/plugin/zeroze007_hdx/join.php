<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}



if (!submitcheck('joinsubmit', 1)) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}


$playerActivity = DB::fetch_first("SELECT * FROM " . DB::table('hdx_player_activity') . " WHERE uid = '" . $_uid . "'");

if ($playerActivity && $playerActivity['type'] == ACTIVITY_AWAY && $playerActivity['expired_time'] > $_timenow) {
    
    throw new Exception(lang('plugin/zeroze007_hdx', 'you_alreay_away_to_join', array('hd' => $_hdxLang['hd'],
                'year' => date('Y', $playerActivity['expired_time']),
        'month' => date('n', $playerActivity['expired_time']),
        'day' => date('j', $playerActivity['expired_time']),
        'hour' => date('H', $playerActivity['expired_time']),
        'minute' => date('i', $playerActivity['expired_time']))
                    
            )
    );
}

// 是否已经加入黑道
if ($_player['uid'] > 0) {
	showError(lang('plugin/zeroze007_hdx', 'already_in', array('hd' => $_hdxLang['hd'])), array('url'=>'plugin.php?id=zeroze007_hdx'));
}


$initSta = intval($_setting['init_sta']);

// player
$data = array(
	'uid' => $_uid,
	'join_time' => $_timenow,
    'level' => 0,
    'exp' => 0,
    'title' => getPlayerTitle(0),
	'available' => 1
);

DB::insert('hdx_player', $data);

DB::query("INSERT INTO " . DB::table('hdx_player_setting') . "(uid, skey, svalue) VALUES (" . $_uid . ",'robbed_alert', '1')");

// ACTIVITY
$data = array(
	'uid' => $_uid,
	'type' => ACTIVITY_JOIN,
	'created_at' => $_timenow
);

DB::insert('hdx_player_activity', $data);

// member
$data = array(
	'sta' => intval($initSta),
);

DB::update('hdx_player', $data, 'uid=' . $_uid);



// log
$log['who_uid'] = $_uid;
$log['to_uid'] = 0;
$log['created_at'] = $_timenow;
$log['msg'] = '<font color=green>{{' . $_G['username'] . '}}'. lang('plugin/zeroze007_hdx', 'success_in_hd', array(
			'hd' => $_hdxLang['hd'])).'</font>';

DB::insert('hdx_log', $log);

$showMsgParam = array('url' => 'plugin.php?id=zeroze007_hdx');
showMsg(lang('plugin/zeroze007_hdx', 'success_in_hd_and_start', array(
			'hd' => $_hdxLang['hd'],
			'hdx' => $_hdxLang['hdx'])),1,$showMsgParam);
?>
