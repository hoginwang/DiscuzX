<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member.php 31849 2012-10-17 04:39:16Z zhangguosheng $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_plugin extends discuz_table
{
	public function __construct() {
		$this->_table = 'common_member_plugin';

		parent::__construct();
	}

	public function get_by_loginid($identifier, $loginid) {
		$pluginid = $this->_get_pluginid($identifier);
		if (!$pluginid) {
			return array();
		}
		return DB::fetch_first("SELECT * FROM %t WHERE loginid=%s AND pluginid=%d", array($this->_table, $loginid, $pluginid));
	}

	public function get_by_uid($identifier, $uid) {
		$pluginid = $this->_get_pluginid($identifier);
		if (!$pluginid) {
			return array();
		}
		return DB::fetch_first("SELECT * FROM %t WHERE uid=%s AND pluginid=%d", array($this->_table, $uid, $pluginid));
	}

	public function delete_by_loginid($identifier, $loginid) {
		$pluginid = $this->_get_pluginid($identifier);
		if (!$pluginid) {
			return array();
		}
		return DB::query("DELETE FROM %t WHERE loginid=%s AND pluginid=%d", array($this->_table, $loginid, $pluginid));
	}

	public function delete_by_uid($identifier, $uid) {
		$pluginid = $this->_get_pluginid($identifier);
		if (!$pluginid) {
			return array();
		}
		return DB::query("DELETE FROM %t WHERE uid=%s AND pluginid=%d", array($this->_table, $uid, $pluginid));
	}

	public function update_by_loginid($identifier, $loginid, $data) {
		$pluginid = $this->_get_pluginid($identifier);
		if (!$pluginid) {
			return false;
		}
		return DB::UPDATE($this->_table, $data, array('loginid' => $loginid, 'pluginid' => $pluginid), 'UNBUFFERED');
	}

	public function update_by_uid($identifier, $uid, $data) {
		$pluginid = $this->_get_pluginid($identifier);
		if (!$pluginid) {
			return false;
		}
		return DB::UPDATE($this->_table, $data, array('uid' => $uid, 'pluginid' => $pluginid), 'UNBUFFERED');
	}

	public function register($identifier, $uid, $loginid) {
		$pluginid = $this->_get_pluginid($identifier);
		if (!$pluginid) {
			return false;
		}
		return DB::insert($this->_table, array(
			'uid' => $uid,
			'pluginid' => $pluginid,
			'loginid' => $loginid,
			'regdate' => TIMESTAMP,
			'status' => 0,
		), false, true);
	}

	private function _get_pluginid($identifier) {
		global $_G;
		if (!empty($_G['setting']['plugins']['id'][$identifier])) {
			return $_G['setting']['plugins']['id'][$identifier];
		}
		return 0;
	}
}