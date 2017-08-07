<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if (!submitcheck('guardsubmit')) {
    showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
}


$result = 1;

if ($_POST['do_join'] == 'yes') {



    $protectedGroupList = $_hdx['guard_allow_join_group_list'];
    $protectedGroupAry = unserialize($protectedGroupList);

    if (!in_array($_groupid, $protectedGroupAry)) {
        showError(lang('plugin/zeroze007_hdx', 'group_could_not_join_guard'));
    }




    if (mb_strlen($_POST['description'], 'utf-8') > 30) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'guard_description_too_long'));
    }

    $description = iconv('utf-8', $_charset, $_POST['description']);

    // if join before
    $guard = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_guard') . ' WHERE uid = \'' . $_uid . '\'');

    if ($guard && $guard['expired_time'] - $_timenow >= 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'guard_has_been_hired'));
    }


    if ($guard && $guard['expired_time'] - $_timenow < 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'already_join_guard'));
    }


    if ($_level < intval($_hdx['guard_join_min_level']) && intval($_hdx['guard_join_min_level']) > 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'level_not_reach_to_join_guard', array('amount' => intval($_hdx['guard_join_min_level']))));
    }

    $guardMaxTime = max(1, intval($_hdx['guard_max_time']));
    $guardMaxSalary = max(1, intval($_hdx['guard_max_salary']));
    $guardMinSalary = max(1, intval($_hdx['guard_min_salary']));
    $guardJoinMinLevel = intval($_hdx['guard_join_min_level']);

    if ($guardMinSalary > $guardMaxSalary) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'guard_salary_range_error'));
    }

    $guardSalaryRate = floatval($_hdx['guard_salary_rate']);
    if ($guardSalaryRate > 0) {
        $guardMinSalary = min(intval(($_level == 0 ? 1 : $_level) * $guardSalaryRate), $guardMinSalary);
        $guardMaxSalary = min(intval(($_level == 0 ? 1 : $_level) * $guardSalaryRate), $guardMaxSalary);
    }


    $hourSalary = $_POST['hour_salary'];
    if (intval($hourSalary) > $guardMaxSalary) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'excess_max_guard_salary'));
    }

    $protectTime = $_POST['protect_time'];
    if (intval($protectTime) > $guardMaxTime) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'excess_max_guard_time'));
    }


    $guardRate = $_hdx['guard_rate'];
    $userGuardRate = intval($_level * $_hdx['guard_rate']);

    $guardMinRate = intval($_hdx['guard_min_rate']);
    $guardMaxRate = intval($_hdx['guard_max_rate']);

    if ($guardMinRate >= $guardMaxRate) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'setting_guard_min_max_rate_error'));
    }

    if ($userGuardRate <= $guardMinRate) {
        $userGuardMinRate = $userGuardRate;
        $userGuardMaxRate = $guardMinRate;
    } else if ($userGuardRate >= $guardMaxRate) {
        $userGuardMinRate = $guardMinRate;
        $userGuardMaxRate = $guardMaxRate;
    } else {
        $userGuardMinRate = $guardMinRate;
        $userGuardMaxRate = $userGuardRate;
    }

    if ($userGuardMinRate == $userGuardMaxRate) {
        $finalRate = $userGuardMaxRate;
    } else {
        $finalRate = $userGuardMinRate . ',' . $userGuardMaxRate;
    }

    DB::query("REPLACE INTO " . DB::table('hdx_guard') . " (uid, price, rate, protect_time,description) VALUES ('" . $_uid . "','" . intval($hourSalary) . "', '" . $finalRate . "','" . $protectTime . "','" . escape($description) . "')");

    $result = 1;
    $msg = lang('plugin/zeroze007_hdx', 'join_guard_success');

    $url = 'plugin.php?id=zeroze007_hdx&op=guard';
} else if ($_POST['do_quit'] == 'yes') {


    // check before quit
    $guard = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_guard') . ' WHERE uid = \'' . $_uid . '\'');

    if ($guard && $guard['expired_time'] - $_timenow >= 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'hired_could_not_quit'));
    }

    DB::query("DELETE FROM " . DB::table('hdx_guard') . " WHERE uid='" . $_uid . "'");

    $result = 1;
    $msg = lang('plugin/zeroze007_hdx', 'quit_guard_success');

    $url = 'plugin.php?id=zeroze007_hdx&op=guard';
} else if ($_POST['do_fire'] == 'yes') {
    DB::query("UPDATE " . DB::table('hdx_guard') . " SET employer_uid = 0,expired_time = 0 WHERE employer_uid ='" . $_uid . "'");


    $result = 1;
    $msg = lang('plugin/zeroze007_hdx', 'fire_guard_success');


    $url = 'plugin.php?id=zeroze007_hdx&op=guard';
} else if ($_POST['do_hire'] == 'yes') {



    $protectedGroupList = $_hdx['guard_allow_hire_group_list'];
    $protectedGroupAry = unserialize($protectedGroupList);

    if (!in_array($_groupid, $protectedGroupAry)) {
        showError(lang('plugin/zeroze007_hdx', 'group_could_not_hire_guard'));
    }



    $guardUid = intval($_POST['guard_uid']);

    if ($_uid == $guardUid) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'could_not_hire_self'));
    }
    $guard = DB::fetch_first("SELECT g.*,m.username guard_name FROM " . DB::table('hdx_guard') . " g, " . DB::table('common_member') . " m  WHERE m.uid=g.uid AND g.uid='" . $guardUid . "'");

    if (!$guard) {
        showError(lang('plugin/zeroze007_hdx', 'no_such_guard'));
    }

    if (intval($guard['protect_time']) <= 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'guard_protect_hour_error'));
    }






// 限制
    $price = intval($guard['price'] * $guard['protect_time']);

    if ($_money < $price) {
        // 是否够钱

        showError(lang('plugin/zeroze007_hdx', 'not_enough_money', array('money_title' => $_moneyTitle, 'amount' => $price)));
    }


    $msgAry[] = lang('plugin/zeroze007_hdx', 'money_reduce', array('money_title' => $_moneyTitle, 'amount' => $price));


    $msg = lang('plugin/zeroze007_hdx', 'employ_success');

// update array
    $update = array();

// update db
    $update[] = $_moneyExtStr . '=' . $_moneyExtStr . '-' . $price;

    DB::query('UPDATE ' . DB::table('common_member_count') . ' mc,' . DB::table('hdx_player') . ' p SET ' . implode(',', $update) . ' WHERE mc.uid=p.uid AND mc.uid=' . $_uid);

    $msg = $msg . '<br><br>' . implode('<br>', $msgAry);

    $url = 'plugin.php?id=zeroze007_hdx&op=guard';

    $expiredTime = $_timenow + intval($guard['protect_time'] * 3600);

    DB::query("UPDATE " . DB::table('hdx_guard') . " SET employer_uid = 0,expired_time = 0 WHERE employer_uid ='" . $_uid . "'");
    $data = array(
        'employer_uid' => $_uid,
        'expired_time' => $expiredTime
    );
    DB::update('hdx_guard', $data, "uid='" . $guardUid . "'");

    $expiredTimeStr = lang('plugin/zeroze007_hdx', 'mdhm', array(
        'month' => date('n', $playerGuard['expired_time']),
        'day' => date('j', $playerGuard['expired_time']),
        'hour' => date('H', $playerGuard['expired_time']),
        'minute' => date('i', $playerGuard['expired_time'])));

    $subject = lang('plugin/zeroze007_hdx', 'you_have_been_hired_subject', array('employer_name' => $_player[username], 'hdx' => $_hdxLang['hdx']));
    $message = lang('plugin/zeroze007_hdx', 'you_have_been_hired_body', array('employer_name' => $_player[username], 'rob' => $_hdxLang['rob'], 'money_title' => $_moneyTitle, 'amount' => $price, 'expired_time' => $expiredTimeStr));


    // DZ提醒
    notification_add($guardUid, 'system', 'system_notice', array(
        'subject' => $subject,
        'message' => $message
        ), 1);


    $log['msg'] = "{{" . $_player['username'] . "}}" . lang('plugin/zeroze007_hdx', 'hire') . "{{" . $guard['guard_name'] . "}}" . lang('plugin/zeroze007_hdx', 'as_guard');



    $data = array(
        'who_uid' => $_uid,
        'to_uid' => $guardUid,
        'created_at' => $_timenow,
        'msg' => $log['msg']
    );

    DB::insert('hdx_log', $data);
}

// 输出
showMsg($msg, $result, array(
    'url' => $url
));
?>
