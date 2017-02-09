<?php

/**
 *      $Id: cron_member_lock_daily.php 00001 2016-2-10 22:07:47 jiangyou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
@set_time_limit(0);
function recordaction($uid, $reason) {
	global $_G;
	$uid = intval($uid);
	$insert = array(
		'uid' => $uid,
		'operatorid' => 0,
		'operator' => 'SYSTEM',
		'action' => 6,
		'reason' => $reason,
		'dateline' => $_G['timestamp']
	);
	C::t('common_member_crime')->insert($insert);
	return true;
}
$superGroup = implode(',', array(1,2,3,16,17,18,19,21,22,33,34,35,36,37,40));
$superUID = implode(',', array(89));
$countPre = 'SELECT count(1) FROM ';
$selPre = 'SELECT m.uid as uid FROM ';
//超长不活跃
$dateline = strtotime('-3 months', TIMESTAMP);
$bastSql = DB::table('common_member') . ' m 
	LEFT JOIN ' . DB::table('common_member_status') . ' ms ON m.uid=ms.uid 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%s) AND m.uid NOT IN (%s) 
	AND ms.lastpost !=0 AND ms.lastpost <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($dateline));
    foreach ($resultTmp as $r) {
		recordaction($r['uid'], '超长不活跃');
	}
}
//超长不活跃存档表用户
if (DB::fetch_first("SHOW TABLES LIKE 'common_member_archive'") && DB::fetch_first("SHOW TABLES LIKE 'common_member_status_archive'")) {
    $bastSql = DB::table('common_member_archive') . ' m 
		LEFT JOIN ' . DB::table('common_member_status_archive') . ' ms ON m.uid=ms.uid 
		WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%s) AND m.uid NOT IN (%s) 
		AND ms.lastpost !=0 AND ms.lastpost <= %d';
    if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
        $resultTmp = DB::fetch_all($selPre . $bastSql, array($dateline));
		foreach ($resultTmp as $r) {
			recordaction($r['uid'], '超长不活跃');
		}
    }
}
//7天未验证邮箱
$dateline = strtotime('-7 day', TIMESTAMP);
$bastSql = DB::table('common_member') . ' m 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%s) AND m.uid NOT IN (%s) 
	AND m.emailstatus !=1 AND m.regdate <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($dateline));
	foreach ($resultTmp as $r) {
		recordaction($r['uid'], '7天未验证邮箱');
	}
}
//新用户7天未发表
$bastSql = DB::table('common_member') . ' m 
	LEFT JOIN ' . DB::table('common_member_status') . ' ms ON m.uid=ms.uid 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%s) AND m.uid NOT IN (%s) 
	AND ms.lastpost = 0 AND m.regdate <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($dateline));
	foreach ($resultTmp as $r) {
		recordaction($r['uid'], '新用户7天未发表');
	}
}
?>