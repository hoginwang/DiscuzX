<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}



$url = 'plugin.php?id=zeroze007_hdx&op=msg';

// 会员列表


$query = DB::query('SELECT * FROM ' . DB::table('hdx_msg') . ' g 
	LEFT JOIN ' . DB::table('common_member') . ' m ON m.uid=g.from_uid 
	WHERE to_uid =' . $_uid . ' ORDER BY g.id DESC LIMIT ' . $_start . ',' . $_perpage);

$msgs = array();
while ($m = DB::fetch($query)) {
    $m = escape($m, 'html');
    if ($m['from_uid'] == 0) {
        $m['sender'] = lang('plugin/zeroze007_hdx', 'system_msg');
    }
    $m['time'] = date('Y-n-d H:i:s', $m['created_at']);
    $msgs[] = $m;
}

$count = DB::result_first('SELECT COUNT(*) FROM ' . DB::table('hdx_msg') . ' 
	WHERE to_uid =' . $_uid);


$multipage = multi($count, $_perpage, $_page, $url);
?>
