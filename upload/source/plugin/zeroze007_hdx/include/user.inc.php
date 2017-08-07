<?php

// 必须使用此判断避免外部调用
if (!defined('IN_PLUGIN')) {
    exit('Access Denied');
}

// get user detail
if ($_uid > 0) {

    if (intval($_setting['sw_ext']) > 0 && intval($_setting['sw_ext']) < 9) {
        $swField = 'mc.extcredits' . intval($_setting['sw_ext']) . ' as player_sw';
    } else {
        $swField = 'p.sw as player_sw';
    }

    if (intval($_setting['sta_ext']) > 0 && intval($_setting['sta_ext']) < 9) {
        $staField = 'mc.extcredits' . intval($_setting['sta_ext']) . ' as player_sta';
    } else {
        $staField = 'p.sta as player_sta';
    }


    // get member detail
    $_player = DB::fetch_first('
            SELECT m.username,
                mc.' . $_moneyExtStr . ' money,' . $swField . ',' . $staField . ',
                p.*
            FROM ' . DB::table('common_member') . ' m, ' . DB::table('common_member_count') . ' mc, ' . DB::table('hdx_player') . ' p 
            WHERE p.uid=m.uid AND m.uid=mc.uid AND p.uid = \'' . $_uid . '\'');
    if ($_player) {

        // 会员金钱和体力
        $_money = $_player['money'];
        $_sta = $_player['player_sta'];
        $_sw = $_player['player_sw'];
        $_exp = $_player['exp'];

        $_level = intval($_player['level']);

        $_title = $_player['title'];

        $_next_level_required_exp = nextLevelExp($_level, $_hdx['level_rate']);



        // if next level reqruired exp is bigger than current exp, that means we need to cal what the final level is.
        if ($_next_level_required_exp < $_exp) {

            while ($_next_level_required_exp < $_exp) {
                $_level++;
                $_next_level_required_exp = nextLevelExp($_level, $_hdx['level_rate']);
            }


            $_title = getPlayerTitle($_level);


            // update level
            DB::query("UPDATE " . DB::table('hdx_player') . " SET level='" . $_level . "', title='" . escape($_title) . "' WHERE uid='" . $_uid . "'");
        }


        $_title = escape($_title, 'html');




        $_weapon = array(
            'id' => 0,
            'name' => lang('plugin/zeroze007_hdx', 'none'),
            'durability' => 0
        );

        $_armor = array(
            'id' => 0,
            'name' => lang('plugin/zeroze007_hdx', 'none'),
            'durability' => 0,
        );

        $itemCond = array();
        if ($_player['weapon_id'] > 0) {
            $itemCond[] = 'pi.id=' . $_player['weapon_id'];
        }
        if ($_player['armor_id'] > 0) {
            $itemCond[] = 'pi.id=' . $_player['armor_id'];
        }

        if (count($itemCond) > 0) {
            $itemCondStr = ' AND (' . implode(' OR ', $itemCond) . ')';

            $query = DB::query('SELECT si.*,pi.durability current_durability,pi.id player_item_id FROM ' . DB::table('hdx_shop_item') . ' si,' . DB::table('hdx_player_item') . ' pi WHERE pi.uid=' . $_uid . ' AND si.available = 1 AND pi.item_id=si.id ' . $itemCondStr);
            $items = array();
            while ($data = DB::fetch($query)) {
                $items[] = $data;
            }


            foreach ($items as $item) {
                $pitem = array(
                    'id' => $item['player_item_id'],
                    'name' => $item['name'],
                    'durability' => ($item['current_durability'] <= 0 ? '<font color=red>0</font>' : $item['current_durability']),
                    'original_durability' => $item['durability'],
                    'rate' => $item['rate'],
                    'd_loss_rate' => $item['d_loss_rate']
                );
                if ($item['type'] == 'weapon') {
                    $_weapon = $pitem;
                } else if ($item['type'] == 'armor') {
                    $_armor = $pitem;
                }
            }
        } else {
            $itemCondStr = '';
        }


        // guard
        
          $sql = "SELECT m.username guard_name,g.* FROM " . DB::table("hdx_guard") . " g," . DB::table("common_member") . " m WHERE m.uid=g.uid AND g.employer_uid=" . $_uid . " AND g.expired_time > " . intval($_timenow);
          $playerGuard = DB::fetch_first($sql);

          $playerGuard['protect_until'] = lang('plugin/zeroze007_hdx', 'protect_until', array(
          'month' => date('n', $playerGuard['expired_time']),
          'day' => date('j', $playerGuard['expired_time']),
          'hour' => date('H', $playerGuard['expired_time']),
          'minute' => date('i', $playerGuard['expired_time']))); 
    } else {
        $_money = 0;
        $_sta = 0;
        $_sw = 0;
        $_exp = 0;
        $_level = 0;
        $_title = '';
    }
} else {
    $_money = 0;
    $_sta = 0;
    $_sw = 0;
    $_exp = 0;
    $_level = 0;
    $_title;
}
?>