<?php

class magic_rename_card {

    var $version = '1.0';
	var $name = 'rename_card_name';
	var $description = 'rename_card_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 1;
	var $targetgroupperm = false;
	var $copyright = 'Chino';
	var $magic = array();
    var $parameters = array();
    
    function getsetting(&$magic) {}

    function setsetting(&$magicnew, &$parameters) {}

    function usesubmit() {
        global $_G;

        $new_username = trim($_G["gp_new_username"]);
        if (empty($new_username)) {
            showmessage(lang('magic/rename_card', 'rename_card_empty_input'));
        }

        $query = DB::query("SELECT uid FROM ".DB::table('ucenter_members')." WHERE username ='$new_username'");
        if (DB::num_rows($query)) {
            showmessage(lang('magic/rename_card', 'rename_card_username_exists'));
        } else {
            $tables = array(
                'ucenter_members' => array('id' => 'uid', 'name' => 'username'),
    
                'common_block' => array('id' => 'uid', 'name' => 'username'),
                'common_invite' => array('id' => 'fuid', 'name' => 'fusername'),
                'common_member' => array('id' => 'uid', 'name' => 'username'),
                'common_member_security' => array('id' => 'uid', 'name' => 'username'),
                'common_mytask' => array('id' => 'uid', 'name' => 'username'),
                'common_report' => array('id' => 'uid', 'name' => 'username'),
    
                'forum_thread' => array('id' => 'authorid', 'name' => 'author'),
                'forum_post' => array('id' => 'authorid', 'name' => 'author'),
                'forum_activityapply' => array('id' => 'uid', 'name' => 'username'),
                'forum_groupuser' => array('id' => 'uid', 'name' => 'username'),
                'forum_pollvoter' => array('id' => 'uid', 'name' => 'username'),
                'forum_postcomment' => array('id' => 'authorid', 'name' => 'author'),
                'forum_ratelog' => array('id' => 'uid', 'name' => 'username'),
    
                'home_album' => array('id' => 'uid', 'name' => 'username'),
                'home_blog' => array('id' => 'uid', 'name' => 'username'),
                'home_clickuser' => array('id' => 'uid', 'name' => 'username'),
                'home_docomment' => array('id' => 'uid', 'name' => 'username'),
                'home_doing' => array('id' => 'uid', 'name' => 'username'),
                'home_feed' => array('id' => 'uid', 'name' => 'username'),
                'home_feed_app' => array('id' => 'uid', 'name' => 'username'),
                'home_friend' => array('id' => 'fuid', 'name' => 'fusername'),
                'home_friend_request' => array('id' => 'fuid', 'name' => 'fusername'),
                'home_notification' => array('id' => 'authorid', 'name' => 'author'),
                'home_pic' => array('id' => 'uid', 'name' => 'username'),
                'home_poke' => array('id' => 'fromuid', 'name' => 'fromusername'),
                'home_share' => array('id' => 'uid', 'name' => 'username'),
                'home_show' => array('id' => 'uid', 'name' => 'username'),
                'home_specialuser' => array('id' => 'uid', 'name' => 'username'),
                'home_visitor' => array('id' => 'vuid', 'name' => 'vusername'),
    
                'portal_article_title' => array('id' => 'uid', 'name' => 'username'),
                'portal_comment' => array('id' => 'uid', 'name' => 'username'),
                'portal_topic' => array('id' => 'uid', 'name' => 'username'),
                'portal_topic_pic' => array('id' => 'uid', 'name' => 'username'),
            );

            foreach ($tables as $table => $conf) {
                DB::query("UPDATE ".DB::table($table)." SET `$conf[name]`='$new_username' WHERE `$conf[id]`='$_G[uid]'");
            }

            usemagic($this->magic['magicid'], $this->magic['num']);
            updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'uid', $_G['uid']);
            showmessage(lang('magic/rename_card', 'rename_card_rename_succeed'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => 1));
        }
    }

    function show() {
        magicshowtype('top');
        magicshowsetting(lang('magic/rename_card', 'rename_card_input_new_username'), 'new_username', '', 'text');
        magicshowtype('bottom');
    }
}

?>
