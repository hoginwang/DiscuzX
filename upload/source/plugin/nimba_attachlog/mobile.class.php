<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class mobileplugin_nimba_attachlog
{

}

class mobileplugin_nimba_attachlog_forum extends mobileplugin_nimba_attachlog
{
    function attachment_log()
    {
        loadcache('plugin');
        global $_G;
        $open = $_G['cache']['plugin']['nimba_attachlog']['open'];
        if ($open) {
            $data = explode('|', base64_decode($_GET['aid']));
            $data[0] = intval($data[0]);
            $uid = $_G['uid'];
            if ($data[0] && $data[3] == $uid) {//0:aid 1:hash 2:dateline 3:uid 4:tid
                $table = C::t('forum_attachment')->fetch_all_by_id('aid', (array)$data[0]);
                $attach = C::t('forum_attachment_n')->fetch($table[$data[0]]['tableid'], (array)$data[0]);
                if (!$attach['isimage']) {//图片
                    if ($uid) {//会员 同uid只记录一次
                        $num = C::t('#nimba_attachlog#nimba_attachlog')->count_by_aid_uid($data[0], $uid);
                        if (!$num) {
                            $attach = array('aid' => $data[0], 'uid' => $data[3], 'tid' => $data[4], 'ip' => $_G['clientip'], 'dateline' => $data[2]);
                            C::t('#nimba_attachlog#nimba_attachlog')->insert($attach);
                        }
                    } else {//游客 同IP只记录一次
                        $num = C::t('#nimba_attachlog#nimba_attachlog')->count_by_aid_ip($data[0], $_G['clientip']);
                        if (!$num) {
                            $attach = array('aid' => $data[0], 'uid' => $data[3], 'tid' => $data[4], 'ip' => $_G['clientip'], 'dateline' => $data[2]);
                            C::t('#nimba_attachlog#nimba_attachlog')->insert($attach);
                        }
                    }
                }
            }
        }
    }
}

?>