<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_check.php 672 2020-04-04 10:30:00Z community $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_check {

	public static function check_avatar($uid) {
		$member = getuserbyuid($uid);
		if($member['avatarstatus']) {
			return true;
		} else {
			loaducenter();
			if(uc_check_avatar($uid, 'middle')) {
				C::t('common_member')->update($uid, array('avatarstatus' => '1'));
				updatecreditbyaction('setavatar');
				return true;
			} else {
				return false;
			}
		}
	}

}

?>