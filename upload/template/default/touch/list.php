<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
function get_attach($kmlist){
	global $_G;
	require_once libfile('function/post');
	$tids = $attach_tids = $attachtableid_array = $threadlist_data = array();
	foreach($kmlist as $value) {
		$tids[] = $value['tid'];
		if($value['attachment'] == 2) {
			$attach_tids[] = $value['tid'];
		}
	}
	$query = DB::fetch_all('SELECT tid, pid, message FROM %t WHERE '.DB::field('tid', $tids).' AND first = 1', array('forum_post'));
	foreach($query as $value) {
		$threadlist_data[$value['tid']]['message'] = messagecutstr($value['message'], 90);
		if(in_array($value['tid'], $attach_tids)) {
			$attachtableid_array[getattachtableid($value['tid'])][] = $value['pid'];
		}
	}
	foreach($attachtableid_array as $tableid => $pids) {
		$query = DB::query('SELECT tid, aid FROM `'. DB::table('forum_attachment_' .intval($tableid)). '` WHERE pid IN (' .dimplode($pids). ') AND isimage IN (1, -1)');
		while($value = DB::fetch($query)) {
			$threadlist_data[$value['tid']]['attachment'][] = getforumimg($value['aid'], '0', '300', '250');
		}
	}
	return $threadlist_data;
}