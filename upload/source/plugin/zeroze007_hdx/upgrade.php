<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', '1');


require DISCUZ_ROOT . './source/plugin/zeroze007_hdx/include/function.inc.php';

try {

    $fromversion = $_GET['fromversion'];

    $config = array(
        'dbcharset' => $_G['config']['db']['1']['dbcharset'],
        'charset' => $_G['config']['output']['charset'],
        'tablepre' => $_G['config']['db']['1']['tablepre']
    );



    $plugin_identifier = 'zeroze007_hdx';

    $theurl = ADMINSCRIPT . '?action=plugins&operation=pluginupgrade&dir=' . $dir . '&installtype=' . $installtype . '&fromversion=' . $_GET['fromversion'];

    $devmode = file_exists(DISCUZ_ROOT . './source/plugin/zeroze007_hdx/zeroze007_hdx_dev.sql');
    $sqlfile = DISCUZ_ROOT . ($devmode ? './source/plugin/zeroze007_hdx/zeroze007_hdx_dev.sql' : './source/plugin/zeroze007_hdx/zeroze007_hdx.sql');


    if (empty($_GET['step'])) {
        $_GET['step'] = 'start';
    }

    if ($_GET['step'] == 'start') {

        $yulegame_plugin = DB::fetch_first("SELECT available,name, pluginid, modules FROM " . DB::table('common_plugin') . " WHERE identifier='{$plugin_identifier}' LIMIT 1");

        $pluginId = $yulegame_plugin['pluginid'];
        if ($yulegame_plugin['available']) {

            DB::query("UPDATE " . DB::table('common_plugin') . " SET available = 0 WHERE pluginid='" . intval($pluginId) . "'");

            require_once libfile('function/cache');
            updatecache('setting');
            show_msg($installlang['plugin_not_closed'], $theurl . '&step=start&plugin_id=' . $pluginId);
        }

        show_msg($installlang['update_note'] . '
			<a href="' . $theurl . '&step=prepare&plugin_id=' . $pluginId . '">' . $installlang['ready_to_upgrade'] . '</a>', $theurl . '&step=prepare&plugin_id=' . $pluginId);
    } elseif ($_GET['step'] == 'prepare') {
        $fromversionInt = intval(str_replace('.', '', $fromversion));


        if ($fromversionInt < 150) {
            DB::query("DROP TABLE IF EXISTS " . DB::table('hdx_guard'));
        }

        $pluginId = intval($_GET['plugin_id']);
        /*
          $usql = "RENAME TABLE " . DB::table('hdx_weapon1') . " TO " . DB::table('hdx_shop_item');
          if (!DB::query($usql, 'SILENT')) {
          throw new Exception('');
          } */
        if (!$row = DB::fetch_first("SHOW TABLES LIKE '" . DB::table('hdx_shop_item') . "'")) {
            if (DB::fetch_first("SHOW TABLES LIKE '" . DB::table('hdx_weapon') . "'")) {
                $usql = "RENAME TABLE " . DB::table('hdx_weapon') . " TO " . DB::table('hdx_shop_item');
                if (!DB::query($usql, 'SILENT')) {
                    throw new Exception('');
                }
            }
        }

        if (DB::fetch_first("SHOW TABLES LIKE '" . DB::table('hdx_shop_item') . "'")) {
            if (DB::fetch_first("SHOW COLUMNS FROM " . DB::table('hdx_shop_item') . " LIKE 'add_rate'")) {
                if (!$row = DB::fetch_first("SHOW COLUMNS FROM " . DB::table('hdx_shop_item') . " LIKE 'rate'")) {
                    $usql = "ALTER TABLE " . DB::table('hdx_shop_item') . " CHANGE `add_rate` `rate` VARCHAR(10)";
                    if (!DB::query($usql, 'SILENT')) {
                        throw new Exception('');
                    }
                }
            }

            if (DB::fetch_first("SHOW COLUMNS FROM " . DB::table('hdx_shop_item') . " LIKE 'loss_rate'")) {
                if (!$row = DB::fetch_first("SHOW COLUMNS FROM " . DB::table('hdx_shop_item') . " LIKE 'd_loss_rate'")) {
                    $usql = "ALTER TABLE " . DB::table('hdx_shop_item') . " CHANGE `loss_rate` `d_loss_rate` VARCHAR(10)";
                    if (!DB::query($usql, 'SILENT')) {
                        throw new Exception('');
                    }
                }
            }
        }

        if (!$row = DB::fetch_first("SHOW TABLES LIKE '" . DB::table('hdx_player_item') . "'")) {
            if (DB::fetch_first("SHOW TABLES LIKE '" . DB::table('hdx_player_weapon') . "'")) {
                $usql = "RENAME TABLE " . DB::table('hdx_player_weapon') . " TO " . DB::table('hdx_player_item');
                if (!DB::query($usql, 'SILENT')) {
                    throw new Exception('');
                }
            }
        }

        if (DB::fetch_first("SHOW TABLES LIKE '" . DB::table('hdx_player_item') . "'")) {
            $remove_oldpk = false;
            $query = DB::query("SHOW INDEX FROM " . DB::table('hdx_player_item'));
            while ($row = DB::fetch($query)) {
                if ($row['Column_name'] == 'uid') {
                    $remove_oldpk = true;
                    break;
                }
            }
            if ($remove_oldpk) {
                $usql = "ALTER TABLE " . DB::table('hdx_player_item') . " DROP PRIMARY KEY";
                if (!DB::query($usql, 'SILENT')) {
                    throw new Exception('');
                }
            }

            if (DB::fetch_first("SHOW COLUMNS FROM " . DB::table('hdx_player_item') . " LIKE 'weapon_id'")) {
                $usql = "ALTER TABLE " . DB::table('hdx_player_item') . " CHANGE `weapon_id` `item_id` SMALLINT(6) NOT NULL";
                if (!DB::query($usql, 'SILENT')) {
                    throw new Exception('');
                }
            }
        }

        show_msg($installlang['db_structure_upgrade'], $theurl . '&step=sql&plugin_id=' . intval($_GET['plugin_id']));
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
            show_msg($installlang['data_insert_step'], $theurl . '&step=data&plugin_id=' . intval($_GET['plugin_id']));
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
                show_msg($installlang['add_table'] . DB::table($newtable) . $installlang['add_table_error_execute_manually'] . dhtmlspecialchars($usql));
            } else {
                $msg = $installlang['add_table'] . DB::table($newtable) . $installlang['finish'];
            }
        } else {
            $value = DB::fetch($query);
            $oldcols = getcolumn($value['Create Table']);

            $updates = array();
            $allfileds = array_keys($newcols);
            foreach ($newcols as $key => $value) {
                if ($key == 'PRIMARY') {
                    if ($value != $oldcols[$key]) {
                        if (!empty($oldcols[$key])) {
                            $usql = "RENAME TABLE " . DB::table($newtable) . " TO " . DB::table($newtable . '_bak');
                            if (!DB::query($usql, 'SILENT')) {
                                show_msg($installlang['upgrade_table'] . DB::table($newtable) . $installlang['upgrade_table_error'] . '<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . "</div><br><b>Error</b>: " . DB::error() . "<br><b>Errno.</b>: " . DB::errno());
                            } else {
                                $msg = $installlang['rename_table'] . DB::table($newtable) . $installlang['finish'];
                                show_msg($msg, $theurl . '&step=sql&i=' . $_GET['i']);
                            }
                        }
                        $updates[] = "ADD PRIMARY KEY $value";
                    }
                } elseif ($key == 'KEY') {
                    foreach ($value as $subkey => $subvalue) {
                        if (!empty($oldcols['KEY'][$subkey])) {
                            if ($subvalue != $oldcols['KEY'][$subkey]) {
                                $updates[] = "DROP INDEX `$subkey`";
                                $updates[] = "ADD INDEX `$subkey` $subvalue";
                            }
                        } else {
                            $updates[] = "ADD INDEX `$subkey` $subvalue";
                        }
                    }
                } elseif ($key == 'UNIQUE') {
                    foreach ($value as $subkey => $subvalue) {
                        if (!empty($oldcols['UNIQUE'][$subkey])) {
                            if ($subvalue != $oldcols['UNIQUE'][$subkey]) {
                                $updates[] = "DROP INDEX `$subkey`";
                                $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                            }
                        } else {
                            $usql = "ALTER TABLE  " . DB::table($newtable) . " DROP INDEX `$subkey`";
                            DB::query($usql, 'SILENT');
                            $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                        }
                    }
                } else {
                    if (!empty($oldcols[$key])) {
                        if (strtolower($value) != strtolower($oldcols[$key])) {
                            $updates[] = "CHANGE `$key` `$key` $value";
                        }
                    } else {
                        $j = array_search($key, $allfileds);
                        $fieldposition = $j > 0 ? 'AFTER `' . $allfileds[$j - 1] . '`' : 'FIRST';
                        $updates[] = "ADD `$key` $value $fieldposition";
                    }
                }
            }

            if (!empty($updates)) {
                $usql = "ALTER TABLE " . DB::table($newtable) . " " . implode(', ', $updates);
                if (!DB::query($usql, 'SILENT')) {
                    show_msg($installlang['upgrade_table'] . DB::table($newtable) . $installlang['upgrade_table_error'] . '<br><br><b>Éý¼¶SQLÓï¾ä</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . "</div><br><b>Error</b>: " . DB::error() . "<br><b>Errno.</b>: " . DB::errno());
                } else {
                    $msg = $installlang['upgrade_table'] . DB::table($newtable) . $installlang['finish'];
                }
            } else {
                $msg = $installlang['check_table'] . DB::table($newtable) . $installlang['check_table_complete'];
            }
        }

        if ($specid) {
            $newtable = $spectable;
        }

        if (get_special_table_by_num($newtable, $specid + 1)) {
            $next = $theurl . '&step=sql&i=' . ($_GET['i']) . '&specid=' . ($specid + 1) . '&plugin_id=' . intval($_GET['plugin_id']);
        } else {
            $next = $theurl . '&step=sql&i=' . ($_GET['i'] + 1) . '&plugin_id=' . intval($_GET['plugin_id']);
        }
        show_msg("[ $i / $count_i ] " . $msg, $next, 1000);
    } elseif ($_GET['step'] == 'data') {

        $fromversionInt = intval(str_replace('.', '', $fromversion));

        $pluginId = intval($_GET['plugin_id']);

        // only do it when version less than 1.4.0
        if ($fromversionInt < 140 && $pluginId > 0 && $fromversion != '1.4') {
            // set weapon
            if (DB::fetch_first("SHOW COLUMNS FROM " . DB::table('hdx_shop_item') . " LIKE 'type'")) {
                DB::query("UPDATE " . DB::table('hdx_shop_item') . " SET `type`='weapon'");
            }
        }


        $titlesCount = DB::result_first("SELECT COUNT(*) FROM " . DB::table('hdx_title'));
        if ($titlesCount == 0) {
            $usql = "INSERT INTO " . DB::table('hdx_title') . "(`id`, `name`, `high`, `low`) VALUES
(1, '" . $installlang['lv_1'] . "', -999999999, 0),
(2, '" . $installlang['lv_2'] . "', 0, 10),
(3, '" . $installlang['lv_3'] . "', 10, 20),
(4, '" . $installlang['lv_4'] . "', 20, 30),
(5, '" . $installlang['lv_5'] . "', 30, 40),
(6, '" . $installlang['lv_6'] . "', 40, 50),
(7, '" . $installlang['lv_7'] . "', 50, 70),
(8, '" . $installlang['lv_8'] . "', 70, 100),
(9, '" . $installlang['lv_9'] . "', 100, 999999999)";
            if (!DB::query($usql, 'SILENT')) {
                throw new Exception('');
            }
        }


        show_msg($installlang['data_process_complete'], "$theurl&step=cache");
    } elseif ($_GET['step'] == 'cache') {

        if (!$devmode && @$fp = fopen($lockfile, 'w')) {
            fwrite($fp, ' ');
            fclose($fp);
        }

        dir_clear(ROOT_PATH . './data/template');
        dir_clear(ROOT_PATH . './data/cache');
        dir_clear(ROOT_PATH . './data/threadcache');
        dir_clear(ROOT_PATH . './uc_client/data');
        dir_clear(ROOT_PATH . './uc_client/data/cache');

        show_msg($installlang['plugin_upgrade_finished'], "$theurl&step=done");
    } elseif ($_GET['step'] == 'done') {



        $finish = TRUE;
    }
} catch (Exception $e) {
    if ($usql && $e->getMessage() == '') {
        $sqlmsg = '<br><br>' . $installlang['error_sql_stmt'] . '<br><br><div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . '</div><br><b>Error</b>: ' . DB::error() . '<br><b>Errno</b>: ' . DB::errno() . '<br>';
    } else {
        $sqlmsg = '<br><br><div style=\"position:absolute;background:#EBEBEB;padding:0.5em;\">' . $installlang['error_reason'] . $e->getMessage() . '</div>';
    }

    $msg = $installlang['sql_error'] . '<a href="' . $_G['siteurl'] . 'source/plugin/zeroze007_hdx/update_standalone.php" target=_blank>' . $_G['siteurl'] . 'source/plugin/zeroze007_hdx/update_standalone.php</a>' . $sqlmsg . $installlang['run_update_standalone'];

    updateFailed($plugin, $fromversion, $pluginarray, $msg);
}

function updateFailed($plugin, $fromVersion, $pluginarray, $errorMessage) {

    global $installlang;
    //$fromVersion = escape($fromVersion);
    //DB::query("UPDATE " . DB::table('common_plugin') . " SET version='{$fromVersion}' WHERE pluginid='$plugin[pluginid]'");

    $error = $installlang['upgrade_fail'] . "<br><div style='color: #333333;
    float: left;
    font-size: 12px;
    font-weight: normal;
    line-height: 20px;
    margin-bottom: 20px;
    margin-top: 10px;
    width: 100%;'>" . $errorMessage . "</div>";

    show_msg($error);
    $finish = FALSE;
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
    $value = str_replace(array('`', ', ', ' ,', '( ', ' )', 'mediumtext'), array('', ',', ',', '(', ')', 'text'), $value);
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

function show_msg($message, $url_forward = '', $time = 5000, $noexit = 0, $notice = '') {
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
	<h1>{$installlang['plugin_upgrade_tool']}</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>{$installlang['update_1']}</td>
	<td{$nowarr[sql]}>{$installlang['update_2']}</td>
	<td{$nowarr[data]}>{$installlang['update_3']}</td>
	<td{$nowarr[cache]}>{$installlang['update_4']}</td>
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

?>