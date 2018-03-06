<?php
/**
 *    [个性化(zeroze007diy.{modulename})] (C)2016-2099 Powered by jiangyou.
 *    Version: 1.0
 *    Date: 2016-3-12 14:24
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function tpl_hide_reply2()
{
    $return = "<div class=\"showhide\"><h4>本帖隐藏的内容</h4>\\1</div>";
    return $return;
}

class plugin_zeroze007diy
{

    function _hidepermit($permitrequire, $message)
    {
        global $_G;
        if ($_G['forum']['ismoderator'] || $_G['group']['readaccess'] >= $permitrequire) {
            $msg = "<div class='locked'>&#38544;&#34255;&#20869;&#23481;&#24050;&#26174;&#31034;</div>";
            $msg .= str_replace('\\"', '"', $message);
            $msg .= "<br/>";
            return $msg;
        } else {
            return "<div class='locked'>&#20197;&#19979;&#20869;&#23481;&#38656;&#35201;&#38405;&#35835;&#26435;&#38480;&#39640;&#20110;{$permitrequire}&#25165;&#21487;&#38405;&#35835;</div>";
        }
    }

    function _creditshide($creditsrequire, $message, $pid, $authorid)
    {
        global $_G;
        if ($_G['member']['credits'] >= $creditsrequire || $_G['forum']['ismoderator'] || in_array($_G['adminid'], array(1, 2, 3)) || $_G['uid'] && $authorid == $_G['uid']) {
            return tpl_hide_credits($creditsrequire, str_replace('\\"', '"', $message));
        } else {
            return tpl_hide_credits_hidden($creditsrequire);
        }
    }

    function _parseurl($url, $text, $scheme)
    {
        global $_G;
        if (!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
            $url = $matches[0];
            $length = 65;
            if (strlen($url) > $length) {
                $text = substr($url, 0, intval($length * 0.5)) . ' ... ' . substr($url, -intval($length * 0.3));
            }
            return plugin_zeroze007diy::_filterurl(substr(strtolower($url), 0, 4) == 'www.' ? 'http://' . $url : $url, $text);
        } else {
            $url = substr($url, 1);
            if (substr(strtolower($url), 0, 4) == 'www.') {
                $url = 'http://' . $url;
            }
            $url = !$scheme ? $_G['siteurl'] . $url : $url;
            return plugin_zeroze007diy::_filterurl($url, $text);
        }
    }

    function _filterurl($url, $title = '&#38142;&#25509;&#60;&#60;', $onlytext)
    {
        global $_G;
        $url = htmlspecialchars_decode(trim($url));
        $disable = Array(
            "400gb.com/",
            "t00y.com/",
            "ctdisk.com/",
            "dd.ma/",
            "colafile.com/"
        );
        foreach ($disable as $d) {
            if (strpos($url, $d)) {
                $title = '&#38750;&#27861;&#38142;&#25509;';
                $url = '#';
                return '<a href="' . ($url) . '" target="_blank">' . ($title) . '</a>';
            }
        }
        $keys = array(
            'pan.baidu.com/' => '&#30334;&#24230;&#20113;',
            '115.com/lb' => '&#49;&#49;&#53;&#31036;&#21253;',
            'kuai.xunlei.com/' => '&#36805;&#38647;&#24555;&#20256;',
            'yunpan.cn/' => '&#51;&#54;&#48;&#20113;&#30424;',
            'caiyun.feixin.10086.cn/' => '&#21644;&#24425;&#20113;&#20998;&#20139;',
            'cloud.letv.com/' => '&#20048;&#35270;&#20113;&#30424;',
            '.vmall.com/' => '&#21326;&#20026;&#32593;&#30424;',
            'www.dbank.com/' => '&#21326;&#20026;&#32593;&#30424;',
            'drive.google.com/' => '&#71;&#111;&#111;&#103;&#108;&#101;&#20113;&#31471;&#30828;&#30424;'
        );

        foreach ($keys as $k => $v) {
            if (strpos($url, $k)) {
                $title = $v;
                $url = $_G['siteurl'] . 'plugin.php?id=zeroze007diy:linkFileter&code=' . base64_encode(authcode($url, 'ENCODE'));
                break;
            }
        }

        return '<a href="' . ($url) . '" target="_blank">' . (isset($onlytext) && !empty($onlytext) ? $onlytext : $title) . '</a>';
    }

    public static function _createValidateCode()
    {
        global $_G;
        include_once DISCUZ_ROOT . '/source/plugin/zeroze007diy/aes.class.php';
        $return = '&#39564;&#35777;&#30721;&#65306;';
        if ($_G['uid']) {
            $aes = new AES();
            $return .= '<input style="width:200px;" value="' . $aes->encrypt($_G['uid'] . '|' . time()) . '"><font color="red">*&#20999;&#21247;&#27844;&#38706;*</font>';
        } else {
            $return .= '<font color="red">*&#35831;&#20808;&#30331;&#24405;*</font>';
        }
        return $return;
    }

    function discuzcode($val)
    {
        global $_G, $pid;

        loadcache('plugin');
        $cacheVar = $_G['cache']['plugin']['zeroze007diy'];
        $exemptHideGroups = unserialize($cacheVar['exemptHideGroups']);
        $exemptHideUids = explode('/', $cacheVar['exemptHideUids']);

        static $authorreplyexist;

        $message = $_G['discuzcodemessage'];
        $msglower = strtolower($message);
        if (strpos($msglower, '[/url]') !== false) {
            $message = preg_replace_callback("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/is", create_function('$matches', 'return plugin_zeroze007diy::_parseurl($matches[1], $matches[5], $matches[2]);'), $message);
        }
        //validateCode
        if (strpos($msglower, '[validatecode/]') !== false) {
            $message = preg_replace_callback("/\[validateCode\/\]/is", 'self::_createValidateCode', $message);
        }
        $message = preg_replace_callback('/((http|https)\:\/\/)?pan\.baidu.com\/(s|share|pcloud)\/([\w\/\?\=&_;\-]*)/is', create_function('$matches', 'return plugin_zeroze007diy::_filterurl("$matches[1]pan.baidu.com/$matches[3]/$matches[4]");'), $message);
        $message = preg_replace_callback('/((http|https)\:\/\/)?(yunpan\.cn\/)(\w*)/is', create_function('$matches', 'return plugin_zeroze007diy::_filterurl("$matches[1]yunpan.cn/$matches[4]");'), $message);
        $message = preg_replace_callback('/((http|https)\:\/\/)?(caiyun\.feixin\.10086.cn\/dl\/)(\w*)/is', create_function('$matches', 'return plugin_zeroze007diy::_filterurl("$matches[1]caiyun.feixin.10086.cn/dl/$matches[4]");'), $message);
        $message = preg_replace_callback('/((http|https)\:\/\/)?(cloud\.letv\.com\/s\/)(\w*)/is', create_function('$matches', 'return plugin_zeroze007diy::_filterurl("$matches[1]cloud.letv.com/s/$matches[4]");'), $message);
        $message = preg_replace_callback('/((http|https)\:\/\/)?(dl\.vmall\.com\/)(\w*)/is', create_function('$matches', 'return plugin_zeroze007diy::_filterurl("$matches[1]dl.vmall.com/$matches[4]");'), $message);
        $message = preg_replace_callback('/((http|https)\:\/\/)?(dl\.dbank\.com\/)(\w*)/is', create_function('$matches', 'return plugin_zeroze007diy::_filterurl("$matches[1]dl.dbank.com/$matches[4]");'), $message);
        $message = preg_replace_callback('/((http|https)\:\/\/)?(drive\.google\.com\/)([\w\/\?\=&_;]*)/is', create_function('$matches', 'return plugin_zeroze007diy::_filterurl("$matches[1]drive.google.com/$matches[4]");'), $message);

        if (strpos($msglower, '[/hide]') !== false) {
            $msglower = strtolower($message);
            if (strpos($msglower, '[hide=p') !== false) {
                $message = preg_replace_callback("/\[hide=p(\d+)\]\s*(.+?)\s*\[\/hide\]/is", create_function('$matches', 'return plugin_zeroze007diy::_hidepermit($matches[1], $matches[2]);'), $message);
            }
            if (strpos($msglower, '[hide=d') !== FALSE && in_array($_G['member']['groupid'], $exemptHideGroups) || ($_G['uid'] && in_array($_G['uid'], $exemptHideUids))) {
                $message = preg_replace("/\[hide=(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/", tpl_hide_reply2(), $message);
            }
            if (strpos($msglower, '[hide]') !== false && in_array($_G['member']['groupid'], $exemptHideGroups) || ($_G['uid'] && in_array($_G['uid'], $exemptHideUids))) {
                $message = preg_replace("/\[hide\]\s*(.*?)\s*\[\/hide\]/is", tpl_hide_reply2(), $message);
            }
            if (strpos($msglower, '[/hide]') !== false && strpos($msglower, '[hide') === false) {
                $message = preg_replace("/\[\/hide\]/is", "", $message);
            }
        }
        $_G['discuzcodemessage'] = $message;
    }

    public function viewthread_postheader_output()
    {
        global $_G, $postlist;
        $return = array();
        if (!$postlist) {
            return $return;
        }

        if ($_G['forum']['alloweditpost'] && $_G['uid']) {
            $alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $_G['forum_thread']['special'] + 1);
            if (!$alloweditpost_status) {
                $edittimelimit = $_G['group']['edittimelimit'] * 60;
            }
        }

        foreach ($postlist as $pid => $post) {
            if (($_G['forum']['ismoderator'] && $_G['group']['alloweditpost']) ||
                ($_G['forum']['alloweditpost'] && $_G['uid'] && ($post['authorid'] == $_G['uid'] && $_G['forum_thread']['closed'] == 0) && !(!$alloweditpost_status && $edittimelimit && TIMESTAMP - $post['dbdateline'] > $edittimelimit))
            ) {
                $return[] = '<span class="pipe">|</span><a href="forum.php?mod=post&action=edit&fid=' . $_G['fid'] . '&tid=' . $_G['tid'] . '&pid=' . $pid . (!empty($_GET['modthreadkey']) ? '&modthreadkey=' . $_GET['modthreadkey'] : '') . '&page=' . $_G['page'] . '">' . ($_G['forum_thread']['special'] == 2 && !$post['message'] ? lang('template', 'post_add_aboutcounter') : lang('template', 'edit')) . '</a>';
            } elseif ($_G['uid'] && $post['authorid'] == $_G['uid'] && $_G['setting']['postappend']) {
                $return[] = '<span class="pipe">|</span><a href="forum.php?mod=misc&action=postappend&tid=' . $_G['tid'] . '&pid=' . $pid . '&extra=' . $_GET['extra'] . '&page=' . $_G['page'] . '" onClick="showWindow(\'postappend\', this.href, \'get\', 0)">' . lang('forum/template', 'postappend') . '</a>';
            }
        }
        return $return;
    }

    public function global_header()
    {
        global $_G;
        $return = '';
        /*
        if ($_G['member']['newpm'] || $_G['member']['newprompt']){
            $return = '<div class="wp cl"><table border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody><tr><td style="padding: 10px; border: 0; background: '.( $_G['member']['newpm'] ? 'red' : 'indigo').';"><b/>
                            <a href="home.php?mod=space&do='.( $_G['member']['newpm'] ? 'pm' : 'notice' ).'"><font style="color:white;font-size:18px;">&#20320;&#26377;&#26032;'.( $_G['member']['newpm'] ? lang('template' ,'pm_center') : lang('template' ,'remind')).'&#65281;&#28857;&#20987;&#26597;&#30475;</font>
                            </a></b/></td></tr></tbody></table>
                        </div>';
        }
        */
        return $return;
    }
}

class plugin_zeroze007diy_member extends plugin_zeroze007diy
{
}

class plugin_zeroze007diy_forum extends plugin_zeroze007diy
{
}

class plugin_zeroze007diy_home extends plugin_zeroze007diy
{
}

class plugin_zeroze007diy_group extends plugin_zeroze007diy
{
}

class mobileplugin_zeroze007diy extends plugin_zeroze007diy
{
}

class mobileplugin_zeroze007diy_member extends mobileplugin_zeroze007diy
{
}

class mobileplugin_zeroze007diy_forum extends mobileplugin_zeroze007diy
{
}

class mobileplugin_zeroze007diy_home extends mobileplugin_zeroze007diy
{
}

class mobileplugin_zeroze007diy_group extends mobileplugin_zeroze007diy
{
}

?>
