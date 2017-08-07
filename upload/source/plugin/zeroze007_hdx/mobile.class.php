<?php

/**
 * [Discuz!] (C)2001-2099 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 *
 * $Id: myrepeats.class.php 21730 2011-04-11 06:23:46Z lifangming $
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', '1');

class mobileplugin_zeroze007_hdx {

	public $_hdx;


	function plugin_zeroze007_hdx() {
        // 黑道设置
        $_hdx = array();
        $query = DB::query("SELECT * FROM " . DB::table('hdx_setting'));

        while ($setting = DB::fetch($query)) {
            $key = $setting['skey'];
            $_hdx[$key] = $setting['svalue'];
        }
		
		
		$this->_hdx = $_hdx;
    }

}

class mobileplugin_zeroze007_hdx_forum extends mobileplugin_zeroze007_hdx {



    function post_thread() {
        global $_G;
        if (!$_G['uid']) {
            return;
        }

        $_player = DB::fetch_first("SELECT * FROM " . DB::table('hdx_player') . " WHERE uid=" . $_G['uid']);

        if (!$_player) {
            return;
        }

        $_timenow = $_SERVER['REQUEST_TIME'];
        // 发帖前
        // 黑道设置
        $_hdx = $this->_hdx;

        if (!$_hdx['jail_allow_post']) {
            // 不允许发帖
            if ($_player['out_jail_time'] > $_timenow && $_player['out_jail_time'] > 0) {
               showmessage(lang('plugin/zeroze007_hdx', 'still_in_jail', array(
                            'month' => date('n', $_player['out_jail_time']),
                            'day' => date('j', $_player['out_jail_time']),
                            'hour' => date('H', $_player['out_jail_time']),
                            'minute' => date('i', $_player['out_jail_time']))));
            }
        }
    }

    function viewthread_zeroze007_hdx() {
        global $_G;
        if (!$_G['uid']) {
            return;
        }

        $_player = DB::fetch_first("SELECT * FROM " . DB::table('hdx_player') . " WHERE uid=" . $_G['uid']);

        if (!$_player) {
            return;
        }

        $_timenow = $_SERVER['REQUEST_TIME'];
        // 发帖前
        // 黑道设置
        $_hdx = $this->_hdx;

        if (!$_hdx['jail_allow_view']) {
            // 不允许发帖
            if ($_player['out_jail_time'] > $_timenow && $_player['out_jail_time'] > 0) {
              showmessage(lang('plugin/zeroze007_hdx', 'still_in_jail', array(
                            'month' => date('n', $_player['out_jail_time']),
                            'day' => date('j', $_player['out_jail_time']),
                            'hour' => date('H', $_player['out_jail_time']),
                            'minute' => date('i', $_player['out_jail_time']))));
            }
        }
    }

}

?>