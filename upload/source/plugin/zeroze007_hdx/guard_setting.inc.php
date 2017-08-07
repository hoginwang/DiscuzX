<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

// 后台插件设置
loadcache('plugin');
$_setting = $_G['cache']['plugin']['zeroze007_hdx'];


$pmod = $_G['gp_pmod'];
$formAction = 'plugins&operation=config&do=' . $pluginid . '&identifier=' . $plugin['identifier'] . '&pmod=' . $pmod;

if (!submitcheck('settingsubmit', 1)) {
// 黑道设置
    $hdx = array();
    $query = DB::query("SELECT * FROM " . DB::table('hdx_setting') . " WHERE skey LIKE 'guard_%'");

    while ($setting = DB::fetch($query)) {
        $key = $setting['skey'];
        $hdx[$key] = $setting['svalue'];
    }



    if (empty($hdx['guard_rate'])) {
        $hdx = array(
            'guard_rate' => 40,
            'guard_time' => 5,
            'guard_allow_view' => 0,
            'guard_allow_post' => 0,
            'guard_escape_sta' => 5,
            'guard_escape_rate' => 40,
            'guard_escape_interval' => 10,
            'guard_rob_sta' => 20,
            'guard_rob_rate' => 20,
            'guard_rob_in_guard_rate' => 20,
            'guard_rob_in_guard_time' => 10,
            'guard_bail_fee' => 500,
            'guard_min_rate' => 10,
            'guard_max_rate' => 30,
            'guard_max_time' => 72,
            'guard_salary_rate' => 1,
            'guard_max_salary' => 50,
            'guard_min_salary' => 10,
            'guard_join_min_level' => 1,
            'guard_to_be_robbed_rate' => 10
            
        );
    }

    //showtips('<li>请</li><li>sss</li>');

    showformheader($formAction);

    showtableheader('');


    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_rate'), 'hdx[guard_rate]', $hdx['guard_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_rate_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_min_rate'), 'hdx[guard_min_rate]', $hdx['guard_min_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_min_rate_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_max_rate'), 'hdx[guard_max_rate]', $hdx['guard_max_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_max_rate_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_max_time'), 'hdx[guard_max_time]', $hdx['guard_max_time'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_max_time_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_salary_rate'), 'hdx[guard_salary_rate]', $hdx['guard_salary_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_salary_rate_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_max_salary'), 'hdx[guard_max_salary]', $hdx['guard_max_salary'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_max_salary_desc'));

    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_min_salary'), 'hdx[guard_min_salary]', $hdx['guard_min_salary'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_min_salary_desc'));


    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_join_min_level'), 'hdx[guard_join_min_level]', $hdx['guard_join_min_level'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_join_min_level_desc'));


    showsetting(lang('plugin/zeroze007_hdx', 'setting_guard_to_be_robbed_rate'), 'hdx[guard_to_be_robbed_rate]', $hdx['guard_to_be_robbed_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'setting_guard_to_be_robbed_rate_desc'));



    $query = DB::query("SELECT * FROM " . DB::table('common_usergroup') . " ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");


    $allowJoinGroupAry = unserialize($hdx['guard_allow_join_group_list']);
    $groupselect = array();
    while ($group = DB::fetch($query)) {

        $groupselect[$group['type']] .= '<option value="' . $group['groupid'] . '"' . (@in_array($group['groupid'], $allowJoinGroupAry) ? ' selected' : '') . '>' . $group['grouptitle'] . '</option>';
    }





    $usergroups = '<select name="hdx[guard_allow_join_group_list][]" size="10" multiple="multiple"><option value=""' . (@in_array('', $allowJoinGroupAry) ? ' selected' : '') . '>' . cplang('plugins_empty') . '</option>' .
        '<optgroup label="' . $lang['usergroups_member'] . '">' . $groupselect['member'] . '</optgroup>' .
        ($groupselect['special'] ? '<optgroup label="' . $lang['usergroups_special'] . '">' . $groupselect['special'] . '</optgroup>' : '') .
        ($groupselect['specialadmin'] ? '<optgroup label="' . $lang['usergroups_specialadmin'] . '">' . $groupselect['specialadmin'] . '</optgroup>' : '') .
        '<optgroup label="' . $lang['usergroups_system'] . '">' . $groupselect['system'] . '</optgroup></select>';



    showsetting(lang('plugin/zeroze007_hdx', 'setting_allow_join_group_list'), '', '', $usergroups, '', 0, lang('plugin/zeroze007_hdx', 'setting_allow_join_group_list_desc'));




    $allowHireGroupAry = unserialize($hdx['guard_allow_hire_group_list']);
    $groupselect = array();
    $query = DB::query("SELECT * FROM " . DB::table('common_usergroup') . " ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");

    while ($group = DB::fetch($query)) {

        $groupselect[$group['type']] .= '<option value="' . $group['groupid'] . '"' . (@in_array($group['groupid'], $allowHireGroupAry) ? ' selected' : '') . '>' . $group['grouptitle'] . '</option>';
    }

    $usergroups = '<select name="hdx[guard_allow_hire_group_list][]" size="10" multiple="multiple"><option value=""' . (@in_array('', $allowHireGroupAry) ? ' selected' : '') . '>' . cplang('plugins_empty') . '</option>' .
        '<optgroup label="' . $lang['usergroups_member'] . '">' . $groupselect['member'] . '</optgroup>' .
        ($groupselect['special'] ? '<optgroup label="' . $lang['usergroups_special'] . '">' . $groupselect['special'] . '</optgroup>' : '') .
        ($groupselect['specialadmin'] ? '<optgroup label="' . $lang['usergroups_specialadmin'] . '">' . $groupselect['specialadmin'] . '</optgroup>' : '') .
        '<optgroup label="' . $lang['usergroups_system'] . '">' . $groupselect['system'] . '</optgroup></select>';



    showsetting(lang('plugin/zeroze007_hdx', 'setting_allow_hire_group_list'), '', '', $usergroups, '', 0, lang('plugin/zeroze007_hdx', 'setting_allow_hire_group_list_desc'));



    showsubmit('settingsubmit');
    showtablefooter();
    showformfooter();
} else {

    require DISCUZ_ROOT . './source/plugin/zeroze007_hdx/include/function.inc.php';

    $hdx = $_POST['hdx'];

    if (intval($hdx['guard_min_salary']) > intval($hdx['guard_max_salary'])) {
        cpmsg(lang('plugin/zeroze007_hdx', 'setting_guard_salary_range_error'), '', 'error');
    }



    if ($hdx['guard_allow_join_group_list'][0] == '') {
        $hdx['guard_allow_join_group_list'] = array();
        $hdx['guard_allow_join_group_list'][0] = '';
    }

    $hdx['guard_allow_join_group_list'] = serialize($hdx['guard_allow_join_group_list']);


    if ($hdx['guard_allow_hire_group_list'][0] == '') {
        $hdx['guard_allow_hire_group_list'] = array();
        $hdx['guard_allow_hire_group_list'][0] = '';
    }

    $hdx['guard_allow_hire_group_list'] = serialize($hdx['guard_allow_hire_group_list']);

    // 进库前过滤
    foreach ($hdx as $key => $val) {
        $key = escape($key);
        $val = escape($val);
        DB::query("INSERT INTO " . DB::table('hdx_setting') . "(skey, svalue) VALUES ('" . $key . "', '" . $val . "') ON DUPLICATE KEY UPDATE svalue = '" . $val . "'");
    }
    cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=plugins&operation=config&do=' . $pluginid . '&identifier=zeroze007_hdx&pmod=guard_setting', 'succeed');
}
?>
