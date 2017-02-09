<?php
/**
 *      $Id: cron_member_lock_notice_send.php 00001 2016-02-23 23:01:13 jiangyou $
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
@set_time_limit(0);
$superGroup = implode(',', array(1,2,3,16,17,18,19,21,22,33,34,35,36,37,40));
$superUID = implode(',', array(89));
$countPre = 'SELECT count(1) FROM ';
$selPre = 'SELECT m.uid as uid FROM ';
//超长不活跃
$dateline = strtotime('-3 months +3 day', TIMESTAMP);
$bastSql = DB::table('common_member') . ' m 
	LEFT JOIN ' . DB::table('common_member_status') . ' ms ON m.uid=ms.uid 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%s) AND m.uid NOT IN (%s) 
	AND ms.lastpost !=0 AND ms.lastpost <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($dateline));
    foreach ($resultTmp as $r) {
		notification_add(
			$r['uid'], 'system', '&#36134;&#21495;&#36829;&#21453;&#24635;&#29256;&#35268;&#31532;&#19971;&#22823;&#39033;&#31532;&#49;&#26465;&#21363;&#23558;&#34987;&#31995;&#32479;&#31105;&#29992;&#65292;&#35831;&#31435;&#21363;&#25913;&#21892;&#65281;<br/>&#24635;&#29256;&#35268;&#65306;https://www.yueing.org/forum.php?mod=viewthread&tid=2<br/>&#22914;&#26377;&#20219;&#20309;&#30097;&#38382;&#35831;&#33267;&#25509;&#24453;&#20013;&#24515;&#21453;&#39304;',
			array() ,
			1
		);
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
			notification_add(
				$r['uid'], 'system', '&#36134;&#21495;&#36829;&#21453;&#24635;&#29256;&#35268;&#31532;&#19971;&#22823;&#39033;&#31532;&#49;&#26465;&#21363;&#23558;&#34987;&#31995;&#32479;&#31105;&#29992;&#65292;&#35831;&#31435;&#21363;&#25913;&#21892;&#65281;<br/>&#24635;&#29256;&#35268;&#65306;https://www.yueing.org/forum.php?mod=viewthread&tid=2<br/>&#22914;&#26377;&#20219;&#20309;&#30097;&#38382;&#35831;&#33267;&#25509;&#24453;&#20013;&#24515;&#21453;&#39304;',
				array() ,
				1
			);
		}
    }
}
//7天未验证邮箱
$dateline = strtotime('-3 day', TIMESTAMP);
$bastSql = DB::table('common_member') . ' m 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%s) AND m.uid NOT IN (%s) 
	AND m.emailstatus !=1 AND m.regdate <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($dateline));
	foreach ($resultTmp as $r) {
		notification_add(
			$r['uid'], 'system', '&#36134;&#21495;&#36829;&#21453;&#24635;&#29256;&#35268;&#31532;&#19971;&#22823;&#39033;&#31532;&#50;&#26465;&#21363;&#23558;&#34987;&#31995;&#32479;&#31105;&#29992;&#65292;&#35831;&#31435;&#21363;&#25913;&#21892;&#65281;<br/>&#24635;&#29256;&#35268;&#65306;https://www.yueing.org/forum.php?mod=viewthread&tid=2<br/>&#22914;&#26377;&#20219;&#20309;&#30097;&#38382;&#35831;&#33267;&#25509;&#24453;&#20013;&#24515;&#21453;&#39304;',
			array() ,
			1
		);
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
		notification_add(
			$r['uid'], 'system', '&#36134;&#21495;&#36829;&#21453;&#24635;&#29256;&#35268;&#31532;&#19971;&#22823;&#39033;&#31532;&#50;&#26465;&#21363;&#23558;&#34987;&#31995;&#32479;&#31105;&#29992;&#65292;&#35831;&#31435;&#21363;&#25913;&#21892;&#65281;<br/>&#24635;&#29256;&#35268;&#65306;https://www.yueing.org/forum.php?mod=viewthread&tid=2<br/>&#22914;&#26377;&#20219;&#20309;&#30097;&#38382;&#35831;&#33267;&#25509;&#24453;&#20013;&#24515;&#21453;&#39304;',
			array() ,
			1
		);
	}
}
?>