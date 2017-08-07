<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
// INIT
$result = 0;
if (empty($_GET['from_op'])) {
    $fromOp = 'home';
} else {
    $fromOp = escape($_GET['from_op'], 'url');
}
if ($_REQUEST['redirect'] == 1) {
    $url = 'plugin.php?id=zeroze007_hdx&op=' . $fromOp;
}


if (!submitcheck('robsubmit', 1)) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}

//
$inThread = intval($_G['gp_inThread']); // 是否在帖子里面打劫
// update array
$robberMemberUpdate = array();
$victimMemberUpdate = array();
$victimPlayerUpdate = array();
$robberPlayerUpdate = array();

// 当天打劫次数限制
$robDayMax = $_hdx['rob_day_max'];

// 是否允许打劫论坛会员
$robAnyone = $_hdx['rob_anyone'];

// 每人每天被打劫次数限制
$dailyRobbedTimes = intval($_hdx['rob_daily_robbed_times']);

// SETTING 两次打劫时间间隔
$robInterval = intval($_hdx['rob_interval']);
if ($robInterval > 0) {
    if ($_player['last_rob_time'] > 0 && $_timenow - $_player['last_rob_time'] < $robInterval) {


        showError(lang('plugin/zeroze007_hdx', 'rob_interval', array('rob' => $_hdxLang['rob'], 'rob_interval' => $robInterval, 'waiting_time' => ($robInterval - ($_timenow - $_player['last_rob_time'])))));
    }
}

// 当天打劫次数限制
if ($robDayMax != '') {

    if (intval($robDayMax) > 0) {
        if ($_player['last_rob_time'] > 0) {
            $lastRobDay = date('z', $_player['last_rob_time']);
            $day = date('z', $_timenow);

            if ($lastRobDay == $day) {
                // 当天
                if ($_player['rob_day_count'] + 1 > $robDayMax) {

                    showError(lang('plugin/zeroze007_hdx', 'daily_rob_limit', array('rob' => $_hdxLang['rob'], 'rob_day_max' => $robDayMax)));
                }

                $robberPlayerUpdate[] = 'rob_day_count = rob_day_count + 1';
            } else {
                $robberPlayerUpdate[] = 'rob_day_count = 1';
            }
        } else {
            $robberPlayerUpdate[] = 'rob_day_count = 1';
        }
    } elseif (intval($robDayMax) == 0) {

        showError(lang('plugin/zeroze007_hdx', 'rob_function_disabled', array('rob' => $_hdxLang['rob'])));
    }
}

// 打劫需要体力
$robMemberSta = $_hdx['rob_member_sta'];
$robPlayerSta = $_hdx['rob_player_sta'];

$pickType = $_G['gp_pickType'];

if ($pickType == '1') {
    $memberName = dhtmlspecialchars(iconv('utf-8', $_charset, $_G['gp_memberName']));
    //die($memberName);
    $sql = 'SELECT * FROM ' . DB::table('common_member') . ' m LEFT JOIN ' . DB::table('common_member_count') . ' mc ON m.uid=mc.uid 
		WHERE m.username = \'' . $memberName . '\'';
} else {
    if ($pickType == '2') {
        $memberUid = intval($_G['gp_memberUid']);
    } else {
        $memberUid = intval($_G['gp_suggestMemberUid']);
    }

    $sql = 'SELECT m.groupid,m.username,m.uid,mc.* FROM ' . DB::table('common_member') . ' m LEFT JOIN ' . DB::table('common_member_count') . ' mc ON m.uid=mc.uid 
		WHERE m.uid = ' . $memberUid;
}

$victimMember = DB::fetch_first($sql);



if (!$victimMember) {

    showError(lang('plugin/zeroze007_hdx', 'no_such_member'));
}

// v1.1.1更新
$victim = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_victim') . ' WHERE uid=' . $victimMember['uid']);

// 每人每天被打劫次数限制
if ($dailyRobbedTimes > 0) {

    if ($victim['last_robbed_time'] > 0) {
        $lastRobbedDay = date('z', $victim['last_robbed_time']);
        $day = date('z', $_timenow);

        if ($lastRobbedDay == $day) {
            // 当天
            if ($victim['robbed_day_count'] + 1 > $dailyRobbedTimes) {

                showError(lang('plugin/zeroze007_hdx', 'could_not_rob_today', array('rob' => $_hdxLang['rob'], 'daily_robbed_times' => $dailyRobbedTimes)));
            }

            $victimUpdate[] = 'robbed_day_count = robbed_day_count + 1';
        } else {
            $victimUpdate[] = 'robbed_day_count = 1';
        }
    } else {
        $victimUpdate[] = 'robbed_day_count = 1';
    }
}

$victimUpdate[] = 'last_robbed_time = ' . $_timenow;
$victimUpdate[] = 'robbed_times = robbed_times + 1';



// 限制
//$robMinSw = $_hdx['rob_min_sw'];
// 是否打劫自己
if ($victimMember['uid'] == $_uid) {

    showError(lang('plugin/zeroze007_hdx', 'rob_yourself', array('rob' => $_hdxLang['rob'])));
}

// 是否足够声望
/*
  if ($robMinSw != '' && $_sw < intval($robMinSw)) {

  showError(lang('plugin/zeroze007_hdx', 'sw_not_enough_to_rob', array('rob' => $_hdxLang['rob'], 'sw_title' => $_swTitle, 'rob_min_sw' => $robMinSw)));
  } */





// 受保护用户组
$protectedGroupList = $_hdx['rob_protected_group_list'];
$protectedGroupAry = unserialize($protectedGroupList);

if (in_array($victimMember['groupid'], $protectedGroupAry)) {
    showError(lang('plugin/zeroze007_hdx', 'could_not_rob_protected_user_in_group', array('rob' => $_hdxLang['rob'])));
}

// 受保护人群
$protectedUserList = trim($_hdx['rob_protected_user_list']);
if (!empty($protectedUserList)) {
    $protectedUserAry = explode("\n", $protectedUserList);
    if (in_array($victimMember['uid'], $protectedUserAry)) {
        showError(lang('plugin/zeroze007_hdx', 'could_not_rob_protected_user', array('rob' => $_hdxLang['rob'])));
    }
}


// away hd
$victimActivity = DB::fetch_first("SELECT * FROM " . DB::table('hdx_player_activity') . " WHERE uid = '" . $victimMember['uid'] . "'");

if ($victimActivity && $victimActivity['type'] == ACTIVITY_AWAY && $victimActivity['expired_time'] > $_timenow) {

    throw new Exception(lang('plugin/zeroze007_hdx', 'could_not_rob_away_member', array('rob' => $_hdxLang['rob'], 'hd' => $_hdxLang['hd'],
            'year' => date('Y', $playerActivity['expired_time']),
            'month' => date('n', $playerActivity['expired_time']),
            'day' => date('j', $playerActivity['expired_time']),
            'hour' => date('H', $playerActivity['expired_time']),
            'minute' => date('i', $playerActivity['expired_time']))
        )
    );
}


// 低于多少钱不能被打劫
$robMinMoneyToProtect = $_hdx['rob_min_money_to_protect'];
if ($robMinMoneyToProtect != '' && $victimMember[$_moneyExtStr] < intval($robMinMoneyToProtect)) {

    showError(lang('plugin/zeroze007_hdx', 'money_too_less_to_rob', array('rob' => $_hdxLang['rob'], 'money_title' => $_moneyTitle)));
}

// 只允许打劫加入黑道的人
// 判断是否加入黑道
$victimPlayer = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_player') . ' WHERE uid=' . $victimMember['uid']);

if ($victimPlayer) {
    // 打劫加入黑道的玩家所需的体力
    if ($robPlayerSta != '' && $robPlayerSta != '0') {
        $robPlayerSta = getRandomNumber($robPlayerSta);
        if ($robPlayerSta > 0 && $_sta < $robPlayerSta) {

            showError(lang('plugin/zeroze007_hdx', 'sta_not_enough', array('rob' => $_hdxLang['rob'], 'hdx' => $_hdxLang['hdx'], 'sta_title' => $_staTitle, 'rob_player_sta' => $robPlayerSta)));
        }
        $requiredSta = $robPlayerSta;
    } else {
        $requiredSta = 0;
    }
} else {

    // 打劫论坛会员
    if (!$_hdx['rob_anyone']) {

        $robNotLoginMemberTime = floatval($_hdx['rob_not_login_member_time']) * 3600;
        $lastVisit = DB::result_first('SELECT lastvisit FROM ' . DB::table('common_member_status') . ' WHERE uid=' . $victimMember['uid']);

        if ($lastVisit > 0 && ($_timenow - $lastVisit) > $robNotLoginMemberTime && $robNotLoginMemberTime > 0) {
            // 未登录时间是否超过设定的时间，如果是则打劫
        } else {
            // 不允许打劫论坛会员


            showError(lang('plugin/zeroze007_hdx', 'could_not_robbed_without_in', array('hdx' => $_hdxLang['hdx'], 'rob' => $_hdxLang['rob'])));
        }
    }


    // 打劫论坛会员，所需体力
    if ($robMemberSta != '' && $robMemberSta != '0') {
        $robMemberSta = getRandomNumber($robMemberSta);
        if ($robMemberSta > 0 && $_sta < $robMemberSta) {

            showError(lang('plugin/zeroze007_hdx', 'sta_not_enough_to_rob_member', array('sta_title' => $_staTitle, 'rob' => $_hdxLang['rob'], 'hdx' => $_hdxLang['hdx'], 'rob_member_sta' => $robMemberSta)));
        }
        $requiredSta = $robMemberSta;
    } else {
        $requiredSta = 0;
    }
}



// 是否允许打劫坐牢的人
if ($victimPlayer && $victimPlayer['out_jail_time'] > $_timenow && $victimPlayer['out_jail_time'] > 0 && !$_hdx['rob_allow_rob_prisoner']) {
    showError(lang('plugin/zeroze007_hdx', 'could_not_rob_prisoner', array('rob' => $_hdxLang['rob'])));
}


// 添加 victim 资料
DB::query("INSERT INTO " . DB::table('hdx_victim') . " 
	(uid, last_robbed_time, robbed_times, robbed_day_count) 
	VALUES 
	(" . $victimMember['uid'] . "," . $_timenow . ", 1,1)  ON DUPLICATE KEY 
	UPDATE " . implode(',', $victimUpdate));

// 打劫成功率
$robRate = intval($_hdx['rob_rate']);
$robSuccessSw = $_hdx['rob_success_sw'];
$robFailSw = $_hdx['rob_fail_sw'];

// 武器附加成功率
if ($_weapon['id'] > 0 && $_weapon['durability'] > 0) {
    $weaponAddRate = getRandomNumber($_weapon['rate']);
    $robRate = $robRate + $weaponAddRate;


    // 耗损率
    $weaponLossRate = getRandomNumber($_weapon['d_loss_rate']);

    if ($_weapon['durability'] - $weaponLossRate <= 0) {
        $weaponDurability = 0;
    } else {
        $weaponDurability = $_weapon['durability'] - $weaponLossRate;
    }
    $msgAry[] = lang('plugin/zeroze007_hdx', 'weapon_add_rate', array('weapon' => $_weapon['name'], 'rate' => $weaponAddRate));
    DB::query('UPDATE ' . DB::table('hdx_player_item') . ' SET durability=' . intval($weaponDurability) . ' WHERE uid=' . $_uid . ' AND id=' . intval($_weapon['id']));
}


// 对方是否有防具
if ($victimPlayer && $victimPlayer['armor_id'] > 0) {
    $armor = DB::fetch_first('SELECT d_loss_rate,name,rate,pi.durability FROM ' . DB::table('hdx_shop_item') . ' si,' . DB::table('hdx_player_item') . ' pi WHERE pi.uid=' . intval($victimPlayer['uid']) . ' AND si.available = 1 AND pi.item_id=si.id AND pi.id=' . intval($victimPlayer['armor_id']));

    if ($armor && $armor['durability'] > 0) {
        // 护具减少成功率
        $armorDeductRate = getRandomNumber($armor['rate']);
        $robRate = $robRate - $armorDeductRate;


        // 耗损率
        $armorLossRate = getRandomNumber($armor['d_loss_rate']);

        if ($armor['durability'] - $armorLossRate <= 0) {
            $armorDurability = 0;
        } else {
            $armorDurability = $armor['durability'] - $armorLossRate;
        }

        $msgAry[] = lang('plugin/zeroze007_hdx', 'armor_deduct_rate', array('victim' => $victimMember['username'], 'armor' => $armor['name'], 'rate' => $armorDeductRate));

        DB::query('UPDATE ' . DB::table('hdx_player_item') . ' SET durability=' . intval($armorDurability) . ' WHERE uid=' . $victimPlayer['uid'] . ' AND id=' . intval($victimPlayer['armor_id']));
    }
}

// guard

if ($victimPlayer) {
    $victimGuard = DB::fetch_first("SELECT g.rate,m.username guard_name FROM " . DB::table('hdx_guard') . " g, " . DB::table('common_member') . " m  WHERE m.uid=g.uid AND g.expired_time > " . $_timenow . " AND g.employer_uid='" . $victimPlayer['uid'] . "'");

    if ($victimGuard) {
        $victimGuardRate = getRandomNumber($victimGuard['rate']);
        $msgAry[] = lang('plugin/zeroze007_hdx', 'guard_deduct_rate', array('victim' => $victimMember['username'], 'guard' => $victimGuard['guard_name'], 'rate' => $victimGuardRate));
        $robRate = $robRate - $victimGuardRate;
    }

    $guard = DB::fetch_first("SELECT uid FROM " . DB::table('hdx_guard') . " g WHERE g.expired_time > " . $_timenow . " AND g.employer_uid > 0 AND g.uid='" . $victimPlayer['uid'] . "'");

    if ($guard) {
        $victimToBeGuardRate = getRandomNumber($_hdx['guard_to_be_robbed_rate']);
        //$msgAry[] = lang('plugin/zeroze007_hdx', 'guard_deduct_rate', array('victim' => $victimMember['username'], 'guard' => $victimGuard['guard_name'], 'rate' => $victimGuardRate));
        $robRate = $robRate + $victimToBeGuardRate;
    }
}



// add exp
$addExp = getRandomNumber($_hdx['rob_exp']);

if (rand(0, 100) <= $robRate) {
    $result = 1;

    // 获得对方金钱
    $victimMoney = $victimMember[$_moneyExtStr];

    // 已加入黑道，取得黑道打劫金钱率
    if ($victimPlayer) {
        $robMemberMoneyRate = $_hdx['rob_player_money_rate'];
    } else {
        $robMemberMoneyRate = $_hdx['rob_member_money_rate'];
    }

    $robAmount = getRandomNumber($robMemberMoneyRate, $victimMoney);



    // add exp
    $addSuccessExp = getRandomNumber($_hdx['rob_success_exp']);
    if ($addSuccessExp > 0) {
        // update db
        $addExp = $addExp + $addSuccessExp;
    }




    // 没钱
    if ($robAmount == 0) {

        $msg = lang('plugin/zeroze007_hdx', 'rob_success_without_money', array('rob' => $_hdxLang['rob']));

        $log['result'] = lang('plugin/zeroze007_hdx', 'rob_success_without_money_log');
    } else {

        $msg = lang('plugin/zeroze007_hdx', 'rob_success_with_money', array('rob' => $_hdxLang['rob'], 'rob_amount' => $robAmount, 'money_title' => $_moneyTitle));

        $log['result'] = lang('plugin/zeroze007_hdx', 'rob_success_with_money_log', array('rob_amount' => $robAmount, 'money_title' => $_moneyTitle));


        // db
        $robberMemberUpdate[] = $_moneyExtStr . '=' . $_moneyExtStr . '+' . $robAmount;

        // victim 损失金钱
        $victimMemberUpdate[] = $_moneyExtStr . '=' . $_moneyExtStr . '-' . $robAmount;


        //$victimUpdate[] = 'robbed_money_amount = robbed_money_amount + '. $robAmount;

        // update victim 资料
        DB::query("UPDATE " . DB::table('hdx_victim') . " SET robbed_money_amount = robbed_money_amount + '" . $robAmount . "' WHERE uid='" . $victimMember['uid'] . "'");
    }

    // 增加声望
    if ($robSuccessSw == '' || $robSuccessSw == '0') {
        $robSuccessSw = 0;
    } else {
        $robSuccessSw = getRandomNumber($robSuccessSw);
    }

    if ($robSuccessSw > 0) {

        $msgAry[] = lang('plugin/zeroze007_hdx', 'sw_increase', array('rob_success_sw' => $robSuccessSw, 'sw_title' => $_swTitle));
        $log['ext']['sw'] = $_swTitle . "+" . $robSuccessSw;

        // db
        updateSw($_uid, $robSuccessSw);
        //$robberMemberUpdate[] = 'sw = sw +' . $robSuccessSw;
    }
    // update db
    $robberPlayerUpdate[] = 'rob_money_amount = rob_money_amount +' . $robAmount;
    $robberPlayerUpdate[] = 'rob_success_times = rob_success_times + 1';
} else {

    // 坐牢几率
    $jailRate = intval($_hdx['jail_rate']);

    // 打劫失败减少金钱
    if ($_hdx['rob_fail_lost_money_rate'] != '0' && $_hdx['rob_fail_lost_money_rate'] != '') {
        $lostMoney = round(getRandomNumber($_hdx['rob_fail_lost_money_rate'], $_money));


        $msgAry[] = lang('plugin/zeroze007_hdx', 'money_reduce', array('amount' => $lostMoney, 'money_title' => $_moneyTitle));

        // 退回给受害者
        if (intval($_hdx['rob_fail_lost_money_rate']) > 0) {
            $backMoneyAmount = intval($lostMoney * (intval($_hdx['rob_fail_back_money_rate']) / 100));
        }

        if ($backMoneyAmount > 0) {
            $victimMemberUpdate[] = $_moneyExtStr . '=' . $_moneyExtStr . '+\'' . $backMoneyAmount . '\'';
            $msgAry[] = lang('plugin/zeroze007_hdx', 'money_back_to_victim', array('amount' => $backMoneyAmount, 'money_title' => $_moneyTitle));
        }

        // update db
        $robberMemberUpdate[] = $_moneyExtStr . '=' . $_moneyExtStr . '-' . $lostMoney;
        $log['ext']['money'] = "{$_moneyTitle}-{$lostMoney}";
    }

    // 减少声望
    if ($robFailSw == '' || $robFailSw == '0') {
        $robFailSw = 0;
    } else {
        $robFailSw = getRandomNumber($robFailSw);
    }
    if ($robFailSw > 0) {

        $msgAry[] = lang('plugin/zeroze007_hdx', 'sw_reduce', array('rob_fail_sw' => $robFailSw, 'sw_title' => $_swTitle));
        // update db
        updateSw($_uid, (0 - abs($robFailSw)));
        //$robberMemberUpdate[] = 'sw=sw-' . $robFailSw;

        $log['ext']['sw'] = $_swTitle . "-" . $robFailSw;
    }

    // 是否坐牢	
    if (rand(0, 100) <= $jailRate) {

        $outJailTime = $_timenow + intval($_hdx['jail_time']) * 60;

        // 坐牢
        $robberPlayerUpdate[] = 'jail_times = jail_times + 1, in_jail_time =' . $_timenow . ', out_jail_time =' . $outJailTime;

        $url = 'plugin.php?id=zeroze007_hdx&op=jail';
        $redirect = 1;


        $msg = lang('plugin/zeroze007_hdx', 'send_to_jail', array('month' => date('n', $outJailTime), 'day' => date('j', $outJailTime), 'hour' => date('H', $outJailTime), 'minute' => date('i', $outJailTime)));

        // log

        $log['result'] = lang('plugin/zeroze007_hdx', 'send_to_jail_log');
    } else {
        // 逃跑了

        $msg = lang('plugin/zeroze007_hdx', 'escape', array('rob' => $_hdxLang['rob']));

        // log

        $log['result'] = lang('plugin/zeroze007_hdx', 'escape_log');
    }
}

if ($addExp > 0) {
    $robberMemberUpdate[] = "exp=exp+'" . intval($addExp) . "'";
    $msgAry[] = lang('plugin/zeroze007_hdx', 'add_exp', array('amount' => $addExp));
    if (chkUpgrade($_exp, $_next_level_required_exp, $addExp)) {
        $robberMemberUpdate[] = "level=level+'1'";
        $nextLevelReqExp = nextLevelExp(($_level + 1), $_hdx['level_rate']);
        $playerTitle = getPlayerTitle($_level + 1);
        if ($playerTitle != $_title) {
            $robberMemberUpdate[] = "title='" . escape($playerTitle) . "'";
        }
        $msgAry[] = lang('plugin/zeroze007_hdx', 'upgraded', array('amount' => $nextLevelReqExp));
    }
}


// 减少体力
if ($requiredSta > 0) {
    $msgAry[] = lang('plugin/zeroze007_hdx', 'sta_reduce', array('required_sta' => $requiredSta, 'sta_title' => $_staTitle));
    // update db
    //$robberMemberUpdate[] = 'sta=sta-' . $requiredSta;
    updateSta($_uid, (0 - abs($requiredSta)));
}








$msg = $msg . '<br><br>' . ($msgAry ? implode('<br>', $msgAry) : '');

// robber member
if ($robberMemberUpdate) {

    DB::query('UPDATE ' . DB::table('common_member_count') . ' mc,' . DB::table('hdx_player') . ' p SET ' . implode(',', $robberMemberUpdate) . ' WHERE mc.uid=p.uid AND mc.uid=' . $_uid);
}

// robber player
$robberPlayerUpdate[] = 'rob_times = rob_times + 1, last_rob_time = ' . $_timenow;
if ($robberPlayerUpdate) {
    DB::query('UPDATE ' . DB::table('hdx_player') . ' SET ' . implode(",", $robberPlayerUpdate) . ' WHERE uid=' . $_uid);
}

// victim member
if ($victimMemberUpdate) {
    DB::query('UPDATE ' . DB::table('common_member_count') . ' SET ' . implode(",", $victimMemberUpdate) . ' WHERE uid=' . $victimMember['uid']);
}

// robber player
if ($victimPlayerUpdate && $victimPlayer) {
    DB::query('UPDATE ' . DB::table('hdx_player') . ' SET ' . implode(",", $victimPlayerUpdate) . ' WHERE uid=' . $victimPlayer['uid']);
}

// log
$log['who_uid'] = $_uid;
$log['to_uid'] = $victimMember['uid'];
$log['created_at'] = $_timenow;

if (count($log['ext']) > 0) {
    $logExt = implode("，", $log['ext']);
}

$log['msg'] = "{{" . $_player['username'] . "}}" . dhtmlspecialchars($_hdxLang['rob']) . "{{" . $victimMember['username'] . "}}" . $log['result'] . $logExt;

$data = array(
    'who_uid' => $_uid,
    'to_uid' => $victimMember['uid'],
    'created_at' => $_timenow,
    'msg' => $log['msg']
);

DB::insert('hdx_log', $data);

// msg
$data = array(
    'from_uid' => 0,
    'to_uid' => $victimMember['uid'],
    'created_at' => $_timenow,
    'content' => msgFilter($log['msg'], $victimMember['username'], lang('plugin/zeroze007_hdx', 'i'))
);
DB::insert('hdx_msg', $data);

// 个人设置
$robbedAlert = DB::result_first("SELECT svalue FROM " . DB::table('hdx_player_setting') . " WHERE uid=" . $victimMember['uid'] . " AND skey='robbed_alert'");

if (intval($robbedAlert) == 1 || !$victimPlayer) {



    $subject = lang('plugin/zeroze007_hdx', 'you_robbed', array('player_username' => $_player[username], 'hdx' => $_hdxLang['hdx'], 'rob' => $_hdxLang['rob']));
    $message = msgFilter($log['msg'], $victimMember['username'], lang('plugin/zeroze007_hdx', 'i'));

    // DZ提醒
    notification_add($victimMember['uid'], 'system', 'system_notice', array(
        'subject' => $subject,
        'message' => $message
        ), 1);
}



// 输出
showMsg($msg, $result, array(
    'url' => $url
));
?>
