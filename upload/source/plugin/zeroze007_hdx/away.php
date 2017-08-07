<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}


if (!$_hdx['allow_away']) {
    throw new Exception(lang('plugin/zeroze007_hdx', 'system_not_allow_away', array('hd' => $_hdxLang['hd'])));
}
if ($_player['uid'] > 0) {
    throw new Exception(lang('plugin/zeroze007_hdx', 'in_game_could_not_away', array('hd' => $_hdxLang['hd'])));
}


$playerActivity = DB::fetch_first("SELECT * FROM " . DB::table('hdx_player_activity') . " WHERE uid = '" . $_uid . "'");

if ($playerActivity && $playerActivity['type'] == ACTIVITY_AWAY && $playerActivity['expired_time'] > $_timenow) {
    
    throw new Exception(lang('plugin/zeroze007_hdx', 'you_alreay_away', array('hd' => $_hdxLang['hd'],
                'year' => date('Y', $playerActivity['expired_time']),
        'month' => date('n', $playerActivity['expired_time']),
        'day' => date('j', $playerActivity['expired_time']),
        'hour' => date('H', $playerActivity['expired_time']),
        'minute' => date('i', $playerActivity['expired_time']))
                    
            )
    );
}


$awayFee = intval($_hdx['away_fee']);
$awayMaxDays = intval($_hdx['away_max_days']);
?>
