<?php

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', '1');

$plugin_identifier = 'zeroze007_hdx';

try {

    $theurl = ADMINSCRIPT . '?action=plugins&operation=' . $operation . '&pluginid=' . $pluginid . '&dir=' . $dir . '&installtype=' . $installtype;

    if (empty($_GET['step'])) {
        $_GET['step'] = 'start';
    }
    switch ($operation) {
        case 'import':

            $tablepre = $_G['config']['db'][1]['tablepre'];
            // check if there is hdx tables in database already.
            
            $query = DB::query("SHOW TABLES LIKE '" . $tablepre . "hdx_%'");
            $hdxTables = array();
            while($data = DB::fetch($query)) {
                $hdxTables[] = $data;
            }
            
            
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if ($_POST['force_install'] != '1' && $hdxTables) {
                    throw new Exception($installlang['need_to_force_install'] ."<br><br><form action='" . $theurl . "' method=post><input type=checkbox value=1 name=force_install> ". $installlang['need_to_force_install'] ."<div class='next-step'><input type=submit value='". $installlang['next_step'] ."'></form>");
                }

                $sqlAry = array();
                foreach ($hdxTables as $t) {
                    $sqlAry[] = "DROP TABLE IF EXISTS " . implode('', $t) . ";";
                }
                $sql = implode("\n", $sqlAry);

                runquery($sql);
            } else {
                if ($hdxTables) {
                    throw new Exception($installlang['plugin_db_table_exists'] . "<br><br><form action='" . $theurl . "' method=post><input type=checkbox value=1 name=force_install> ". $installlang['need_to_force_install'] ."<div class='next-step'><input type=submit value='". $installlang['netx_step'] ."'></form></div>");
                }
            }



            break;
        case 'upgrade':

            // plugin if exists
            $yulegame_plugin = DB::fetch_first("SELECT available,name, pluginid, modules FROM " . DB::table('common_plugin') . " WHERE identifier='{$plugin_identifier}' LIMIT 1");
            if (!$yulegame_plugin) {
                throw new Exception($installlang['plugin_not_exist']);
            }


            break;
        case 'delete':
            if (empty($_GET['confirmed'])) {
                show_confirm($installlang['plugin_uninstall_warning_title'],$installlang['plugin_uninstall_warning_content'] .'<form method="post" action="' . $theurl . '&confirmed=yes"><input type="hidden" name="formhash" value="' . FORMHASH . '"><input type="submit"  name="confirmed" value="确定"> &nbsp;<script type="text/javascript">if(history.length > (BROWSER.ie ? 0 : 1)) document.write(\'<input type="button" value="取消" onClick="history.go(-1);">\');</script></form>');
            }
            break;

        default:
    }
} catch (DbException $e) {
    updateFailed($e->getMessage() . '<br>SQL: ' . $e->sql, $truncateTable);
} catch (Exception $e) {
    updateFailed($e->getMessage(), $truncateTable);
}


function show_confirm($header, $message) {
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
input[type=submit],input[type=button] {
cursor: pointer;
}
div.next-step {
 margin-top: 20px;       
}
span.alert {
color: red;
}
	</style>
        

	<div class="bodydiv">
<h1>$header</h1>
	<div style="width:90%;margin:0 auto;">
	<br>
        <table>
	<tr><td>$message</td></tr>
	</table>
END;
    show_footer();
    !$noexit && exit();
}

function updateFailed($errorMessage) {
    global $installlang, $_G;
    $plugin_identifier = 'zeroze007_hdx';

    if ($truncateTable) {
        // plugin if installed
        $plugin = DB::fetch_first("SELECT available,name, pluginid, modules FROM " . DB::table('common_plugin') . " WHERE identifier='{$plugin_identifier}' LIMIT 1");
        if ($plugin) {
            DB::query("DELETE FROM " . DB::table('common_pluginvar') . " WHERE pluginid='$plugin[pluginid]'");
            DB::query("DELETE FROM " . DB::table('common_plugin') . " WHERE pluginid='$plugin[pluginid]'");
        }
        $tablepre = $_G['config']['db'][1]['tablepre'];

            $query = DB::query("SHOW TABLES LIKE '" . $tablepre . "hdx_%'");
            $hdxTables = array();
            while($data = DB::fetch($query)) {
                $hdxTables[] = $data;
            }
        $sqlAry = array();
        foreach ($hdxTables as $t) {
            $sqlAry[] = "DROP TABLE IF EXISTS " . implode('', $t) . ";";
        }
        $sql = implode("\n", $sqlAry);

        runquery($sql);
    }

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

function show_msg($message, $url_forward = '', $time = 3000, $noexit = 0, $notice = '') {

    if ($url_forward) {
        $message = "<a href=\"$url_forward\">$message (". $installlang['redirecting_now'] .")</a><br>$notice<script>setTimeout(\"window.location.href ='$url_forward';\", $time);</script>";
        //$message = "<a href=\"$url_forward\">$message (跳转中...)</a>";
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
        div.next-step {
 margin-top: 20px;       
}
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

?>