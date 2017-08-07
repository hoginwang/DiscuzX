<?php

// 必须使用此判断避免外部调用
if (! defined('IN_DISCUZ') || ! defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//loadcache('plugin');


if (! submitcheck('settingsubmit', 1)) {
	// 黑道设置
	$hdx = array ();
	$query = DB::query("SELECT * FROM " . DB::table('hdx_setting') . " ");

	while ($setting = DB::fetch($query)) {
		$key = $setting['skey'];
		$hdx[$key] = $setting['svalue'];
	}

	
	showtips(lang('plugin/zeroze007_hdx', 'setting_lang_tips'));
	

	showformheader('plugins&operation=config&do=' . $pluginid . '&identifier=zeroze007_hdx&pmod=lang_setting');
	
	showtableheader('');
	
	showsetting(lang('plugin/zeroze007_hdx', 'setting_lang_hdx'), 'hdx[lang_hdx]', $hdx['lang_hdx'], 'text', '', 0, 'hdx');
	showsetting(lang('plugin/zeroze007_hdx', 'setting_lang_hd'), 'hdx[lang_hd]', $hdx['lang_hd'], 'text', '', 0, 'hd');
	showsetting(lang('plugin/zeroze007_hdx', 'setting_lang_rob'), 'hdx[lang_rob]', $hdx['lang_rob'], 'text', '', 0, 'rob');
	showsetting(lang('plugin/zeroze007_hdx', 'setting_lang_rob_jail'), 'hdx[lang_rob_jail]', $hdx['lang_rob_jail'], 'text', '', 0, 'rob_jail');
	
	showsubmit('settingsubmit');
	showtablefooter();
	showformfooter();
} else {
	
	$hdx = $_G['gp_hdx'];
	
	foreach ($hdx as $key => $val) {
				$key = dhtmlspecialchars($key);
		$val = dhtmlspecialchars($val);
		DB::query("INSERT INTO " . DB::table('hdx_setting') . "(skey, svalue) VALUES ('" . $key . "', '" . $val . "') ON DUPLICATE KEY UPDATE svalue = '" . $val . "'");
	}
	cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=plugins&operation=config&do=' . $pluginid . '&identifier=zeroze007_hdx&pmod=lang_setting', 'succeed');

}
?>
