<?php

// 必须使用此判断避免外部调用
if (! defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$_hdx['quit_rob_times'] = intval($_hdx['quit_rob_times']);
$_hdx['quit_join_times'] = intval($_hdx['quit_join_times']);
$_hdx['quit_rate'] = floatval($_hdx['quit_rate']);

$quitFee = ceil($_player['rob_times'] * $_hdx['quit_rate']);

?>
