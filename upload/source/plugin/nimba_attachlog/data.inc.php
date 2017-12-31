<?php
/*
 * ��ҳ��http://addon.discuz.com/?@ailab
 * �˹�����ʵ���ң�Discuz!Ӧ������ʮ�����㿪���ߣ�
 * ������� ��ϵQQ594941227
 * From www.ailab.cn
 */

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
?>
    <table class="tb tb2 " id="tips">
        <tbody>
        <tr>
            <th class="partition"><?php echo lang('plugin/nimba_attachlog', 'readme'); ?></th>
        </tr>
        <tr>
            <td class="tipsblock" s="1">
                <ul id="tipslis">
                    <li><?php echo lang('plugin/nimba_attachlog', 'info_1'); ?></li>
                    <li><?php echo lang('plugin/nimba_attachlog', 'info_2'); ?></li>
                    <li><?php echo lang('plugin/nimba_attachlog', 'info_3'); ?></li>
                    <?php
                    if (!file_exists(DISCUZ_ROOT . './source/plugin/nimba_attachlog/data.lib.php')) {
                        ?>
                        <li><?php echo lang('plugin/nimba_attachlog', 'info_4'); ?></li>
                        <?php
                    }
                    ?>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
<?php
require_once libfile('function/misc');
loadcache('plugin');
$pagenum = intval(6 * pi()) + 2;
$page = max(1, intval($_GET['page']));
$check = trim($_GET['check']);
$aid = intval($_GET['aid']);
$uid = intval($_GET['uid']);
if ($check == 'delete') {
    C::t('#nimba_attachlog#nimba_attachlog')->clear();
    cpmsg(lang('plugin/nimba_attachlog', 'op_delete_ok'), 'action=plugins&operation=config&identifier=nimba_attachlog&pmod=data', 'succeed');
    exit;
} elseif ($check == 'aid' && $aid) {
    $count = C::t('#nimba_attachlog#nimba_attachlog')->count_by_aid($aid);
    $data = C::t('#nimba_attachlog#nimba_attachlog')->fetch_all_by_aid_range($aid, ($page - 1) * $pagenum, $pagenum);
    $table = C::t('forum_attachment')->fetch_all_by_id('aid', (array)$aid);
    $attach = C::t('forum_attachment_n')->fetch($table[$aid]['tableid'], (array)$aid);
    showtableheader(lang('plugin/nimba_attachlog', 'tip_1') . lang('plugin/nimba_attachlog', 'tip_2') . '<font color="red">' . $attach['filename'] . '</font>' . lang('plugin/nimba_attachlog', 'tip_4') . '<a href="' . ADMINSCRIPT . '?action=plugins&operation=config&do=' . $pluginid . '&identifier=nimba_attachlog&pmod=data">' . lang('plugin/nimba_attachlog', 'tip_5') . '</a>');
} elseif ($check == 'uid' && $uid >= 0) {
    $count = C::t('#nimba_attachlog#nimba_attachlog')->count_by_uid($uid);
    $data = C::t('#nimba_attachlog#nimba_attachlog')->fetch_all_by_uid_range($uid, ($page - 1) * $pagenum, $pagenum);
    $username = C::t('common_member')->fetch_all_username_by_uid((array)$uid);
    if (empty($username[$uid])) $username[$uid] = lang('plugin/nimba_attachlog', 'nouid');
    showtableheader(lang('plugin/nimba_attachlog', 'tip_1') . lang('plugin/nimba_attachlog', 'tip_2') . '<font color="red">' . $username[$uid] . '</font>' . lang('plugin/nimba_attachlog', 'tip_4') . '<a href="' . ADMINSCRIPT . '?action=plugins&operation=config&do=' . $pluginid . '&identifier=nimba_attachlog&pmod=data">' . lang('plugin/nimba_attachlog', 'tip_5') . '</a>');
} else {
    $count = C::t('#nimba_attachlog#nimba_attachlog')->count();
    $data = C::t('#nimba_attachlog#nimba_attachlog')->fetch_all_by_range(($page - 1) * $pagenum, $pagenum);
    showtableheader(lang('plugin/nimba_attachlog', 'tip_6') . '<a href="' . ADMINSCRIPT . '?action=plugins&operation=config&do=' . $pluginid . '&identifier=nimba_attachlog&pmod=data&check=delete"><font color="red">' . lang('plugin/nimba_attachlog', 'op_delete') . '</font></a>');
}
showsubtitle(array(lang('plugin/nimba_attachlog', 'item_1'), lang('plugin/nimba_attachlog', 'item_2'), lang('plugin/nimba_attachlog', 'item_3'), lang('plugin/nimba_attachlog', 'item_4'), lang('plugin/nimba_attachlog', 'item_5'), lang('plugin/nimba_attachlog', 'item_6')));
foreach ($data as $user) {
    $threads = C::t('forum_thread')->fetch_all_by_tid((array)$user['tid']);
    $thread = $threads[$user['tid']];
    $username = C::t('common_member')->fetch_all_username_by_uid((array)$user['uid']);
    if (empty($username[$user['uid']])) $username[$user['uid']] = lang('plugin/nimba_attachlog', 'nouid');
    $table = C::t('forum_attachment')->fetch_all_by_id('aid', (array)$user['aid']);
    $attach = C::t('forum_attachment_n')->fetch($table[$user['aid']]['tableid'], (array)$user['aid']);
    $myadd = convertip($user['ip']);
    $myadd = str_replace('-', '', $myadd);
    $myadd = str_replace(' ', '', $myadd);
    showtablerow('', array('class="td_k"', 'class="td_k"', 'class="td_l"'), array(
        '<a id="' . $user['uid'] . '" href="' . ADMINSCRIPT . '?action=plugins&operation=config&do=' . $pluginid . '&identifier=nimba_attachlog&pmod=data&check=uid&uid=' . $user['uid'] . '">' . $username[$user['uid']] . '</a>',
        '<a href="forum.php?mod=viewthread&tid=' . $user['tid'] . '" target="_blank">' . $thread['subject'] . '</a>',
        '<a href="' . ADMINSCRIPT . '?action=plugins&operation=config&do=' . $pluginid . '&identifier=nimba_attachlog&pmod=data&check=aid&aid=' . $user['aid'] . '">' . $attach['filename'] . '</a>',
        $table[$user['aid']]['downloads'],
        date('Y-m-d H:i:s', $user['dateline']),
        $user['ip'] . '(' . $myadd . ')',
    ));

}
showtablefooter();
if (!count($data)) echo lang('plugin/nimba_attachlog', 'nodata');
if (file_exists(DISCUZ_ROOT . './source/plugin/nimba_attachlog/data.lib.php')) {
    @require_once DISCUZ_ROOT . './source/plugin/nimba_attachlog/data.lib.php';
}
?>