<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$url = 'plugin.php?id=zeroze007_hdx&op=shop';

if ($subop == 'gift') {
    $itemId = intval($_GET['item_id']);
    $item = DB::fetch_first("SELECT * FROM " . DB::table('hdx_shop_item') . " WHERE available= 1 AND id='{$itemId}'");

    if (!$item) {
        showError(lang('plugin/zeroze007_hdx', 'no_such_item'));
    }

    $item = escape($item, 'html');
    if (strpos($item['rate'], ',') === false) {
        $item['rate'] = $item['rate'] . '%';
    } else {
        list($low, $high) = explode(',', $item['rate']);
        $item['rate'] = $low . '% ~ ' . $high . '%';
    }
    if (strpos($item['d_loss_rate'], ',') === false) {
        $item['d_loss_rate'] = $item['d_loss_rate'];
    } else {
        $item['d_loss_rate'] = str_replace(',', ' ~ ', $item['d_loss_rate']);
    }

    if ($item['type'] == 'weapon') {
        $item['rate'] = strval(' +' . $item['rate']);
    } else if ($item['type'] == 'armor') {
        $item['rate'] = ' -' . $item['rate'];
    } else if ($item['type'] == 'food') {
        $item['rate'] = strval(' +' . str_replace("%", '', $item['rate']));
    }
} else {
// 武器列表
    $items = array();

    $query = DB::query('SELECT * FROM ' . DB::table('hdx_shop_item') . ' WHERE  available= 1 ORDER BY disp_order LIMIT ' . $_start . ',' . $_perpage);

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
            $w['rate'] = strval(' +' . $w['rate']);
        } else if ($w['type'] == 'armor') {
            $w['rate'] = ' -' . $w['rate'];
        } else if ($w['type'] == 'food') {
            $w['rate'] = strval(' +' . str_replace("%", '', $w['rate']));
        }


        $items[] = $w;
    }


    $count = DB::result_first('SELECT COUNT(*) FROM ' . DB::table('hdx_shop_item') . ' WHERE  available= 1');

    $multipage = multi($count, $_perpage, $_page, $url);
}
?>
