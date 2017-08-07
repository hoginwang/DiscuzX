<?php
// 必须使用此判断避免外部调用
if (! defined('IN_PLUGIN')) {
	exit('Access Denied');
}



// 犯人总数
$crimsCount = DB::result_first('SELECT COUNT(*) FROM '. DB::table('hdx_player') .' WHERE available = 1 AND out_jail_time > '. $_timenow);

// 消息总数
$msgCount = DB::result_first('SELECT COUNT(*) FROM '. DB::table('hdx_msg') .' WHERE to_uid = '. $_uid);



?>