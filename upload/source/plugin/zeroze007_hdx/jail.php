<?php

// 必须使用此判断避免外部调用
if (! defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$url = 'plugin.php?id=zeroze007_hdx&op=jail';

// 犯人列表
$query = DB::query('SELECT * FROM '. DB::table('hdx_player') .' p 
	LEFT JOIN '. DB::table('common_member') .' m ON m.uid=p.uid 
	WHERE out_jail_time > '. $_timenow .' AND available = 1 LIMIT '. $_start .','. $_perpage);

$crims = array();
while($data = DB::fetch($query)) {
    $data = escape($data, 'html');
	$crims[] = $data;
}

$count = DB::result_first('SELECT COUNT(*) FROM '. DB::table('hdx_player') .' 
	WHERE out_jail_time > '. $_timenow .' AND available = 1');

if ($_player['out_jail_time'] > $_timenow && $_player['out_jail_time'] > 0) {
	$outJailMonth = date('n', $_player['out_jail_time']);
    $outJailDay = date('j', $_player['out_jail_time']);
    $outJailHour = date('H', $_player['out_jail_time']);
    $outJailMinute = date('i', $_player['out_jail_time']);
    
}

$multipage = multi($count, $_perpage, $_page, $url);


?>
