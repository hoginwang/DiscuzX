<?php
/**
 *    [个性化(zeroze007diy.{modulename})] (C)2016-2099 Powered by jiangyou.
 *    Version: 1.0
 *    Date: 2016-3-12 14:24
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
$code = !empty($_G['gp_code']) ? $_G['gp_code'] : base64_encode(authcode($_G['siteurl'], 'ENCODE'));
if ($url = authcode(base64_decode($code), 'DECODE')) {
    if (!$_G['uid']) {
        showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
    }
    $link_fileter_url = array(
        'uid' => $_G['uid'],
        'username' => $_G['username'],
        'url' => $url,
        'referer' => $_SERVER['HTTP_REFERER'],
        'dateline' => TIMESTAMP
    );
    C::t('#zeroze007diy#link_fileter_url')->insert($link_fileter_url);
    /*
    if (preg_match('/^https?:\/\//is', $url)) {
        dheader("Location: {$url}");
    } else {
        dheader("Location: http://{$url}");
    }
    */
    if (!preg_match('/^https?:\/\//is', $url)) {
        dheader("Location: {$url}");
        $url = "http://{$url}";
    }
    include template('zeroze007diy:linkloading');
} else {
    dheader('Location: ' . $_G['siteurl']);
}
?>