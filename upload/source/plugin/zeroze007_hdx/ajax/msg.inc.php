<?php

// 必须使用此判断避免外部调用
if (! defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!submitcheck('msgsubmit')) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}


// INIT
$url = 'plugin.php?id=zeroze007_hdx&op=msg';

$ids = $_G['gp_ids'];
if (empty($ids)) {
	showError(lang('plugin/zeroze007_hdx', 'choose_record_to_delete'));
}

$idAry = array();
foreach($ids as $id) {
	$idAry[] = intval($id);
}

DB::query('DELETE FROM '. DB::table('hdx_msg') .' WHERE to_uid = '. $_uid .' AND id IN ('. implode(',', $idAry) .')');

$msg = lang('plugin/zeroze007_hdx', 'done_successfully');


// 输出
showMsg($msg, true, array(
	'url' => $url
));

?>
