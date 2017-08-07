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

class plugin_zeroze007_hdx {

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

class plugin_zeroze007_hdx_forum extends plugin_zeroze007_hdx {

    function viewthread_sidebottom_output() {
        global $_G, $postlist;

        $_hdx = $this->_hdx;
        if ($_hdx['rob_in_thread']) {

            $robLang = trim($_hdx['lang_rob']) == '' ? lang('plugin/zeroze007_hdx', 'rob') : $_hdx['lang_rob'];
            $robNotLoginMemberTime = floatval($_hdx['rob_not_login_member_time']) * 3600;
            $returnAry = array();
            $showHdxLink = false;
            foreach ($postlist as $pid => $post) {
                if ($post['uid'] > 0) {
                    $victimPlayerUid = DB::result_first('SELECT uid FROM ' . DB::table('hdx_player') . ' WHERE uid=' . $post['uid']);
                    $lastVisit = DB::result_first('SELECT lastvisit FROM ' . DB::table('common_member_status') . ' WHERE uid=' . $post['uid']);


                    if ($post['authorid'] != $_G['uid']) {
                        if ($victimPlayerUid > 0 || $_hdx['rob_anyone'] || (!$_hdx['rob_anyone'] && !$victimPlayerUid && $lastVisit > 0 && ($_G['timestamp'] - $lastVisit) > $robNotLoginMemberTime && $robNotLoginMemberTime > 0)) {
                            $returnAry[] = '<ul class="xl xl2 o cl"><li class="rob"><img src="source/plugin/zeroze007_hdx/images/rob_icon.png" align=absmiddle> <a id="rob_user_link_' . $pid . '" class="xi2 rob-user" title="' . $robLang . 'Ta"  onclick="showWindow(this.id, this.href, \'get\', 0);" href="plugin.php?id=zeroze007_hdx&op=ajax&subop=rob&pickType=2&memberUid=' . $post[authorid] . '&showmessage=1&robsubmit=yes&handlekey=favoriteforum&formhash=' . formhash() . '">' . $robLang . 'Ta</a></li></ul>';
                            $showHdxLink = true;
                        } else {
                            //$returnAry[] = $post['authorid'] .'-aa'. ($_G['timestamp'] - $lastVisit) .'>'. $robNotLoginMemberTime;
                            $returnAry[] = '';
                        }
                    } else {
                        $returnAry[] = '';
                    }
                } else {
                    $returnAry[] = '';
                }
            }

            if (count($returnAry) > 0 && $showHdxLink == true) {
                $returnAry[0] = '<style>.pls .o li.rob { text-indent: 0px}</style>' . $returnAry[0];
            }
            //		$poster = reset($postlist);
            return $returnAry;
        }
    }

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

            $allowView = false;

            // 不受限制的用户组
            $allowedGroupList = $_hdx['jail_allow_post_group_list'];
            $allowedGroupAry = unserialize($allowedGroupList);
            if (in_array($_G['groupid'], $allowedGroupAry)) {
                $allowed = true;
            }



            // 不允许发帖
            if ($_player['out_jail_time'] > $_timenow && $_player['out_jail_time'] > 0 && $allowed == false) {
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


        // 不受限制的用户组
        $allowedGroupList = $_hdx['jail_allow_view_group_list'];
        $allowedGroupAry = unserialize($allowedGroupList);
        if (in_array($_G['groupid'], $allowedGroupAry)) {
            $allowed = true;
        }

        if (!$_hdx['jail_allow_view']) {
            // 不允许发帖
            if ($_player['out_jail_time'] > $_timenow && $_player['out_jail_time'] > 0 && $allowed == false) {
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