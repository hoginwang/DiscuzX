<?php
// 必须使用此判断避免外部调用
if (! defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$url = 'plugin.php?id=zeroze007_hdx&op=setting';

if (submitcheck('settingsubmit')) {
	
	$setting = $_G['gp_setting'];
	foreach ($setting as $key => $val) {
		DB::query("INSERT INTO " . DB::table('hdx_player_setting') . "(uid, skey, svalue) VALUES (" . $_uid . ",'" . $key . "', '" . $val . "') ON DUPLICATE KEY UPDATE svalue = '" . $val . "'");
	}
	
	showMsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 1, array('url' => $url));

} else {
	$setting = array();
	// DEFAULT
	$query = DB::query('SELECT * FROM ' . DB::table('hdx_player_setting') . ' WHERE uid=' . $_uid);
	
	while ( $s = DB::fetch($query) ) {
		$key = $s['skey'];
		$setting[$key] = $s['svalue'];
	}
	

}
?>
