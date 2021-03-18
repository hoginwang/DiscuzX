<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

// discuz_cron::run() 运行会更新下次运行时间，因此此处重新读取一次数据库获得 cron 的最新状态
$newCron = C::t('common_cron')->fetch($cron['cronid']);

if ($newCron && $newCron['available']) {
	C::t('common_task')->update_available($newCron['nextrun']);
	C::t('common_task')->update_unavailable($newCron['nextrun']);
}