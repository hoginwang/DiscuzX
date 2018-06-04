<?php
/**
 *    [本地化远程图片(zeroze007_pic.{modulename})] (C)2015-2099 Powered by 零零七工作室.
 *    Version: 1.0.0
 *    Date: 2015-4-15 23:44
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class plugin_zeroze007_pic
{

    function _checkUser()
    {
        global $_G;
        loadcache('plugin');
        $var = $_G['cache']['plugin']['zeroze007_pic'];
        $open = $var['open'];
        $selection = $var['selection'];
        $forum = unserialize($var['forums']);
        $group = unserialize($var['groups']);

        if ($open == 1 && in_array($_G['groupid'], $group) && in_array($_G['fid'], $forum)) {
            return true;
        } else {
            return false;
        }
    }

    function _loadtemplate()
    {
        global $_G;
        loadcache('plugin');
        $var = $_G['cache']['plugin']['zeroze007_pic'];
        $selection = $var['selection'];

        if ($this->_checkUser()) {
            $return = '';
            if (!$_G['uid']) {
                return $return;
            }
            include template('zeroze007_pic:post_newthread');
            return $return;
        }
    }

    function _getCurl($url)
    {
        $t = parse_url($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_REFERER, "http://$t[host]/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (substr($url, 0, 8) == 'https://') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        return $ch;
    }

    function _getHeaders($url)
    {
        $ch = $this->_getCurl($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        $info = curl_exec($ch);
        curl_close($ch);
    }

    function _download($url, $localsrc)
    {
        if (file_exists($localsrc)) {
            copy($url, $localsrc);
        } else {
            $ch = $this->_getCurl($url);
            $img = curl_exec($ch);
            curl_close($ch);
            $fp2 = @fopen($localsrc, 'w');
            fwrite($fp2, $img);
            fclose($fp2);
            unset($img);
        }
        $size = getimagesize($localsrc);

        if (!$size[0] || !$size[2]) {
            unlink($localsrc);
            return false;
        } else {
            return $size[0];
        }
    }

    function _checkattachdir($ym, $d)
    {
        global $_G;
        $attachdir = $_G['setting']['attachdir'];
        if (!is_dir($attachdir . "/forum/" . $ym . "/")) {
            mkdir($attachdir . "/forum/" . $ym . "/");
        }
        if (!is_dir($attachdir . "/forum/" . $ym . "/" . $d . "/")) {
            mkdir($attachdir . "/forum/" . $ym . "/" . $d . "/");
        }
    }

    function _getromotepic()
    {
        global $_G;
        loadcache('plugin');
        $var = $_G['cache']['plugin']['zeroze007_pic'];
        $down = $_GET['romotepic'];
        $siteurl = empty($var['domain']) ? $_G['siteurl'] : trim($var['domain']);

        if ($down == 1 && $this->_checkUser()) {
            if (preg_match_all("/\[img[^\]]*\]\s*(.*)\s*\[\/img\]/isU", $_GET['message'], $result)) {
                set_time_limit(120);
                // 初始化准备
                $ym = date('Ym');
                $d = date('d');
                $this->_checkattachdir($ym, $d);

                foreach ($result[1] as $key => $url) {
                    if (stripos($url, $siteurl) || !preg_match('/^http[s]?:\/\/(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*\'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/\?)|(\/[0-9a-zA-Z_!~\'\(\)\.;\[\]\?:@&=\+\$,%#-\/^\*\|]*)?)/ies', $url)) {
                        continue;
                    }
                    $his = date('His');
                    $localattachment = $ym . "/" . $d . "/" . $his . strtolower(random(16)) . ".jpg";
                    // 相对路径
                    $localsrc = $_G['setting']['attachdir'] . "/forum/" . $localattachment;
                    // 下载远程附件
                    $attachsaved = $this->_download($url, $localsrc);

                    if ($attachsaved) {
                        require_once libfile('class/image');
                        $romoteimage = new image();
                        $watermarkstatus = unserialize($_G['setting']['watermarkstatus']);
                        if ($watermarkstatus['forum'] && empty($_G['forum']['disablewatermark'])) {
                            $romoteimage->Watermark($localsrc);
                        }
                        // 组织参数
                        $width = $attachsaved;
                        $path_parts = pathinfo($url);
                        $picname = $path_parts['filename'];
                        $filename = $picname . '.jpg'; // 原始文件名
                        $filesize = filesize($localsrc);
                        $isimage = 1;
                        $remote = 0;
                        if (!$remote) {
                            $thumb = $romoteimage->Thumb($localsrc, '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], $_G['setting']['thumbsource']) ? 1 : 0;
                            if (!$_G['setting']['thumbsource'])
                                $width = $romoteimage->imginfo['width'];
                        }

                        // 插入附件
                        $aid = C::t('forum_attachment')->insert(array(
                            'aid' => NULL,
                            'tid' => '0',
                            'pid' => '0',
                            'uid' => $_G['uid'],
                            'tableid' => '127',
                            'downloads' => '0'
                        ), true);
                        C::t('forum_attachment_unused')->insert(array(
                            'aid' => $aid,
                            'uid' => $_G['uid'],
                            'dateline' => $_G['timestamp'],
                            'filename' => $filename,
                            'filesize' => $filesize,
                            'attachment' => $localattachment,
                            'remote' => $remote,
                            'isimage' => $isimage,
                            'width' => $width,
                            'thumb' => $thumb
                        ));
                        $_GET['attachnew'][$aid] = array();
                        $strfirst = strpos($_GET['message'], $result[0][$key]);
                        if ($strfirst !== false) {
                            $_GET['message'] = substr_replace($_GET['message'], '[attachimg]' . $aid . '[/attachimg]', $strfirst, strlen($result[0][$key]));
                        }
                    }
                }
            }
        }
    }
}

class plugin_zeroze007_pic_forum extends plugin_zeroze007_pic
{

    function post_getromotepic()
    {
        parent::_getromotepic();
    }

    function post_middle_output()
    {
        return parent::_loadtemplate();
    }

    function forumdisplay_fastpost_btn_extra_output()
    {
        return parent::_loadtemplate();
    }

    function viewthread_fastpost_btn_extra()
    {
        return parent::_loadtemplate();
    }
}

class plugin_zeroze007_pic_group extends plugin_zeroze007_pic
{

}

?>