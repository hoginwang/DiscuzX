<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if (!submitcheck('jailsubmit')) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}

// INIT
$crimOut = false;
$result = 0;


// 需要体力
$jailRobSta = intval($_hdx['jail_rob_sta']); // 劫狱需要体力
$jailEscapeSta = intval($_hdx['jail_escape_sta']); // 逃狱需要体力
// 需要金钱
$jailBailFee = intval($_hdx['jail_bail_fee']); // 保释需要金钱
// 成功率
$jailRobRate = intval($_hdx['jail_rob_rate']); // 劫狱成功率
$jailEscapeRate = intval($_hdx['jail_escape_rate']); // 逃狱成功率

$jailRobInJailRate = intval($_hdx['jail_rob_in_jail_rate']);
$jailRobInJailTime = intval($_hdx['jail_rob_in_jail_time']);


$action = $_G['gp_action'];

$crimUid = intval($_G['gp_crimUid']);

switch ($action) {
    case 'rob' :
    case 'bail' :

        // 自己是否坐牢
        if ($_player['out_jail_time'] > $_timenow && $_player['out_jail_time'] > 0) {
            if ($action == 'rob') {
                showError(lang('plugin/zeroze007_hdx', 'could_not_rob_jail'));
            } else {
                showError(lang('plugin/zeroze007_hdx', 'could_not_bail'));
            }
        }

        if ($_player['uid'] == $crimUid) {
            showError(lang('plugin/zeroze007_hdx', 'could_not_rob_jail_and_bail', array('rob_jail' => $_hdxLang['rob_jail'])), array(
                'url' => 'plugin.php?id=zeroze007_hdx&op=jail'
            ));
        }

        $crim = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_player') . ' p 
			LEFT JOIN ' . DB::table('common_member') . ' m ON m.uid = p.uid 
			WHERE p.uid=' . $crimUid);

        if (!$crim) {
            showError(lang('plugin/zeroze007_hdx', 'no_such_player'));
        }
        if ($crim['out_jail_time'] < $_timenow) {
            $url = 'plugin.php?id=zeroze007_hdx';
            showError(lang('plugin/zeroze007_hdx', 'the_player_has_out_jail'), array('url' => $url));
        }

        // log
        $log['who_uid'] = $_uid;
        $log['to_uid'] = $crim['uid'];

        break;
}
switch ($action) {
    case 'rob' :
        if ($_sta < $jailRobSta) {
            showError(lang('plugin/zeroze007_hdx', 'sta_not_enough_to_rob_jail', array('sta' => $_staTitle, 'jail_rob_sta' => $jailRobSta, 'rob_jail' => $_hdxLang['rob_jail'])));
        }
        if (rand(0, 100) <= $jailRobRate) {

            // 劫狱成功
            $crimOut = true;

            $msg = lang('plugin/zeroze007_hdx', 'rob_jail_success', array('rob_jail' => $_hdxLang['rob_jail']));

            // log
            $log['msg'] = lang('plugin/zeroze007_hdx', 'rob_jail_success_msg', array('player_username' => $_player['username'], 'rob_jail' => $_hdxLang['rob_jail'], 'crim_username' => $crim['username']));
        } else {



            // 是否坐牢	
            if (rand(0, 100) <= $jailRobInJailRate && $jailRobInJailTime > 0) {

                $outJailTime = $_timenow + $jailRobInJailTime * 60;

                // 坐牢

                $data = array(
                    'in_jail_time' => $_timenow,
                    'out_jail_time' => $outJailTime
                );

                DB::update('hdx_player', $data, "uid='" . $_uid . "'");




                $url = 'plugin.php?id=zeroze007_hdx&op=jail';
                $redirect = 1;


                $msg = lang('plugin/zeroze007_hdx', 'rob_jail_send_to_jail', array('month' => date('n', $outJailTime), 'day' => date('j', $outJailTime), 'hour' => date('H', $outJailTime), 'minute' => date('i', $outJailTime)));

                // log

                $log['msg'] = lang('plugin/zeroze007_hdx', 'rob_jail_fail_in_jail_msg', array('player_username' => $_player['username'], 'rob_jail' => $_hdxLang['rob_jail'], 'crim_username' => $crim['username']));
            } else {
                $result = 0;
                $msg = lang('plugin/zeroze007_hdx', 'rob_jail_fail', array('rob_jail' => $_hdxLang['rob_jail']));
                $log['msg'] = lang('plugin/zeroze007_hdx', 'rob_jail_fail_msg', array('player_username' => $_player['username'], 'rob_jail' => $_hdxLang['rob_jail'], 'crim_username' => $crim['username']));
            }
        }

        /*
          $update = array();

          // update db
          $update[] = 'sta = sta -' . $jailRobSta;

          DB::query('UPDATE ' . DB::table('hdx_player') . ' SET ' . implode(',', $update) . ' WHERE uid=' . $_uid);
         *
         */
        updateSta($_uid, (0 - abs($jailRobSta)));



        $msgAry[] = lang('plugin/zeroze007_hdx', 'escape_sta_reduce', array('sta_title' => $_staExt['title'], 'jail_escape_sta' => $jailRobSta));


        $url = 'plugin.php?id=zeroze007_hdx&op=jail';

        break;
    case 'bail' :
        if ($_money < $jailBailFee) {
            showError(lang('plugin/zeroze007_hdx', 'not_enough_money_to_bail', array('money' => $_moneyTitle, 'jail_bail_fee' => $jailBailFee)));
        }

        $update = array();

        // update db
        $update[] = $_moneyExtStr . '=' . $_moneyExtStr . '-' . $jailBailFee;

        DB::query('UPDATE ' . DB::table('common_member_count') . ' SET ' . implode(',', $update) . ' WHERE uid=' . $_uid);

        // 犯人
        $crimOut = true;

        // log

        $log['msg'] = lang('plugin/zeroze007_hdx', 'bail_someone', array('money' => $jailBailFee . $_moneyTitle, 'player_username' => $_player['username'], 'crim_username' => $crim['username']));

        $msgAry[] = lang('plugin/zeroze007_hdx', 'your_money_reduce', array('money_title' => $_moneyTitle, 'jail_bail_fee' => $jailBailFee));

        $msg = lang('plugin/zeroze007_hdx', 'bail_success');

        $url = 'plugin.php?id=zeroze007_hdx&op=jail';
        break;
    case 'escape' :

        $escapeInterval = $_hdx['jail_escape_interval'] * 60; // seconds


        if ($_player['out_jail_time'] < $_timenow) {
            showError(lang('plugin/zeroze007_hdx', 'not_in_prison', array('rob_jail' => $_hdxLang['rob_jail'])), array(
                'url' => 'plugin.php?id=zeroze007_hdx'
            ));
        }


        if ($_sta < $jailEscapeSta) {
            showError(lang('plugin/zeroze007_hdx', 'not_enough_sta_to_escape', array('sta_title' => $_staTitle, 'jail_escape_sta' => $jailEscapeSta)));
        }



        if ($_player['last_escape_time'] > 0 && $_timenow - $_player['last_escape_time'] < $escapeInterval) {
            showError(lang('plugin/zeroze007_hdx', 'escape_interval_alert', array('jail_escape_interval' => $_hdx['jail_escape_interval'], 'waiting_seconds' => ($escapeInterval - ($_timenow - $_player['last_escape_time'])))));
        }


        if (rand(0, 100) < $jailEscapeRate) {
            $msg = lang('plugin/zeroze007_hdx', 'escape_success');

            DB::query('UPDATE ' . DB::table('hdx_player') . ' SET out_jail_time = 0, last_escape_time = 0 WHERE uid=' . $_uid);


            // log
            $result = 1;

            $log['msg'] = lang('plugin/zeroze007_hdx', 'escape_success_log', array('player_username' => $_player['username']));

            $url = 'plugin.php?id=zeroze007_hdx';
            
            $crimUid = $_uid;
            
            $crimOut = true;
        } else {
            $result = 0;

            $msg = lang('plugin/zeroze007_hdx', 'escape_fail');
            // log

            $log['msg'] = lang('plugin/zeroze007_hdx', 'escape_fail_log', array('player_username' => $_player['username']));

            // update last escape time
            $data = array(
                'last_escape_time' => $_timenow
            );

            DB::update('hdx_player', $data, 'uid=' . $_uid);


            $url = 'plugin.php?id=zeroze007_hdx&op=jail';
            $crimOut = false;
        }

        // update db
        //$update[] = 'sta = sta -' . $jailEscapeSta;

        if (count($update) > 0) {
            DB::query('UPDATE ' . DB::table('hdx_player') . ' SET ' . implode(',', $update) . ' WHERE uid=' . $_uid);
        }
        updateSta($_uid, (0 - abs($jailEscapeSta)));

        $msgAry[] = lang('plugin/zeroze007_hdx', 'escape_sta_reduce', array('sta_title' => $_staExt['title'], 'jail_escape_sta' => $jailEscapeSta));

        $log['who_uid'] = $_uid;
        $log['to_uid'] = 0;
        break;
}

$msg = $msg . (count($msgAry) > 0 ? '<br><br>' . implode('<br>', $msgAry) : '');

// log 
$log['created_at'] = $_timenow;
DB::insert('hdx_log', $log);

// 犯人离开监狱
if ($crimOut == true) {
    $data = array(
        'out_jail_time' => 0
    );

    DB::update('hdx_player', $data, 'uid=' . $crimUid);
}

// 输出
showMsg($msg, $result, array(
    'url' => $url
));
?>
