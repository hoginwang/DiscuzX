<?php
/*
	Install Uninstall Upgrade AutoStat System Code
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
//start to put your own code 
$sql = <<<EOF

DROP TABLE IF EXISTS `pre_diy007_code`;
CREATE TABLE `pre_diy007_code` (
  `uid` int(10) unsigned NOT NULL,
  `Code` varchar(255) NOT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `Result` tinyint(1) unsigned NOT NULL,
  `UploaderPrefix` tinyint(1) unsigned NOT NULL,
  `DownloaderPrefix` tinyint(1) unsigned NOT NULL,
  `UploaderPrefixName` varchar(255) DEFAULT NULL,
  `DownloaderPrefixName` varchar(255) DEFAULT NULL,
  `Remark` varchar(255) DEFAULT NULL,
  `Dateline` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

EOF;
runquery($sql);	

//finish to put your own code
$finish = TRUE;
?>