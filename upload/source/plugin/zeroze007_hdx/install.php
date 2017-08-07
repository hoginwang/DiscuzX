<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 8889 2010-04-23 07:48:22Z monkey $
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', '1');

try {

    $truncateTable = false;

    $config = array(
        'dbcharset' => $_G['config']['db']['1']['dbcharset'],
        'charset' => $_G['config']['output']['charset'],
        'tablepre' => $_G['config']['db']['1']['tablepre']
    );




    $plugin_identifier = 'zeroze007_hdx';

    $theurl = ADMINSCRIPT . '?action=plugins&operation=plugininstall&dir=' . $dir . '&installtype=' . $installtype;
//dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=pluginupgrade&dir='.$dir.'&installtype='.$modules['extra']['installtype'].'&fromversion='.$plugin['version']);

    $devmode = file_exists(DISCUZ_ROOT . './source/plugin/zeroze007_hdx/zeroze007_hdx_dev.sql');
    $sqlfile = DISCUZ_ROOT . ($devmode ? './source/plugin/zeroze007_hdx/zeroze007_hdx_dev.sql' : './source/plugin/zeroze007_hdx/zeroze007_hdx.sql');


    if (empty($_GET['step'])) {
        $_GET['step'] = 'sql';
    }

    if ($_GET['step'] == 'prepare') {
        show_msg($installlang['db_structure_upgrade'], $theurl . '&step=sql');
    } elseif ($_GET['step'] == 'sql') {

        $sql = implode('', file($sqlfile));
        preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
        $newtables = empty($matches[1]) ? array() : $matches[1];
        $newsqls = empty($matches[0]) ? array() : $matches[0];
        if (empty($newtables) || empty($newsqls)) {
            show_msg($installlang['sql_empty']);
        }

        $i = empty($_GET['i']) ? 0 : intval($_GET['i']);
        $count_i = count($newtables);
        if ($i >= $count_i) {
            show_msg($installlang['data_insert_step'], $theurl . '&step=data');
        }
        $newtable = $newtables[$i];

        $specid = intval($_GET['specid']);
        if ($specid && in_array($newtable, array('forum_post', 'forum_thread'))) {
            $spectable = $newtable;
            $newtable = get_special_table_by_num($newtable, $specid);
        }

        $newcols = getcolumn($newsqls[$i]);

        if (!$query = DB::query("SHOW CREATE TABLE " . DB::table($newtable), 'SILENT')) {
            preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*\=/is", $newsqls[$i], $maths);

            if (in_array($newtable, array('common_session', 'forum_threaddisablepos', 'common_process'))) {
                //$type = mysql_get_server_info() > '4.1' ? " ENGINE=MEMORY" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]" ) : " TYPE=HEAP";
				$type = 5.5 > 4.1 ? " ENGINE=MEMORY" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]" ) : " TYPE=HEAP";
            } else {
                //$type = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]" ) : " TYPE=MYISAM";
				$type = 5.5 > 4.1 ? " ENGINE=MYISAM" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]" ) : " TYPE=MYISAM";
            }
            $usql = $maths[1] . $type;

            $usql = str_replace("CREATE TABLE IF NOT EXISTS pre_", 'CREATE TABLE IF NOT EXISTS ' . $config['tablepre'], $usql);
            $usql = str_replace("CREATE TABLE pre_", 'CREATE TABLE ' . $config['tablepre'], $usql);

            if (!DB::query($usql, 'SILENT')) {
                $truncateTable = true;
                throw new Exception($installlang['add_table'] . DB::table($newtable) . $installlang['add_table_error']);
            } else {
                $msg = $installlang['add_table'] . DB::table($newtable) . $installlang['finish'];
            }
        }


        if ($specid) {
            $newtable = $spectable;
        }

        if (get_special_table_by_num($newtable, $specid + 1)) {
            $next = $theurl . '&step=sql&i=' . ($_GET['i']) . '&specid=' . ($specid + 1);
        } else {
            $next = $theurl . '&step=sql&i=' . ($_GET['i'] + 1);
        }
        show_msg("[ $i / $count_i ] " . $msg, $next);
    } elseif ($_GET['step'] == 'data') {
        if (empty($_GET['op'])) {
            $nextop = 'yule';
            $sql = <<<SQL
INSERT INTO `pre_hdx_shop_item` (`id`, `name`, `type`, `price`, `rate`, `description`, `img_file`, `disp_order`, `available`, `durability`, `d_loss_rate`) VALUES
(1, '{$installlang['item_1']}', 'weapon', 10, '5,10', '{$installlang['item_1_desc']}', 'source/plugin/zeroze007_hdx/images/shop/1.jpg', 1, 1, 100, '10,20'),
(2, '{$installlang['item_2']}', 'weapon', 50, '10,15', '{$installlang['item_2_desc']}', 'source/plugin/zeroze007_hdx/images/shop/2.jpg', 2, 1, 200, '5,10'),
(3, '{$installlang['item_3']}', 'weapon', 100, '15,20', '{$installlang['item_3_desc']}', 'source/plugin/zeroze007_hdx/images/shop/3.jpg', 3, 1, 250, '10,15'),
(4, '{$installlang['item_4']}', 'armor', 200, '15,20', '{$installlang['item_4_desc']}', 'source/plugin/zeroze007_hdx/images/shop/4.jpg', 4, 1, 250, '20,30'),
(5, '{$installlang['item_5']}', 'food', 10, '15,20', '{$installlang['item_5_desc']}', 'source/plugin/zeroze007_hdx/images/shop/5.jpg', 5, 1, 0, '0');
SQL;
            runquery($sql);
            show_msg($installlang['shop_data_add_complete'], "$theurl&step=data&op=$nextop");
        } elseif ($_GET['op'] == 'yule') {
            $nextop = 'title';
            $sql = <<<SQL
INSERT INTO `pre_hdx_yule` (`id`, `name`, `price`, `add_sta`, `description`, `img_file`, `disp_order`, `available`) VALUES
(1, '{$installlang['yule_1']}', 20, 5, '{$installlang['yule_1_desc']}', 'source/plugin/zeroze007_hdx/images/yule/1.jpg', 1, 1),
(2, '{$installlang['yule_2']}', 50, 10, '{$installlang['yule_2_desc']}', 'source/plugin/zeroze007_hdx/images/yule/2.jpg', 2, 1),
(3, '{$installlang['yule_3']}', 100, 20, '{$installlang['yule_3_desc']}', 'source/plugin/zeroze007_hdx/images/yule/3.jpg', 3, 1);
SQL;
            runquery($sql);
            show_msg($installlang['yule_data_add_complete'], "$theurl&step=data&op=$nextop");
        } elseif ($_GET['op'] == 'title') {
            $nextop = 'setting';
            $sql = <<<SQL
INSERT INTO `pre_hdx_title` (`id`, `name`, `high`, `low`) VALUES
(1, '{$installlang['lv_1']}', -999999999, 0),
(2, '{$installlang['lv_2']}', 0, 10),
(3, '{$installlang['lv_3']}', 10, 20),
(4, '{$installlang['lv_4']}', 20, 30),
(5, '{$installlang['lv_5']}', 30, 40),
(6, '{$installlang['lv_6']}', 40, 50),
(7, '{$installlang['lv_7']}', 50, 70),
(8, '{$installlang['lv_8']}', 70, 100),
(9, '{$installlang['lv_9']}', 100, 999999999)
SQL;
            runquery($sql);
            show_msg($installlang['title_data_add_complete'], "$theurl&step=data&op=$nextop");
            
                    } elseif ($_GET['op'] == 'setting') {
            $nextop = 'end';
            $sql = <<<SQL
INSERT INTO `pre_hdx_setting` (`skey`, `svalue`) VALUES
('rob_anyone', '0'),
('rob_in_thread', '1'),
('rob_not_login_member_time', '0'),
('rob_player_sta', '5'),
('rob_player_money_rate', '5%,10%'),
('rob_member_sta', '10'),
('rob_member_money_rate', '1%,5%'),
('rob_interval', '10'),
('rob_rate', '30'),
('rob_exp', '1,5'),
('rob_success_sw', '1,5'),
('rob_success_exp', '1,5'),
('rob_fail_sw', '1,2'),
('rob_fail_lost_money_rate', '1%,2%'),
('rob_fail_back_money_rate', '50'),
('rob_fail_money_to_vicim', '0'),
('rob_day_max', '10'),
('rob_daily_robbed_times', '3'),
('rob_min_money_to_protect', '100'),
('rob_allow_rob_prisoner', '0'),
('rob_suggest_list', ''),
('rob_protected_user_list', ''),
('rob_protected_group_list', 'a:1:{i:0;s:0:"";}'),
('jail_rate', '40'),
('jail_time', '5'),
('jail_allow_view', '0'),
('jail_allow_post', '0'),
('jail_escape_sta', '5'),
('jail_escape_rate', '40'),
('jail_escape_interval', '10'),
('jail_rob_sta', '20'),
('jail_rob_rate', '20'),
('jail_rob_in_jail_rate', '20'),
('jail_rob_in_jail_time', '10'),
('jail_bail_fee', '500'),
('jail_allow_use_item', '0'),
('jail_allow_view_group_list', 'a:1:{i:0;s:0:"";}'),
('jail_allow_post_group_list', 'a:1:{i:0;s:0:"";}'),
('guard_rate', '40'),
('guard_min_rate', '10'),
('guard_max_rate', '30'),
('guard_max_time', '72'),
('guard_salary_rate', '1'),
('guard_max_salary', '50'),
('guard_min_salary', '10'),
('guard_join_min_level', '1'),
('guard_to_be_robbed_rate', '10'),
('guard_allow_join_group_list', 'a:1:{i:0;s:0:"";}'),
('guard_allow_hire_group_list', 'a:1:{i:0;s:0:"";}'),
('quit_allow_quit', '0'),
('quit_rate', '1.5'),
('quit_rob_times', '10'),
('quit_join_times', '100'),
('allow_away', '1'),
('away_fee', '100'),
('away_max_days', '100'),
('level_rate', '0.5');
SQL;
            runquery($sql);
            show_msg($installlang['setting_data_add_complete'], "$theurl&step=data&op=$nextop");
            
        } elseif ($_GET['op'] == 'end') {
            
            
            runquery("UPDATE `pre_common_plugin` SET available = 1 WHERE identifier = 'zeroze007_hdx'");
            
            show_msg($installlang['data_process_complete'], "$theurl&step=cache");
        }
    } elseif ($_GET['step'] == 'cache') {
        
        
        /*
          if (!$devmode && @$fp = fopen($lockfile, 'w')) {
          fwrite($fp, ' ');
          fclose($fp);
          }

          dir_clear(ROOT_PATH . './data/template');
          dir_clear(ROOT_PATH . './data/cache');
          dir_clear(ROOT_PATH . './data/threadcache');
          dir_clear(ROOT_PATH . './uc_client/data');
          dir_clear(ROOT_PATH . './uc_client/data/cache'); */

        include_once libfile('function/cache');
        updatecache();
        /*
          require_once libfile('function/group');
          $groupindex['randgroupdata'] = $randgroupdata = grouplist('lastupdate', array('ff.membernum', 'ff.icon'), 80);
          $groupindex['topgrouplist'] = $topgrouplist = grouplist('activity', array('f.commoncredits', 'ff.membernum', 'ff.icon'), 10);
          $groupindex['updateline'] = TIMESTAMP;
          $groupdata = C::t('forum_forum')->fetch_group_counter();
          $groupindex['todayposts'] = $groupdata['todayposts'];
          $groupindex['groupnum'] = $groupdata['groupnum'];
          savecache('groupindex', $groupindex);
          C::t('forum_groupfield')->truncate();
          savecache('forum_guide', ''); */

        cleartemplatecache();


        if ($_GET['from']) {
            show_msg('<span id="finalmsg">' . $installlang['cache_updating_now'] . '</span><iframe src="../misc.php?mod=initsys" style="display:none;" onload="window.location.href=\'' . $_GET['from'] . '\'"></iframe>');
        } else {
            show_msg('<span id="finalmsg">' . $installlang['cache_updating_now'] . '</span><iframe src="../misc.php?mod=initsys" style="display:none;" onload="document.getElementById(\'finalmsg\').innerHTML = \'' . $installlang['plugin_install_finished'] . '\'"></iframe>', $theurl . '&step=done');
        }
    } elseif ($_GET['step'] == 'done') {



        $finish = TRUE;
    }
} catch (DbException $e) {
    updateFailed($e->getMessage() . '<br>SQL: ' . $e->sql, $truncateTable);
} catch (Exception $e) {
    updateFailed($e->getMessage(), $truncateTable);
}

function updateFailed($errorMessage, $truncateTable) {
    global $installlang;



    $error = $installlang['install_fail'] . "<br><div style='color: #333333;
    float: left;
    font-size: 12px;
    font-weight: normal;
    line-height: 20px;
    margin-bottom: 20px;
    margin-top: 10px;
    width: 100%;'>" . $errorMessage . "</div>" . ($trancateTable ? "<div style='color:green;font-weight:normal'>" . $installlang['send_error_to_author'] . "</div>" : "");

    show_msg($error, '', 'error');
    $finish = FALSE;
}

function show_msg($message, $url_forward = '', $time = 2000, $noexit = 0, $notice = '') {
    global $installlang;
    if ($url_forward) {
        $url_forward = $_GET['from'] ? $url_forward . '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : $url_forward;
        $message = "<a href=\"$url_forward\">$message (" . $installlang['redirecting_now'] . ")</a><br>$notice<script>setTimeout(\"window.location.href ='$url_forward';\", $time);</script>";
        //$message = "<a href=\"$url_forward\">$message (Ìø×ªÖÐ...)</a>";
    }

    show_header();
    print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
    show_footer();
    !$noexit && exit();
}

function show_header() {
    global $config;
    global $installlang;

    $nowarr = array($_GET['step'] => ' class="current"');
    if (in_array($_GET['step'], array('waitingdb', 'prepare'))) {
        $nowarr = array('sql' => ' class="current"');
    }
    print<<<END
	
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>

	<div class="bodydiv">
	<h1>{$installlang['plugin_install_tool']}</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>{$installlang['step_start']}</td>
	<td{$nowarr[sql]}>{$installlang['step_sql']}</td>
	<td{$nowarr[data]}>{$installlang['step_data']}</td>
	<td{$nowarr[cache]}>{$installlang['install_finish']}</td>
	</tr>
	</table>
	<br>
END;
}

function show_footer() {
    print<<<END
	</div>
	<div id="footer">&copy; Comsenz Inc. 2001-2012 http://www.comsenz.com</div>
	</div>

END;
}

function getcolumn($creatsql) {

    $creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
    preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

    $cols = explode("\n", $matchs[1]);
    $newcols = array();
    foreach ($cols as $value) {
        $value = trim($value);
        if (empty($value))
            continue;
        $value = remakesql($value);
        if (substr($value, -1) == ',')
            $value = substr($value, 0, -1);

        $vs = explode(' ', $value);
        $cname = $vs[0];

        if ($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

            $name_length = strlen($cname);
            if ($cname == 'UNIQUE')
                $name_length = $name_length + 4;

            $subvalue = trim(substr($value, $name_length));
            $subvs = explode(' ', $subvalue);
            $subcname = $subvs[0];
            $newcols[$cname][$subcname] = trim(substr($value, ($name_length + 2 + strlen($subcname))));
        } elseif ($cname == 'PRIMARY') {

            $newcols[$cname] = trim(substr($value, 11));
        } else {

            $newcols[$cname] = trim(substr($value, strlen($cname)));
        }
    }
    return $newcols;
}

function remakesql($value) {
    $value = trim(preg_replace("/\s+/", ' ', $value));
    $value = str_replace(array('`', ', ', ' ,', '( ', ' )', 'mediumtext'), array(
        '', ',', ',', '(', ')', 'text'), $value);
    return $value;
}

function get_special_table_by_num($tablename, $num) {
    $tables_array = get_special_tables_array($tablename);

    $num--;
    return isset($tables_array[$num]) ? $tables_array[$num] : FALSE;
}

function get_special_tables_array($tablename) {
    $tablename = DB::table($tablename);
    $tablename = str_replace('_', '\_', $tablename);
    $query = DB::query("SHOW TABLES LIKE '{$tablename}\_%'");
    $dbo = DB::object();
    $tables_array = array();
    while ($row = $dbo->fetch_array($query, MYSQL_NUM)) {
        if (preg_match("/^{$tablename}_(\\d+)$/i", $row[0])) {
            $prefix_len = strlen($dbo->tablepre);
            $row[0] = substr($row[0], $prefix_len);
            $tables_array[] = $row[0];
        }
    }
    return $tables_array;
}

function dir_clear($dir) {
    global $lang;
    if ($directory = @dir($dir)) {
        while ($entry = $directory->read()) {
            $filename = $dir . '/' . $entry;
            if (is_file($filename)) {
                @unlink($filename);
            }
        }
        $directory->close();
        @touch($dir . '/index.htm');
    }
}

?>