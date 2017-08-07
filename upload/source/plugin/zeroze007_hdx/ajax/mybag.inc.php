<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if (!submitcheck('itemsubmit')) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}


// INIT
$playerAction = $_GET['player_action'];

$itemId = intval($_G['gp_itemId']);


$itemAry = array('weapon', 'armor', 'food');

if ($playerAction != 'discard') {
    $item = DB::fetch_first('SELECT type,rate FROM ' . DB::table('hdx_shop_item') . ' si,' . DB::table('hdx_player_item') . ' pi WHERE pi.uid=' . $_uid . ' AND si.available = 1 AND pi.item_id=si.id AND pi.id=' . $itemId);

    if (!$item) {
        showError(lang('plugin/zeroze007_hdx', 'no_such_item'));
    }

    if (!in_array($item['type'], $itemAry)) {
        showError(lang('plugin/zeroze007_hdx', 'shop_item_has_no_type'));
    }
}

switch ($playerAction) {

    case 'equip':
        if (in_array($item['type'], array('weapon', 'armor'))) {
            // 是否替换现有的武器
            DB::query('UPDATE ' . DB::table('hdx_player') . ' SET ' . $item['type'] . '_id = ' . $itemId . '  WHERE uid=' . $_uid);

            $msg = lang('plugin/zeroze007_hdx', 'equip_success');
        } else {
            throw new Exception(lang('plugin/zeroze007_hdx', 'item_could_not_equip'));
        }
        break;
    case 'unload':
        if (in_array($item['type'], array('weapon', 'armor'))) {
            DB::query('UPDATE ' . DB::table('hdx_player') . ' SET ' . $item['type'] . '_id = 0 WHERE uid=' . $_uid);
            $msg = lang('plugin/zeroze007_hdx', 'unload_success');
        } else {
            throw new Exception(lang('plugin/zeroze007_hdx', 'item_could_not_unload'));
        }
        break;
    case 'discard':

        DB::query('DELETE FROM ' . DB::table('hdx_player_item') . ' WHERE uid=' . $_uid . ' AND id = ' . $itemId);
        if ($_weapon['id'] == $itemId) {
            $itemType = 'weapon';
        } else if ($_armor['id'] == $itemId) {
            $itemType = 'armor';
        }
        if ($itemType == 'weapon' || $itemType == 'armor') {
            DB::query('UPDATE ' . DB::table('hdx_player') . ' SET ' . $itemType . '_id = 0  WHERE uid=' . $_uid);
        }

        $msg = lang('plugin/zeroze007_hdx', 'discard_success');
        break;
    case 'use':
        if (!in_array($item['type'], array('food'))) {
            throw new Exception(lang('plugin/zeroze007_hdx', 'item_could_not_use'));
        }

        $foodAddRate = getRandomNumber($item['rate']);
        updateSta($_uid, $foodAddRate);

        $msg = lang('plugin/zeroze007_hdx', 'food_use_success', array('sta' => $_staTitle, 'amount' => $foodAddRate));
        DB::query('DELETE FROM ' . DB::table('hdx_player_item') . ' WHERE uid=' . $_uid . ' AND id = ' . $itemId);
        $url = 'plugin.php?id=zeroze007_hdx&op=mybag';

        break;
    default:
        showError('Invalid Action!');
}
$url = 'plugin.php?id=zeroze007_hdx&op=mybag';

// 输出
showMsg($msg, true, array(
    'url' => $url
));
?>
