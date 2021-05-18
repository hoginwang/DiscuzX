<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_wechatpay.php 33625 2021-05-10 06:03:49Z aphly $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pay_wechat = new pay_wechat;
$pay_wechat->getCertificates();