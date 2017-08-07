<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

// log
if ($subop == 'my') {
    $query = DB::query('SELECT * FROM ' . DB::table('hdx_log') . ' WHERE who_uid=' . $_uid . ' OR to_uid=' . $_uid . ' ORDER BY created_at DESC LIMIT 11');
} else {
    $query = DB::query('SELECT * FROM ' . DB::table('hdx_log') . ' ORDER BY created_at DESC LIMIT 11 ');
}
$logs = array();
while ($l = DB::fetch($query)) {
    $l['msg'] = msgFilter($l['msg'], $_player['username'], lang('plugin/zeroze007_hdx', 'i'));
    $logs[] = $l;
}





$rankTopNum = intval($_setting['rank_top_num']);

if ($rankTopNum > 0) {
    $robRanks = array();
    $query = DB::query("
SELECT m.uid,m.username,p.rob_times, p.rob_money_amount  
FROM " . DB::table('hdx_player') . " p," . DB::table('common_member') . " m 
WHERE p.uid=m.uid AND p.available = 1 
ORDER BY rob_times DESC LIMIT " . $rankTopNum);

    $players = array();
    $i = 0;
    while ($p = DB::fetch($query)) {
        $p = escape($p, 'html');
        $i++;
        $p['rank'] = $i;

        $p['rob_times'] = intval($p['rob_times']);
        $robRanks[] = $p;
    }



    $badLuckRanks = array();
    $query = DB::query("
SELECT m.uid,m.username,v.robbed_times, v.robbed_money_amount  
FROM " . DB::table('hdx_player') . " p," . DB::table('common_member') . " m 
LEFT JOIN " . DB::table('hdx_victim') . " v ON v.uid=m.uid 
WHERE p.uid=m.uid AND p.available = 1 
ORDER BY robbed_times DESC LIMIT " . $rankTopNum);

    $i = 0;
    while ($p = DB::fetch($query)) {
        $p = escape($p, 'html');
        $i++;
        $p['rank'] = $i;

        $p['robbed_times'] = intval($p['robbed_times']);
        $p['robbed_money_amount'] = intval($p['robbed_money_amount']);
        $badLuckRanks[] = $p;
    }
    
    
    
     $levelRanks = array();
    $query = DB::query("
SELECT m.uid,m.username,p.level,p.title  
FROM " . DB::table('hdx_player') . " p," . DB::table('common_member') . " m 
WHERE p.uid=m.uid AND p.available = 1 
ORDER BY level DESC,exp DESC LIMIT " . $rankTopNum);

    $players = array();
    $i = 0;
    while ($p = DB::fetch($query)) {
        $p = escape($p, 'html');
        $i++;
        $p['rank'] = $i;

        $levelRanks[] = $p;
    }
    
    $moneyRanks = array();
    $query = DB::query("
SELECT m.uid,m.username," . $_moneyExtStr . " money,p.rob_money_amount  
FROM " . DB::table('hdx_player') . " p," . DB::table('common_member') . " m,
    " . DB::table('common_member_count') . " mc 
WHERE p.uid=m.uid AND m.uid=mc.uid AND p.available = 1 
ORDER BY " . $_moneyExtStr . " DESC LIMIT " . $rankTopNum);


    $i = 0;
    while ($p = DB::fetch($query)) {
        $p = escape($p, 'html');
        $i++;
        $p['rank'] = $i;

        $moneyRanks[] = $p;
    }
}
?>
