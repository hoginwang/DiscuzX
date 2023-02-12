<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT . './source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

$setting = C::t('common_setting')->fetch_all(array('mobilewechat'));
$setting = (array)dunserialize($setting['mobilewechat']);

cpmsg(lang('plugin/wechat', 'wsq_stat_close'), '', 'error');