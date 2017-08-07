<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.php 30824 2012-06-21 10:01:03Z liulanbo $
 */
include_once('../../../source/class/class_core.php');

include_once('../../../source/function/function_core.php');

include_once(DISCUZ_ROOT . '/source/function/function_admincp.php');


//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', '1');

@set_time_limit(0);


$cachelist = array();
$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = false;
$discuz->init_user = false;
$discuz->init_session = false;
$discuz->init_misc = false;

$discuz->init();


try {
    /*
      $langAry = array('SC_GBK', 'TC_BIG5', 'SC_UTF8', 'TC_UTF8');

      $plugin_xmlfile = '';
      foreach ($langAry as $lang) {
      $xmlfile = 'discuz_plugin_zeroze007_hdx_' . $lang . '.xml';
      $importfile = DISCUZ_ROOT . './source/plugin/zeroze007_hdx/' . $xmlfile;
      if (file_exists($importfile)) {
      $plugin_xmlfile = $importfile;
      break;
      }
      }

      if ($plugin_xmlfile == '') {
      show_msg('插件文件不存在，请到应用中心重新安装黑道插件。');
      }
      $importtxt = @implode('', file($importfile));
      $pluginarray = getimportdata('Discuz! Plugin');

      $toversion = $pluginarray['plugin']['version'];
     */
    $config = array(
        'dbcharset' => $_G['config']['db']['1']['dbcharset'],
        'charset' => $_G['config']['output']['charset'],
        'tablepre' => $_G['config']['db']['1']['tablepre']
    );

    $plugin_identifier = 'zeroze007_hdx';
    $toversion = $pluginarray['plugin']['version'];
    $theurl = 'update_standalone.php';

    $_G['siteurl'] = preg_replace('/\/install\/$/i', '/', $_G['siteurl']);

    if ($_GET['from']) {
        if (md5($_GET['from'] . $_G['config']['security']['authkey']) != $_GET['frommd5']) {
            $refererarr = parse_url(dreferer());
            list($dbreturnurl, $dbreturnurlmd5) = explode("\t", authcode($_GET['from']));
            if (md5($dbreturnurl) == $dbreturnurlmd5) {
                $dbreturnurlarr = parse_url($dbreturnurl);
            } else {
                $dbreturnurlarr = parse_url($_GET['from']);
            }
            parse_str($dbreturnurlarr['query'], $dbreturnurlparamarr);
            $operation = $dbreturnurlparamarr['operation'];
            $version = $dbreturnurlparamarr['version'];
            $release = $dbreturnurlparamarr['release'];
            if (!$operation || !$version || !$release) {
                show_msg('请求的参数不正确');
            }
            $time = $_G['timestamp'];
            dheader('Location: ' . $_G['siteurl'] . basename($refererarr['path']) . '?action=upgrade&operation=' . $operation . '&version=' . $version . '&release=' . $release . '&ungetfrom=' . $time . '&ungetfrommd5=' . md5($time . $_G['config']['security']['authkey']));
        }
    }

    $lockfile = DISCUZ_ROOT . './data/hdx_update.lock';
    if (file_exists($lockfile) && !$_GET['from']) {
        show_msg('请您先登录服务器ftp，手工删除 ./data/hdx_update.lock 文件，再次运行本文件进行升级。');
    }

    $devmode = file_exists(DISCUZ_ROOT . './source/plugin/zeroze007_hdx/zeroze007_hdx_dev.sql');
    $sqlfile = DISCUZ_ROOT . ($devmode ? './source/plugin/zeroze007_hdx/zeroze007_hdx_dev.sql' : './source/plugin/zeroze007_hdx/zeroze007_hdx.sql');

    if (!file_exists($sqlfile)) {
        show_msg('SQL文件 ' . $sqlfile . ' 不存在');
    }
    if ($_POST['delsubmit']) {
        if (!empty($_POST['deltables'])) {
            foreach ($_POST['deltables'] as $tname => $value) {
                DB::query("DROP TABLE `" . DB::table($tname) . "`");
            }
        }
        if (!empty($_POST['delcols'])) {
            foreach ($_POST['delcols'] as $tname => $cols) {
                foreach ($cols as $col => $indexs) {
                    if ($col == 'PRIMARY') {
                        DB::query("ALTER TABLE " . DB::table($tname) . " DROP PRIMARY KEY", 'SILENT');
                    } elseif ($col == 'KEY' || $col == 'UNIQUE') {
                        foreach ($indexs as $index => $value) {
                            DB::query("ALTER TABLE " . DB::table($tname) . " DROP INDEX `$index`", 'SILENT');
                        }
                    } else {
                        DB::query("ALTER TABLE " . DB::table($tname) . " DROP `$col`");
                    }
                }
            }
        }

        show_msg('删除表和字段操作完成了', $theurl . '?step=cache');
    }
    if (empty($_GET['step']))
        $_GET['step'] = 'start';


    if ($_GET['step'] == 'start') {
        $yulegame_plugin = DB::fetch_first("SELECT available,name, pluginid, modules FROM " . DB::table('common_plugin') . " WHERE identifier='{$plugin_identifier}' LIMIT 1");

        $pluginId = $yulegame_plugin['pluginid'];
        if ($yulegame_plugin['available']) {

            DB::query("UPDATE " . DB::table('common_plugin') . " SET available = 0 WHERE pluginid='" . intval($pluginId) . "'");

            require_once libfile('function/cache');
            updatecache('setting');
            show_msg('您的插件未关闭，正在关闭，请稍后...', $theurl . '?step=start', 5000);
        }

        show_msg('说明：<br>本升级程序会参照最新的SQL文件，对数据库进行同步升级。<br>
			请确保当前目录下 ./zeroze007_hdx.sql 文件为最新版本。<br><br>
			如果升级过程中遇到问题，请截图反馈给黑道作者，以便为你提供解决方法(请不要随意卸载插件后再重新安装，以免造成你的数据丢失)。<br><br>
			<a href="' . $theurl . '?step=prepare' . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . '">准备完毕，升级开始</a>');
    } elseif ($_GET['step'] == 'waitingdb') {

        $query = DB::query("SHOW FULL PROCESSLIST");
        $rows = array();
        while ($data = DB::fetch($query)) {
            $rows[] = $data;
        }



        foreach ($rows as $row) {
            if (in_array(md5($row['Info']), $_GET['sql'])) {
                $list .= '[时长]:' . $row['Time'] . '秒 [状态]:<b>' . $row['State'] . '</b>[信息]:' . $row['Info'] . '<br><br>';
            }
        }
        if (empty($list) && empty($_GET['sendsql'])) {
            $msg = '准备进入下一步操作，请稍后...';
            $notice = '';
            $url = "?step=$_GET[nextstep]";
            $time = 5;
        } else {
            $msg = '正在升级数据，请稍后...';
            $notice = '<br><br><b>以下是正在执行的数据库升级语句:</b><br>' . $list . base64_decode($_GET['sendsql']);
            $sqlurl = implode('&sql[]=', $_GET['sql']);
            $url = "?step=waitingdb&nextstep=$_GET[nextstep]&sql[]=" . $sqlurl;
            $time = 20;
        }
        show_msg($msg, $theurl . $url, $time * 1000, 0, $notice);
    } elseif ($_GET['step'] == 'prepare') {

        /*
        $usql = "RENAME TABLE " . DB::table('hdx_weapon1') . " TO " . DB::table('hdx_shop_item');
        if (!DB::query($usql, 'SILENT')) {
            throw new Exception('');
        }*/
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

        show_msg('准备完毕，进入下一步数据库结构升级', $theurl . '?step=sql');
    } elseif ($_GET['step'] == 'sql') {

        $sql = implode('', file($sqlfile));
        preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
        $newtables = empty($matches[1]) ? array() : $matches[1];
        $newsqls = empty($matches[0]) ? array() : $matches[0];
        if (empty($newtables) || empty($newsqls)) {
            show_msg('SQL文件内容为空，请确认');
        }

        $i = empty($_GET['i']) ? 0 : intval($_GET['i']);
        $count_i = count($newtables);
        if ($i >= $count_i) {
            show_msg('数据库结构升级完毕，进入下一步数据升级操作', $theurl . '?step=data');
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
                show_msg('添加表 ' . DB::table($newtable) . ' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>' . dhtmlspecialchars($usql));
            } else {
                $msg = '添加表 ' . DB::table($newtable) . ' 完成';
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
                                show_msg('升级表 ' . DB::table($newtable) . ' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . "</div><br><b>Error</b>: " . DB::error() . "<br><b>Errno.</b>: " . DB::errno());
                            } else {
                                $msg = '表改名 ' . DB::table($newtable) . ' 完成！';
                                show_msg($msg, $theurl . '?step=sql&i=' . $_GET['i']);
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
                    show_msg('升级表 ' . DB::table($newtable) . ' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . "</div><br><b>Error</b>: " . DB::error() . "<br><b>Errno.</b>: " . DB::errno());
                } else {
                    $msg = '升级表 ' . DB::table($newtable) . ' 完成！';
                }
            } else {
                $msg = '检查表 ' . DB::table($newtable) . ' 完成，不需升级，跳过';
            }
        }

        if ($specid) {
            $newtable = $spectable;
        }

        if (get_special_table_by_num($newtable, $specid + 1)) {
            $next = $theurl . '?step=sql&i=' . ($_GET['i']) . '&specid=' . ($specid + 1);
        } else {
            $next = $theurl . '?step=sql&i=' . ($_GET['i'] + 1);
        }
        show_msg("[ $i / $count_i ] " . $msg, $next);
    } elseif ($_GET['step'] == 'data') {



        $fromversionInt = intval(str_replace('.', '', $fromversion));

        $pluginId = intval($_GET['plugin_id']);

        // only do it when version less than 1.4.0
        if ($fromversionInt < 140 && $pluginId > 0 && $fromversion != '1.4') {

            // get sw ext value and copy it
            $swExtVal = DB::result_first("SELECT value FROM " . DB::table('common_pluginvar') . " WHERE pluginid='" . intval($pluginId) . "' AND variable = 'sw_ext'");


            $swSetNum = DB::result_first("SELECT COUNT(*) FROM " . DB::table('hdx_player') . " WHERE sw > 0");

            // has been set
            if (!empty($swExtVal) && $swSetNum == 0) {
                mysql_query("UPDATE " . DB::table('hdx_player') . " p SET sw = (SELECT extcredits" . intval($swExtVal) . " FROM " . DB::table('common_member_count') . " mc WHERE mc.uid=p.uid)");
            }


            // get sta value and copy it
            $staExtVal = DB::result_first("SELECT value FROM " . DB::table('common_pluginvar') . " WHERE pluginid='" . intval($pluginId) . "' AND variable = 'sta_ext'");

            $staSetNum = DB::result_first("SELECT COUNT(*) FROM " . DB::table('hdx_player') . " WHERE sta > 0");
            // has been set
            if (!empty($staExtVal) && $staSetNum == 0) {
                mysql_query("UPDATE " . DB::table('hdx_player') . " p SET sta = (SELECT extcredits" . intval($staExtVal) . " FROM " . DB::table('common_member_count') . " mc WHERE mc.uid=p.uid)");
            }

            // set weapon
            if (DB::fetch_first("SHOW COLUMNS FROM " . DB::table('hdx_shop_item') . " LIKE 'type'")) {
                DB::query("UPDATE " . DB::table('hdx_shop_item') . " SET `type`='weapon'");
            }
        }
        show_msg("数据处理完成", "$theurl?step=delete");
    } elseif ($_GET['step'] == 'delete') {

        if (!$devmode) {
            show_msg("数据删除不处理，进入下一步", "$theurl?step=cache");
        }

        $oldtables = array();
        $query = DB::query("SHOW TABLES LIKE '$config[tablepre]%'");
        while ($value = DB::fetch($query)) {
            $values = array_values($value);
            $oldtables[] = $values[0];
        }

        $sql = implode('', file($sqlfile));
        preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s+\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
        $newtables = empty($matches[1]) ? array() : $matches[1];

        $connecttables = array('common_member_connect', 'common_uin_black', 'connect_feedlog', 'connect_memberbindlog', 'connect_tlog', 'connect_tthreadlog', 'common_connect_guest', 'connect_disktask');

        $newsqls = empty($matches[0]) ? array() : $matches[0];

        $deltables = array();
        $delcolumns = array();

        foreach ($oldtables as $tname) {
            $tname = substr($tname, strlen($config['tablepre']));
            if (in_array($tname, $newtables)) {
                $query = DB::query("SHOW CREATE TABLE " . DB::table($tname));
                $cvalue = DB::fetch($query);
                $oldcolumns = getcolumn($cvalue['Create Table']);

                $i = array_search($tname, $newtables);
                $newcolumns = getcolumn($newsqls[$i]);

                foreach ($oldcolumns as $colname => $colstruct) {
                    if ($colname == 'UNIQUE' || $colname == 'KEY') {
                        foreach ($colstruct as $key_index => $key_value) {
                            if (empty($newcolumns[$colname][$key_index])) {
                                $delcolumns[$tname][$colname][$key_index] = $key_value;
                            }
                        }
                    } else {
                        if (empty($newcolumns[$colname])) {
                            $delcolumns[$tname][] = $colname;
                        }
                    }
                }
            } else {
                if (!strexists($tname, 'uc_') && !strexists($tname, 'ucenter_') && !preg_match('/forum_(thread|post)_(\d+)$/i', $tname) && !in_array($tname, $connecttables)) {
                    $deltables[] = $tname;
                }
            }
        }

        show_header();
        echo '<form method="post" autocomplete="off" action="' . $theurl . '?step=delete' . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . '">';

        $deltablehtml = '';
        if ($deltables) {
            $deltablehtml .= '<table>';
            foreach ($deltables as $tablename) {
                $deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td></tr>";
            }
            $deltablehtml .= '</table>';
            echo "<p>以下 <strong>数据表</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$deltablehtml";
        }

        $delcolumnhtml = '';
        if ($delcolumns) {
            $delcolumnhtml .= '<table>';
            foreach ($delcolumns as $tablename => $cols) {
                foreach ($cols as $coltype => $col) {
                    if (is_array($col)) {
                        foreach ($col as $index => $indexvalue) {
                            $delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$coltype][$index]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>索引($coltype) $index $indexvalue</td></tr>";
                        }
                    } else {
                        $delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>字段 $col</td></tr>";
                    }
                }
            }
            $delcolumnhtml .= '</table>';

            echo "<p>以下 <strong>字段</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$delcolumnhtml";
        }

        if (empty($deltables) && empty($delcolumns)) {
            echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"$theurl?step=cache" . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . "\">请点击进入下一步</a></p>";
        } else {
            echo "<p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段<br><a href=\"$theurl?step=cache" . ($_GET['from'] ? '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : '') . "\">直接进入下一步</a></p>";
        }
        echo '</form>';

        show_footer();
        exit();
    } elseif ($_GET['step'] == 'cache') {

        if (!$devmode && @$fp = fopen($lockfile, 'w')) {
            fwrite($fp, ' ');
            fclose($fp);
        }

        dir_clear(ROOT_PATH . './data/template');
        dir_clear(ROOT_PATH . './data/cache');
        dir_clear(ROOT_PATH . './data/threadcache');


        if ($_GET['from']) {
            show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=initsys" style="display:none;" onload="window.location.href=\'' . $_GET['from'] . '\'"></iframe>');
        } else {
            show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=initsys" style="display:none;" onload="document.getElementById(\'finalmsg\').innerHTML = \'恭喜，数据库结构升级完成！为了数据安全，请删除本文件。\'"></iframe>');
        }
    }
} catch (Exception $e) {
    if ($usql && $e->getMessage() == '') {
        $sqlmsg = '<font color=red>SQL语句执行出错, 请手工执行以下升级语句后, 再重新运行本升级程序:</font><br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">' . dhtmlspecialchars($usql) . "</div><br><b>Error</b>: " . DB::error() . "<br><b>Errno.</b>: " . DB::errno() . "<br>";
    } else {
        $sqlmsg = '<br><br><div style=\"position:absolute;background:#EBEBEB;padding:0.5em;\">错误原因： ' . $e->getMessage() . '</div>';
    }

    $msg = $sqlmsg . '<br>如果手工执行SQL升级语句后依然无法正常升级，请到插件官网(yulegame.net)发布你的错误信息以及留意是否有最新版的数据库升级文件下载（切勿随意卸载插件再重新安装，以免造成数据丢失）。';


    updateFailed($msg);
}

function updateFailed($errorMessage) {


    $error = "很抱歉，由于出现以下问题导致升级终止。<br><div style='color: #333333;
    float: left;
    font-size: 12px;
    font-weight: normal;
    line-height: 20px;
    margin-bottom: 20px;
    margin-top: 10px;
    width: 100%;'>" . $errorMessage . "</div>";

    show_msg($error, '', 'error');
}

function has_another_special_table($tablename, $key) {
    if (!$key) {
        return $tablename;
    }

    $tables_array = get_special_tables_array($tablename);

    if ($key > count($tables_array)) {
        return FALSE;
    } else {
        return TRUE;
    }
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

function get_special_table_by_num($tablename, $num) {
    $tables_array = get_special_tables_array($tablename);

    $num--;
    return isset($tables_array[$num]) ? $tables_array[$num] : FALSE;
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

function show_msg($message, $url_forward = '', $time = 1000, $noexit = 0, $notice = '') {

    if ($url_forward) {
        $url_forward = $_GET['from'] ? $url_forward . '&from=' . rawurlencode($_GET['from']) . '&frommd5=' . rawurlencode($_GET['frommd5']) : $url_forward;
        $message = "<a href=\"$url_forward\">$message (跳转中...)</a><br>$notice<script>setTimeout(\"window.location.href ='$url_forward';\", $time);</script>";
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

    $nowarr = array($_GET['step'] => ' class="current"');
    if (in_array($_GET['step'], array('waitingdb', 'prepare'))) {
        $nowarr = array('sql' => ' class="current"');
    }
    print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gbk" />
	<title> 悦影打劫系统数据库升级程序 </title>
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
	</head>
	<body>
	<div class="bodydiv">
	<h1>悦影打劫系统数据库升级工具</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>升级开始</td>
	<td{$nowarr[sql]}>数据库结构添加与更新</td>
	<td{$nowarr[data]}>数据更新</td>
	<td{$nowarr[delete]}>数据库结构删除</td>
	<td{$nowarr[cache]}>升级完成</td>
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
	<br>
	</body>
	</html>
END;
}

function runquery($sql) {
    global $_G;
    $tablepre = $_G['config']['db'][1]['tablepre'];
    $dbcharset = $_G['config']['db'][1]['dbcharset'];

    $sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' ' . $tablepre, ' ' . $tablepre, ' `' . $tablepre, ' ' . $tablepre, ' `' . $tablepre), $sql));
    $ret = array();
    $num = 0;
    foreach (explode(";\n", trim($sql)) as $query) {
        $queries = explode("\n", trim($query));
        foreach ($queries as $query) {
            $ret[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
        }
        $num++;
    }
    unset($sql);

    foreach ($ret as $query) {
        $query = trim($query);
        if ($query) {

            if (substr($query, 0, 12) == 'CREATE TABLE') {
                $name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
                DB::query(createtable($query, $dbcharset));
            } else {
                DB::query($query);
            }
        }
    }
}

function import_diy($importfile, $primaltplname, $targettplname) {
    global $_G;

    $css = $html = '';
    $arr = array();

    $content = file_get_contents(realpath($importfile));
    if (empty($content))
        return $arr;
    require_once DISCUZ_ROOT . './source/class/class_xml.php';
    $diycontent = xml2array($content);

    if ($diycontent) {

        foreach ($diycontent['layoutdata'] as $key => $value) {
            if (!empty($value))
                getframeblock($value);
        }
        $newframe = array();
        foreach ($_G['curtplframe'] as $value) {
            $newframe[] = $value['type'] . random(6);
        }

        $mapping = array();
        if (!empty($diycontent['blockdata'])) {
            $mapping = block_import($diycontent['blockdata']);
            unset($diycontent['blockdata']);
        }

        $oldbids = $newbids = array();
        if (!empty($mapping)) {
            foreach ($mapping as $obid => $nbid) {
                $oldbids[] = 'portal_block_' . $obid;
                $newbids[] = 'portal_block_' . $nbid;
            }
        }

        require_once DISCUZ_ROOT . './source/class/class_xml.php';
        $xml = array2xml($diycontent['layoutdata'], true);
        $xml = str_replace($oldbids, $newbids, $xml);
        $xml = str_replace((array) array_keys($_G['curtplframe']), $newframe, $xml);
        $diycontent['layoutdata'] = xml2array($xml);

        $css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
        $css = str_replace((array) array_keys($_G['curtplframe']), $newframe, $css);

        $arr['spacecss'] = $css;
        $arr['layoutdata'] = $diycontent['layoutdata'];
        $arr['style'] = $diycontent['style'];
        save_diy_data($primaltplname, $targettplname, $arr, true);
    }
    return $arr;
}

function save_config_file($filename, $config, $default, $deletevar) {
    $config = setdefault($config, $default, $deletevar);
    $date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
    $content = <<<EOT
<?php


\$_config = array();

EOT;
    $content .= getvars(array('_config' => $config));
    $content .= "\r\n// " . str_pad('  THE END  ', 50, '-', STR_PAD_BOTH) . " //\r\n\r\n?>";
    if (!is_writable($filename) || !($len = file_put_contents($filename, $content))) {
        file_put_contents(DISCUZ_ROOT . './data/config_global.php', $content);
        return 0;
    }
    return 1;
}

function setdefault($var, $default, $deletevar) {
    foreach ($default as $k => $v) {
        if (!isset($var[$k])) {
            $var[$k] = $default[$k];
        } elseif (is_array($v)) {
            $var[$k] = setdefault($var[$k], $default[$k]);
        }
    }
    foreach ($deletevar as $k) {
        unset($var[$k]);
    }
    return $var;
}

function getvars($data, $type = 'VAR') {
    $evaluate = '';
    foreach ($data as $key => $val) {
        if (!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
            continue;
        }
        if (is_array($val)) {
            $evaluate .= buildarray($val, 0, "\${$key}") . "\r\n";
        } else {
            $val = addcslashes($val, '\'\\');
            $evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('" . strtoupper($key) . "', '$val');\n";
        }
    }
    return $evaluate;
}

function buildarray($array, $level = 0, $pre = '$_config') {
    static $ks;
    if ($level == 0) {
        $ks = array();
        $return = '';
    }

    foreach ($array as $key => $val) {
        if ($level == 0) {
            $newline = str_pad('  CONFIG ' . strtoupper($key) . '  ', 70, '-', STR_PAD_BOTH);
            $return .= "\r\n// $newline //\r\n";
            if ($key == 'admincp') {
                $newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
                $return .= "// $newline //\r\n";
            }
        }

        $ks[$level] = $ks[$level - 1] . "['$key']";
        if (is_array($val)) {
            $ks[$level] = $ks[$level - 1] . "['$key']";
            $return .= buildarray($val, $level + 1, $pre);
        } else {
            $val = is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\'' . addcslashes($val, '\'\\') . '\'' : $val;
            $return .= $pre . $ks[$level - 1] . "['$key']" . " = $val;\r\n";
        }
    }
    return $return;
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

function block_conver_to_thread($block) {
    if ($block['blockclass'] == 'forum_attachment') {
        $block['blockclass'] = 'forum_thread';
        $block['script'] = 'thread';
    } else if ($block['blockclass'] == 'group_attachment') {
        $block['blockclass'] = 'group_thread';
        $block['script'] = 'groupthread';
    }
    $block['param'] = is_array($block['param']) ? $block['param'] : (array) dunserialize($block['param']);
    unset($block['param']['threadmethod']);
    $block['param']['special'] = array(0);
    $block['param']['picrequired'] = 1;
    $block['param'] = serialize($block['param']);
    $block['styleid'] = 0;
    $block['blockstyle'] = block_style_conver_to_thread($block['blockstyle'], $block['blockclass']);
    return $block;
}

function block_style_conver_to_thread($style, $blockclass) {
    $template = block_build_template($style['template']);
    $search = array('threadurl', 'threadsubject', 'threadsummary', 'filesize', 'downloads');
    $replace = array('url', 'title', 'summary', '');
    $template = str_replace($search, $replace, $template);
    $arr = array(
        'name' => '',
        'blockclass' => $blockclass,
    );
    block_parse_template($template, $arr);
    $arr['fields'] = dunserialize($arr['fields']);
    $arr['template'] = dunserialize($arr['template']);
    $arr = serialize($arr);
    return $arr;
}

function waitingdb($curstep, $sqlarray) {
    global $theurl;
    foreach ($sqlarray as $key => $sql) {
        $sqlurl .= '&sql[]=' . md5($sql);
        $sendsql .= '<img width="1" height="1" src="' . $theurl . '?step=' . $curstep . '&waitingdb=1&sqlid=' . $key . '">';
    }
    show_msg("优化数据表", $theurl . '?step=waitingdb&nextstep=' . $curstep . $sqlurl . '&sendsql=' . base64_encode($sendsql), 5000, 1);
}

?>