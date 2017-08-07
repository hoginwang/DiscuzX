<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if (!submitcheck('formsubmit')) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}


// INIT
if ($_POST['item_action'] == 'shop_gift') {

    $pickType = $_G['gp_pickType'];

    if ($pickType == '1') {
        $memberName = iconv('utf-8', $_charset, $_G['gp_memberName']);
        $query = 'SELECT * FROM ' . DB::table('common_member') . ' m,' . DB::table('hdx_player') . ' p WHERE p.uid=m.uid AND m.username = \'' . dhtmlspecialchars($memberName) . '\'';
    } else {
        $memberUid = intval($_G['gp_memberUid']);
        $query = 'SELECT * FROM ' . DB::table('common_member') . ' m,' . DB::table('hdx_player') . ' p WHERE p.uid=m.uid AND m.uid = ' . $memberUid;
    }

    $receiver = DB::fetch_first($query);

    if (!$receiver) {
        showError(lang('plugin/zeroze007_hdx', 'no_such_player_to_recieve'));
    }

    $player = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_player') . ' WHERE uid=' . $receiver['uid']);

    if (!$player) {

        showError(lang('plugin/zeroze007_hdx', 'member_not_in_hd_to_give', array('hd' => $_hdxLang['hd'])));
    }

    if ($receiver['uid'] == $_uid) {

        showError(lang('plugin/zeroze007_hdx', 'could_not_give_yourself'));
    }
}

$itemId = intval($_POST['itemId']);
$item = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_shop_item') . ' WHERE id=' . $itemId);

if (!$item) {
    showError(lang('plugin/zeroze007_hdx', 'no_such_item'));
}



// 限制
$price = $item['price'];

if ($_money < $price) {
    // 是否够钱
    showError(lang('plugin/zeroze007_hdx', 'money_not_enough_to_buy_item', array('money_title' => $_moneyTitle, 'price' => $item['price'])));
}

$msgAry[] = lang('plugin/zeroze007_hdx', 'money_reduce', array('money_title' => $_moneyTitle, 'amount' => $item['price']));




// update array
$update = array();


// update db
$update[] = $_moneyExtStr . '=' . $_moneyExtStr . '-' . $price;

DB::query('UPDATE ' . DB::table('common_member_count') . ' SET ' . implode(',', $update) . ' WHERE uid=' . $_uid);


if ($_POST['item_action'] == 'shop_gift') {

    DB::query('INSERT INTO ' . DB::table('hdx_player_item') . ' (uid, item_id, durability) VALUES (' . $receiver['uid'] . ',' . $item['id'] . ',' . $item['durability'] . ')');

    // send msg
    $subject = lang('plugin/zeroze007_hdx', 'you_receive_gift', array('player_username' => $_player[username], 'hdx' => $_hdxLang['hdx']));
    $message = lang('plugin/zeroze007_hdx', 'gift_in_your_bag_now', array('url' => 'plugin.php?id=zeroze007_hdx&op=mybag', 'item' => $item['name']));

    // DZ提醒
    notification_add($receiver['uid'], 'system', 'system_notice', array(
        'subject' => $subject,
        'message' => $message
            ), 1);

  

    $msg = lang('plugin/zeroze007_hdx', 'gift_success');
} else {


    DB::query('INSERT INTO ' . DB::table('hdx_player_item') . ' (uid, item_id, durability) VALUES (' . $_uid . ',' . $item['id'] . ',' . $item['durability'] . ')');


    $msg = lang('plugin/zeroze007_hdx', 'buy_success');
}
$msg = $msg . '<br><br>' . implode('<br>', $msgAry);
$url = 'plugin.php?id=zeroze007_hdx&op=shop';

// 输出
showMsg($msg, true, array(
    'url' => $url
));
?>
