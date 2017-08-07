<?php

/**
 * [Discuz!] (C)2001-2099 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 *
 * $Id: uninstall.php 6752 2010-03-25 08:47:54Z cnteacher $
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

$tablepre = $_G['config']['db'][1]['tablepre'];

$query = DB::query("SHOW TABLES LIKE '" . $tablepre . "hdx_%'");
$pluginTables = array();
while ($data = DB::fetch($query)) {
    $pluginTables[] = $data;
}
$sqlAry = array();
foreach ($pluginTables as $t) {
    $sqlAry[] = "DROP TABLE IF EXISTS " . implode('', $t) . ";";
}
$sql = implode("\n", $sqlAry);

runquery($sql);

$finish = TRUE;