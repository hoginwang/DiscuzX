<?php

// 必须使用此判断避免外部调用
if (! defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$_hdx['rob_success_sw'] = str_replace(",", "-", $_hdx['rob_success_sw']);
$_hdx['rob_fail_sw'] = str_replace(",", "-", $_hdx['rob_fail_sw']);
$_hdx['rob_member_sta'] = str_replace(",", "-", $_hdx['rob_member_sta']);




$robSuggestList = trim($_hdx['rob_suggest_list']);
if (! empty($robSuggestList)) {
	$suggestListAry = explode("\n", $robSuggestList);
	$suggestLists = array();
	foreach ($suggestListAry as $ary) {
		$suggestLists[] = intval($ary);
	}
	
	if (count($suggestLists) > 0) {
		$suggestMembers = array();
		
		$query = DB::query('SELECT * FROM '. DB::table('common_member') . ' WHERE uid IN ('. implode(',', $suggestLists) .')'); 
		while ($data = DB::fetch($query)) {
			$suggestMembers[] = $data;
		}
	}
}

?>
