<?php

// 必须使用此判断避免外部调用
if (! defined('IN_DISCUZ')) {
	exit('Access Denied');
}


if (!submitcheck('settingsubmit')) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}


// INIT
$error = false;
$url = 'plugin.php?id=zeroze007_hdx&op=setting';

// SETTING
$_hdx['quit_rob_times'] = intval($_hdx['quit_rob_times']);
$_hdx['quit_join_times'] = intval($_hdx['quit_join_times']);
$_hdx['quit_rate'] = floatval($_hdx['quit_rate']);


$msg = lang('plugin/zeroze007_hdx', 'setup_successfully');

// 输出
showMsg($msg, true,array(
	'url' => $url
));

?>
