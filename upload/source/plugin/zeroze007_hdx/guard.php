<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}


$url = 'plugin.php?id=zeroze007_hdx&op=guard';

$subop = $_GET['subop'];
if (empty($subop)) {

    $guard = DB::fetch_first("SELECT g.*,m.username employer FROM " . DB::table('hdx_guard') . " g LEFT JOIN " . DB::table('common_member') . " m ON m.uid = g.employer_uid WHERE g.uid = '" . $_uid . "'");

    if ($guard && $guard['uid'] > 0) {


        if (strpos($guard['rate'], ',') === false) {
            $guard['rate'] = $guard['rate'] . '%';
        } else {
            list($low, $high) = explode(',', $guard['rate']);
            $guard['rate'] = $low . '% ~ ' . $high . '%';
        }


        if ($guard['employer_uid'] > 0 && $guard['expired_time'] > $_timenow) {
            $guard['end_time'] = lang('plugin/zeroze007_hdx', 'mdhm', array(
                'month' => date('m', $guard['expired_time']),
                'day' => date('d', $guard['expired_time']),
                'hour' => date('H', $guard['expired_time']),
                'minute' => date('m', $guard['expired_time'])));
        }


        if ($guard['expired_time'] - $_timenow >= 0) {
            $guard['available'] = 0;
        } else {
            $guard['available'] = 1;
        }
    } else {

        if (empty($_GET['disp_type'])) {
            $dispType = 2;
        } else {
            $dispType = intval($_GET['disp_type']);
        }
        if ($playerGuard['uid'] > 0) {
            if (strpos($playerGuard['rate'], ',') === false) {
                $playerGuard['rate'] = $playerGuard['rate'] . '%';
            } else {
                list($low, $high) = explode(',', $playerGuard['rate']);
                $playerGuard['rate'] = $low . '% ~ ' . $high . '%';
            }


            $playerGuard['protect_time_to'] = lang('plugin/zeroze007_hdx', 'protect_time_to', array(
                'month' => date('n', $playerGuard['expired_time']),
                'day' => date('j', $playerGuard['expired_time']),
                'hour' => date('H', $playerGuard['expired_time']),
                'minute' => date('i', $playerGuard['expired_time'])));
        }

        $guards = array();

        $query = DB::query('SELECT g.*,m.username guard_name FROM ' . DB::table('hdx_guard') . ' g,' . DB::table('common_member') . ' m WHERE m.uid=g.uid AND g.uid != \'' . $_uid . '\' AND expired_time < ' . $_timenow . ' LIMIT ' . $_start . ',' . $_perpage);

        while ($y = DB::fetch($query)) {

            if (strpos($y['rate'], ',') === false) {
                $y['rate'] = $y['rate'] . '%';
            } else {
                list($low, $high) = explode(',', $y['rate']);
                $y['rate'] = $low . '% ~ ' . $high . '%';
            }

            if (mb_strlen($y['description'], $_charset) > 22) {
                $y['description'] = mb_substr($y['description'], 0, 22, $_charset) . '...';
            }


            $y = escape($y, 'html');
            $guards[] = $y;
        }

        $count = DB::result_first('SELECT COUNT(*) FROM ' . DB::table('hdx_guard') . ' g,' . DB::table('common_member') . ' m WHERE m.uid=g.uid  AND g.uid != \'' . $_uid . '\' AND expired_time < ' . $_timenow);

        $multipage = multi($count, $_perpage, $_page, $url . '&disp_type=' . $dispType);
    }
} elseif ($subop == 'join') {

    if (!submitcheck('guardsubmit', 1)) {
        showError(lang('plugin/zeroze007_hdx', 'submit_invalid'));
    }

    $protectedGroupList = $_hdx['guard_allow_join_group_list'];
    $protectedGroupAry = unserialize($protectedGroupList);

    if (!in_array($_groupid, $protectedGroupAry)) {
        showError(lang('plugin/zeroze007_hdx', 'group_could_not_join_guard'));
    }


    // if hire people
    $employerUid = DB::result_first('SELECT uid FROM ' . DB::table('hdx_guard') . ' WHERE employer_uid = \'' . $_uid . '\' AND expired_time >' . $_timenow);

    if ($employerUid > 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'hired_guard_could_not_join'));
    }

    // if join before
    $guard = DB::fetch_first('SELECT * FROM ' . DB::table('hdx_guard') . ' WHERE uid = \'' . $_uid . '\'');




    if ($guard && $guard['expired_time'] - $_timenow >= 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'guard_has_been_hired'));
    }


    if ($guard && $guard['expired_time'] - $_timenow < 0) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'already_join_guard'));
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
} else {
    throw new Exception('Invliad Action.');
}
?>
