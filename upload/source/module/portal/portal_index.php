<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_index.php 31313 2012-08-10 03:51:03Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

list($navtitle, $metadescription, $metakeywords) = get_seosetting('portal');
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][1]['navname'];
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metakeywords) {
	$metakeywords = $_G['setting']['navs'][1]['navname'];
}
if(!$metadescription) {
	$metadescription = $_G['setting']['navs'][1]['navname'];
}

if(@in_array('portal_index', $_G['setting']['rewritestatus'])) {
	$canonical = rewriteoutput('portal_index', 1, '', '', '', '', '');
} else {
	$canonical = 'portal.php';
}
$_G['setting']['seohead'] .= '<link href="'.$_G['siteurl'].$canonical.'" rel="canonical" />';

if(isset($_G['makehtml'])){
	helper_makehtml::portal_index();
}

include_once template('diy:portal/index');
?>