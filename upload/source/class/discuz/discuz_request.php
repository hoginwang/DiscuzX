<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_request
{

	public static function g($k) {
		return isset($_GET[$k]) ? $_GET[$k] : null;
	}

	public static function p($k) {
		return isset($_POST[$k]) ? $_POST[$k] : null;
	}

	public static function gp($k) {
		return isset($_GET[$k]) ? $_GET[$k] : (isset($_POST[$k]) ? $_POST[$k] : null);
	}

}