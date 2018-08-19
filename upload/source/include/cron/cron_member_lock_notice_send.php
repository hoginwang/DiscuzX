<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
@set_time_limit(0);
$superGroup = array(1, 2, 3, 16, 17, 18, 19, 21, 22, 33, 34, 35, 36, 37, 40);
$superUID = array(89);
$countPre = 'SELECT count(1) FROM ';
$selPre = 'SELECT m.uid as uid FROM ';
//超长不活跃
$dateline = strtotime('-3 months -7 day', TIMESTAMP);
$bastSql = DB::table('common_member') . ' m 
	LEFT JOIN ' . DB::table('common_member_status') . ' ms ON m.uid=ms.uid 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%n) AND m.uid NOT IN (%n) 
	AND ms.lastpost !=0 AND ms.lastpost <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($superGroup, $superUID, $dateline));
    foreach ($resultTmp as $r) {
        notification_add(
            $r['uid'], 'system', '违规预警：总版规第七大项第1条，请立即改善！<br/>总版规：<a href="forum.php?mod=viewthread&tid=2">前往查看</a><br/>如有疑问请至接待中心反馈!',
            array(),
            1
        );
    }
}
//超长不活跃存档表用户
if (DB::fetch_first("SHOW TABLES LIKE 'common_member_archive'") && DB::fetch_first("SHOW TABLES LIKE 'common_member_status_archive'")) {
    $bastSql = DB::table('common_member_archive') . ' m 
		LEFT JOIN ' . DB::table('common_member_status_archive') . ' ms ON m.uid=ms.uid 
		WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%n) AND m.uid NOT IN (%n) 
		AND ms.lastpost !=0 AND ms.lastpost <= %d';
    if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
        $resultTmp = DB::fetch_all($selPre . $bastSql, array($superGroup, $superUID, $dateline));
        foreach ($resultTmp as $r) {
            notification_add(
                $r['uid'], 'system', '违规预警：总版规第七大项第1条，请立即改善！<br/>总版规：<a href="forum.php?mod=viewthread&tid=2">前往查看</a><br/>如有请至接待中心反馈!',
                array(),
                1
            );
        }
    }
}
//7天未验证邮箱
$dateline = strtotime('-3 day', TIMESTAMP);
$bastSql = DB::table('common_member') . ' m 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%n) AND m.uid NOT IN (%n) 
	AND m.emailstatus !=1 AND m.regdate <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($superGroup, $superUID, $dateline));
    foreach ($resultTmp as $r) {
        notification_add(
            $r['uid'], 'system', '违规预警：总版规第七大项第2条，请立即改善！<br/>总版规：<a href="forum.php?mod=viewthread&tid=2">前往查看</a><br/>如有请至接待中心反馈!',
            array(),
            1
        );
    }
}
//新用户7天未发表
$bastSql = DB::table('common_member') . ' m 
	LEFT JOIN ' . DB::table('common_member_status') . ' ms ON m.uid=ms.uid 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (%n) AND m.uid NOT IN (%n) 
	AND ms.lastpost = 0 AND m.regdate <= %d';
if (DB::result_first($countPre . $bastSql, array($superGroup, $superUID, $dateline))) {
    $resultTmp = DB::fetch_all($selPre . $bastSql, array($superGroup, $superUID, $dateline));
    foreach ($resultTmp as $r) {
        notification_add(
            $r['uid'], 'system', '违规预警：总版规第七大项第2条，请立即改善！<br/>总版规：<a href="forum.php?mod=viewthread&tid=2">前往查看</a><br/>如有请至接待中心反馈!',
            array(),
            1
        );
    }
}
?>