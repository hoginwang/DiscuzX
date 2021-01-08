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

if(!$_G['uid'] && !$gid && $_G['setting']['cacheindexlife'] && !defined('IN_ARCHIVER') && !defined('IN_MOBILE')) {
    get_index_page_guest_cache();
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

if(isset($_G['makehtml'])){
    helper_makehtml::portal_index();
}
//var_dump(function_exists('get_index_page_guest_cache'));exit();
include_once template('diy:portal/index');

//ob_get_clean();

function getcacheinfo($tid) {
    global $_G;
    $tid = intval($tid);
    $cachethreaddir2 = DISCUZ_ROOT.'./'.$_G['setting']['cachethreaddir'];
    $cache = array('filemtime' => 0, 'filename' => '');
    $tidmd5 = substr(md5($tid), 3);
    $fulldir = $cachethreaddir2.'/'.$tidmd5[0].'/'.$tidmd5[1].'/'.$tidmd5[2].'/';
    $cache['filename'] = $fulldir.$tid.'.htm';
    if(file_exists($cache['filename'])) {
        $cache['filemtime'] = filemtime($cache['filename']);
    } else {
        if(!is_dir($fulldir)) {
            dmkdir($fulldir);
        }
    }
    return $cache;
}

function replace_formhash($timestamp, $input) {
    global $_G;
    $temp_formhash = substr(md5(substr($timestamp, 0, -3).substr($_G['config']['security']['authkey'], 3, -3)), 8, 8);
    $formhash = constant("FORMHASH");
    return preg_replace('/(name=[\'|\"]formhash[\'|\"] value=[\'\"]|formhash=)'.$temp_formhash.'/ismU', '${1}'.$formhash, $input);
}

function get_index_page_guest_cache() {
    global $_G;
    $indexcache = getcacheinfo(-1);//用-1表示门户 0表示论坛
    if(TIMESTAMP - $indexcache['filemtime'] > $_G['setting']['cacheindexlife']) {
        @unlink($indexcache['filename']);
        define('CACHE_FILE', $indexcache['filename']);
    } elseif($indexcache['filename']) {
        $start_time = microtime(TRUE);
        $filemtime = $indexcache['filemtime'];
        ob_start(function($input) use (&$filemtime) {
            return replace_formhash($filemtime, $input);
        });
        readfile($indexcache['filename']);
        $updatetime = dgmdate($filemtime, 'Y-m-d H:i:s');
        $gzip = $_G['gzipcompress'] ? ', Gzip On' : '';
        echo '<script type="text/javascript">$("debuginfo") ? $("debuginfo").innerHTML = ", Updated at '.$updatetime.', Processed in '.sprintf("%0.6f", microtime(TRUE) - $start_time).' second(s)'.$gzip.'." : "";</script></body></html>';
        ob_end_flush();
        exit();
    }
}
