<?php

/**
 *      $Id: cron_member_lock_daily.php 00001 2016-2-10 22:07:47 jiangyou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//status = -1  锁定
//$dateline = TIMESTAMP - 7776000;//60*60*24*90
$dateline = mktime(0, 0, 0, date('m',TIMESTAMP)-3, 0, date('Y',TIMESTAMP));
//超长不活跃 
DB::query('UPDATE '.DB::table('common_member').' m 
	LEFT JOIN '.DB::table('common_member_status').' ms ON m.uid=ms.uid 
	SET m.status=-1, m.freeze=1 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (1,2,3,16,17,18,19,21,22,33,34,35,36,37) 
	AND ms.lastpost !=0 AND ms.lastpost <= %d', array($dateline));

//超长不活跃存档表用户
if(DB::fetch_first("SHOW TABLES LIKE 'common_member_archive'") && DB::fetch_first("SHOW TABLES LIKE 'common_member_status_archive'")) {
	DB::query('UPDATE '.DB::table('common_member_archive').' m 
	LEFT JOIN '.DB::table('common_member_status_archive').' ms ON m.uid=ms.uid 
	SET m.status=-1, m.freeze=1 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (1,2,3,16,17,18,19,21,22,33,34,35,36,37) 
	AND ms.lastpost !=0 AND ms.lastpost <= %d', array($dateline));
}

//7天未验证邮箱
//$dateline = TIMESTAMP - 604800;//60*60*24*7
$dateline = mktime(0, 0, 0, date('m',TIMESTAMP), date('d',TIMESTAMP) -7, date('Y',TIMESTAMP));
DB::query('UPDATE '.DB::table('common_member').' m 
	SET m.freeze=1 
	WHERE m.status !=-1 AND m.adminid <=0 
	AND m.emailstatus !=1 AND m.regdate <= %d', array($dateline));

//新用户7天未发表
DB::query('UPDATE '.DB::table('common_member').' m 
	LEFT JOIN '.DB::table('common_member_status').' ms ON m.uid=ms.uid 
	SET m.status=-1, m.freeze=1 
	WHERE m.status !=-1 AND m.adminid <=0 AND m.groupid NOT IN (1,2,3,16,17,18,19,21,22,33,34,35,36,37) 
	AND ms.lastpost = 0 AND m.regdate <= %d', array($dateline));
?>
