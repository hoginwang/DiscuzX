<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_usertitles.php 31192 2012-07-25 03:26:29Z chenmengshu $
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

// 后台插件设置
loadcache('plugin');
$_setting = $_G['cache']['plugin']['zeroze007_hdx'];


$pmod = $_G['gp_pmod'];
$formAction = 'plugins&operation=config&do=' . $pluginid . '&identifier=' . $plugin['identifier'] . '&pmod=' . $pmod;


if (!submitcheck('titlesubmit')) {

    $stitles = $smembers = $specialtitle = array();
    $sids = '0';
    $smembernum = $membertitle = $systitle = $membertitleoption = $specialtitleoption = '';


    $query = DB::query("SELECT * FROM " . DB::table('hdx_title') . " ORDER BY high");
    $titles = array();
    while ($data = DB::fetch($query)) {
        $titles[] = $data;
    }

    foreach ($titles as $t) {

        $membertitleoption .= "<option value=\"g{$t[id]}\">" . addslashes($t['name']) . "</option>";

        $membertitle .= showtablerow('', array('class="td25"', '', 'class="td28"'), array(
            "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$t[id]]\" value=\"$t[id]\">",
            "<input type=\"text\" class=\"txt\" size=\"12\" name=\"titlenew[$t[id]][name]\" value=\"$t[name]\">",
            "<input type=\"text\" class=\"txt\" size=\"6\" name=\"titlenew[$t[id]][high]\" value=\"$t[high]\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"titlenew[$t[id]][low]\" value=\"$t[low]\" disabled style='background:#ccc'/>"
                ), TRUE);
    }


    echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" size="12" name="titlenewadd[name][]">'],
		[1,'<input type="text" class="txt" size="6" name="titlenewadd[high][]">', 'td28'],
	]
];
</script>
EOT;
    showtips(lang('plugin/zeroze007_hdx', 'setting_title_tips'));


    showformheader($formAction);
    showtableheader(lang('plugin/zeroze007_hdx', 'player_title_setting'), 'fixpadding', 'id="membertitles"' . ($_GET['type'] && $_GET['type'] != 'member' ? ' style="display: none"' : ''));
    showsubtitle(array('', lang('plugin/zeroze007_hdx', 'player_title'), lang('plugin/zeroze007_hdx', 'level_range')));
    echo $membertitle;
    echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">' . lang('plugin/zeroze007_hdx', 'setting_add_new') . '</a></div></td></tr>';
    showsubmit('titlesubmit', 'submit', 'del');
    showtablefooter();
    showformfooter();
} else {

    $titlenewadd = array_flip_keys($_GET['titlenewadd']);
    foreach ($titlenewadd as $k => $v) {
        if (!$v['name']) {
            unset($titlenewadd[$k]);
        } elseif (!$v['high']) {
            cpmsg(lang('plugin/zeroze007_hdx','player_title_level_high_invalid'), '', 'error');
        }
    }
    
      
    
    $titlenewkeys = array_keys($_GET['titlenew']);
    $maxid = max($titlenewkeys);
    foreach ($titlenewadd as $k => $v) {
        $_GET['titlenew'][$k + $maxid + 1] = $v;
    }
    $orderarray = array();
    if (is_array($_GET['titlenew'])) {
        foreach ($_GET['titlenew'] as $id => $title) {
            if ((is_array($_GET['delete']) && in_array($id, $_GET['delete'])) || ($id == 0 && (!$title['name'] || $title['high'] == ''))) {
                unset($_GET['titlenew'][$id]);
            } else {
                $orderarray[$title['high']] = $id;
            }
        }
    }

    if (empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
        cpmsg(lang('plugin/zeroze007_hdx','player_title_level_invalid'), '', 'error');
    }

    ksort($orderarray);
    $rangearray = array();
    $lowerlimit = array_keys($orderarray);
    for ($i = 0; $i < count($lowerlimit); $i++) {
        $rangearray[$orderarray[$lowerlimit[$i]]] = array(
            'high' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
            'low' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 999999999
        );
    }

    foreach ($_GET['titlenew'] as $id => $title) {
        $highnew = $rangearray[$id]['high'];
        $lownew = $rangearray[$id]['low'];
        if ($highnew == $lownew) {
            cpmsg(lang('plugin/zeroze007_hdx','player_title_level_duplicate'), '', 'error');
        }
        if (in_array($id, $titlenewkeys)) {
            DB::update('hdx_title', array('name' => $title['name'], 'high' => $highnew, 'low' => $lownew), "id='" . intval($id) . "'");
        } elseif ($title['name'] && $title['high'] != '') {
            $data = array(
                'name' => $title['name'],
                'high' => $highnew,
                'low' => $lownew,
            );


            DB::insert('hdx_title', $data);
        }


        if (is_array($_G['gp_delete'])) {
            $ids = $comma = '';
            foreach ($_G['gp_delete'] as $id) {
                $ids .= "$comma'$id'";
                $comma = ',';
            }
            DB::query("DELETE FROM " . DB::table('hdx_title') . " WHERE id IN ($ids)");
        }
    }

     cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=' . $formAction, 'succeed');
}

function array_flip_keys($arr) {
    $arr2 = array();
    $arrkeys = @array_keys($arr);
    list(, $first) = @each(array_slice($arr, 0, 1));
    if ($first) {
        foreach ($first as $k => $v) {
            foreach ($arrkeys as $key) {
                $arr2[$k][$key] = $arr[$key][$k];
            }
        }
    }
    return $arr2;
}

?>