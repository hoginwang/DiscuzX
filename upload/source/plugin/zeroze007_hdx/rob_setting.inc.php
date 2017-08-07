<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

// 后台插件设置
loadcache('plugin');
$_setting = $_G['cache']['plugin']['zeroze007_hdx'];

// 调试开关
if ($_setting['debug']) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', '1');
}



if (!submitcheck('settingsubmit', 1)) {


// 黑道设置
    $hdx = array();
    $query = DB::query("SELECT * FROM " . DB::table('hdx_setting') . " WHERE skey LIKE 'rob_%'");

    while ($setting = DB::fetch($query)) {
        $key = $setting['skey'];
        $hdx[$key] = $setting['svalue'];
    }

    if (empty($hdx)) {
        $hdx = array(
            'rob_anyone' => 0,
            'rob_in_thread' => 1,
            'rob_not_login_member_time' => 0,
            'rob_player_sta' => 5,
            'rob_player_money_rate' => '5%,10%',
            'rob_member_sta' => 10,
            'rob_member_money_rate' => '1%,5%',
            'rob_fail_lost_money_rate' => '1%,2%',
            'rob_fail_back_money_rate' => '50',
            'rob_interval' => 10,
            'rob_rate' => 30,
            'rob_success_sw' => '1,5',
            'rob_exp' => '1,5',
            'rob_success_exp' => '1,5',
            'rob_fail_sw' => '1,2',
            //  'rob_min_sw' => 10,
            'rob_day_max' => 10,
            'rob_daily_robbed_times' => 3,
            'rob_min_money_to_protect' => 100,
            'rob_allow_rob_prisoner' => 0
        );
    }

    showtips(lang('plugin/zeroze007_hdx', 'setting_rob_tips'));

    showformheader('plugins&operation=config&do=' . $pluginid . '&identifier=zeroze007_hdx&pmod=rob_setting');

    showtableheader('');


    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_anyone'), 'hdx[rob_anyone]', $hdx['rob_anyone'], 'radio', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_anyone_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_in_thread'), 'hdx[rob_in_thread]', $hdx['rob_in_thread'], 'radio', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_in_thread_desc'));


    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_not_login_member_time'), 'hdx[rob_not_login_member_time]', $hdx['rob_not_login_member_time'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_not_login_member_time_desc'));


    showtitle(lang('plugin/zeroze007_hdx', 'setting_rob_player'));



    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_player_sta'), 'hdx[rob_player_sta]', $hdx['rob_player_sta'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_player_sta_desc'));


    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_player_money_rate'), 'hdx[rob_player_money_rate]', $hdx['rob_player_money_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_player_money_rate_desc'));
    showtitle(lang('plugin/zeroze007_hdx', 'setting_rob_member'));



    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_member_sta'), 'hdx[rob_member_sta]', $hdx['rob_member_sta'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_member_sta_desc'));


    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_member_money_rate'), 'hdx[rob_member_money_rate]', $hdx['rob_member_money_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_member_money_rate_desc'));



    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_interval'), 'hdx[rob_interval]', $hdx['rob_interval'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_interval_desc'));


    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_rate'), 'hdx[rob_rate]', $hdx['rob_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_rate_desc'));



    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_exp'), 'hdx[rob_exp]', $hdx['rob_exp'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_exp_desc'));





    showtitle(lang('plugin/zeroze007_hdx', 'setting_rob_success'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_success_sw'), 'hdx[rob_success_sw]', $hdx['rob_success_sw'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_success_sw_desc'));
    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_success_exp'), 'hdx[rob_success_exp]', $hdx['rob_success_exp'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_success_exp_desc'));

    showtitle(lang('plugin/zeroze007_hdx', 'setting_rob_fail'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_fail_sw'), 'hdx[rob_fail_sw]', $hdx['rob_fail_sw'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_fail_sw_desc'));
    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_fail_lost_money_rate'), 'hdx[rob_fail_lost_money_rate]', $hdx['rob_fail_lost_money_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_fail_lost_money_rate_desc'));

    
    
    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_fail_back_money_rate'), 'hdx[rob_fail_back_money_rate]', $hdx['rob_fail_back_money_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_fail_back_money_rate_desc'));

    

    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_fail_money_to_victim'), 'hdx[rob_fail_money_to_vicim]', $hdx['rob_fail_money_to_vicim'], 'radio', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_fail_money_to_victim_desc'));


    showtitle(lang('plugin/zeroze007_hdx', 'setting_rob_limit'));


    // showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_min_sw'), 'hdx[rob_min_sw]', $hdx['rob_min_sw'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_min_sw_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_day_max'), 'hdx[rob_day_max]', $hdx['rob_day_max'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_day_max_desc'));


    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_daily_robbed_times'), 'hdx[rob_daily_robbed_times]', $hdx['rob_daily_robbed_times'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_daily_robbed_times_desc'));



    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_min_money_to_protect'), 'hdx[rob_min_money_to_protect]', $hdx['rob_min_money_to_protect'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_min_money_to_protect_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_allow_rob_prisoner'), 'hdx[rob_allow_rob_prisoner]', $hdx['rob_allow_rob_prisoner'], 'radio', '', 0, lang('plugin/zeroze007_hdx', 'setting_allow_rob_prisoner_desc'));



    showsetting(lang('plugin/zeroze007_hdx', 'setting_rob_suggest_list'), 'hdx[rob_suggest_list]', $hdx['rob_suggest_list'], 'textarea', '', 0, lang('plugin/zeroze007_hdx', 'setting_rob_suggest_list_desc'));



    $protectedGroupAry = unserialize($hdx['rob_protected_group_list']);

    $groupselect = array();

    $query = DB::query("SELECT * FROM " . DB::table('common_usergroup') . " ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");

    while ($group = DB::fetch($query)) {

        $groupselect[$group['type']] .= '<option value="' . $group['groupid'] . '"' . (@in_array($group['groupid'], $protectedGroupAry) ? ' selected' : '') . '>' . $group['grouptitle'] . '</option>';
    }




    $usergroups = '<select name="hdx[rob_protected_group_list][]" size="10" multiple="multiple"><option value=""' . (@in_array('', $protectedGroupAry) ? ' selected' : '') . '>' . cplang('plugins_empty') . '</option>' .
            '<optgroup label="' . $lang['usergroups_member'] . '">' . $groupselect['member'] . '</optgroup>' .
            ($groupselect['special'] ? '<optgroup label="' . $lang['usergroups_special'] . '">' . $groupselect['special'] . '</optgroup>' : '') .
            ($groupselect['specialadmin'] ? '<optgroup label="' . $lang['usergroups_specialadmin'] . '">' . $groupselect['specialadmin'] . '</optgroup>' : '') .
            '<optgroup label="' . $lang['usergroups_system'] . '">' . $groupselect['system'] . '</optgroup></select>';



    showsetting(lang('plugin/zeroze007_hdx', 'setting_protected_group_list'), '', '', $usergroups, '', 0, lang('plugin/zeroze007_hdx', 'setting_protected_group_list_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_protected_user_list'), 'hdx[rob_protected_user_list]', $hdx['rob_protected_user_list'], 'textarea', '', 0, lang('plugin/zeroze007_hdx', 'setting_protected_user_list_desc'));

    showsubmit('settingsubmit');
    showtablefooter();
    showformfooter();
} else {

    require DISCUZ_ROOT . './source/plugin/zeroze007_hdx/include/function.inc.php';

    $hdx = $_POST['hdx'];


    if (validValue($hdx['rob_player_sta'], true) == false) {

        cpmsg(lang('plugin/zeroze007_hdx', 'rob_player_sta_error'), '', 'error');
    }


    if (validValue($hdx['rob_player_money_rate'], true, true) == false) {
        cpmsg(lang('plugin/zeroze007_hdx', 'rob_player_money_rate_error'), '', 'error');
    }

    if ($hdx['rob_anyone']) {
        if (validValue($hdx['rob_member_sta'], true) == false) {
            cpmsg(lang('plugin/zeroze007_hdx', 'rob_member_sta_error'), '', 'error');
        }

        if (validValue($hdx['rob_member_money_rate'], true, true) == false) {
            cpmsg(lang('plugin/zeroze007_hdx', 'rob_member_money_rate_error'), '', 'error');
        }
    }

    if (validValue($hdx['rob_fail_lost_money_rate'], true, true) == false) {
        cpmsg(lang('plugin/zeroze007_hdx', 'rob_fail_lost_money_rate_error'), '', 'error');
    }

    if (validValue($hdx['rob_success_sw'], true) == false) {
        cpmsg(lang('plugin/zeroze007_hdx', 'rob_success_sw_error'), '', 'error');
    }
    if (validValue($hdx['rob_fail_sw'], true) == false) {
        cpmsg(lang('plugin/zeroze007_hdx', 'rob_fail_sw_error'), '', 'error');
    }

    // 进库前过滤
    $hdx['rob_interval'] = intval($hdx['rob_interval']);
    $hdx['rob_rate'] = intval($hdx['rob_rate']);


    if ($hdx['rob_protected_group_list'][0] == '') {
        $hdx['rob_protected_group_list'] = array();
        $hdx['rob_protected_group_list'][0] = '';
    }

    $hdx['rob_protected_group_list'] = serialize($hdx['rob_protected_group_list']);


    foreach ($hdx as $key => $val) {
        $key = escape($key);
        $val = escape($val);
        DB::query("INSERT INTO " . DB::table('hdx_setting') . "(skey, svalue) VALUES ('" . $key . "', '" . $val . "') ON DUPLICATE KEY UPDATE svalue = '" . $val . "'");
    }
    cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=plugins&operation=config&do=' . $pluginid . '&identifier=zeroze007_hdx&pmod=rob_setting', 'succeed');
}
?>
