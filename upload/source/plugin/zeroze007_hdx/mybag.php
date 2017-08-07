<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$url = 'plugin.php?id=zeroze007_hdx&op=mybag';

// 武器列表
$items = array();
$query = DB::query('SELECT pi.id player_item_id,pi.durability player_item_durability, si.* FROM ' . DB::table('hdx_player_item') . ' pi,' . DB::table('hdx_shop_item') . ' si WHERE si.available = 1 AND pi.item_id = si.id AND pi.uid = '. intval($_uid) .' LIMIT ' . $_start . ',' . $_perpage);

while ($w = DB::fetch($query)) {
    $w = escape($w, 'html');
    if (strpos($w['rate'], ',') === false) {
        $w['rate'] = $w['rate'] . '%';
    } else {
        list($low, $high) = explode(',', $w['rate']);
        $w['rate'] = $low . '% ~ ' . $high . '%';
    }
    if (strpos($w['d_loss_rate'], ',') === false) {
        $w['d_loss_rate'] = $w['d_loss_rate'];
    } else {
        $w['d_loss_rate'] = str_replace(',', ' ~ ', $w['d_loss_rate']);
    }
    
    if ($w['type'] == 'weapon') {
        $w['rate'] = strval(' +'. $w['rate']);
    } else if ($w['type'] == 'armor') {
        $w['rate'] = ' -'. $w['rate'];
    } else if ($w['type'] == 'food') {
         $w['rate'] = strval(' +'. str_replace("%",'',$w['rate']));
     }
    if ($w['player_item_durability'] <=0 ) {
        $w['player_item_durability'] = '<font color=red>0</font>';
    }
    $items[] = $w;
}


$count = DB::result_first('SELECT COUNT(*) FROM ' . DB::table('hdx_player_item') . ' pi,' . DB::table('hdx_shop_item') . ' si WHERE si.available = 1 AND pi.item_id = si.id AND pi.uid = '. intval($_uid));

$multipage = multi($count, $_perpage, $_page, $url);
?>
