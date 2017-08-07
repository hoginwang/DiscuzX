<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$url = 'plugin.php?id=zeroze007_hdx&op=yule';

if ($subop == 'gift') {
    $yuleId = intval($_G['gp_yuleId']);
    $yule = DB::fetch_first("SELECT * FROM " . DB::table('hdx_yule') . " WHERE available= 1 AND id='{$yuleId}'");

    if (!$yule) {
        showError(lang('plugin/zeroze007_hdx', 'no_such_yule'));
    }
} else {
    // 娱乐列表
    $yules = array();

    $query = DB::query('SELECT * FROM ' . DB::table('hdx_yule') . ' WHERE  available= 1 ORDER BY disp_order LIMIT ' . $_start . ',' . $_perpage);

    while ($y = DB::fetch($query)) {
        $y = escape($y, 'html');
        $yules[] = $y;
    }

    $count = DB::result_first('SELECT COUNT(*) FROM ' . DB::table('hdx_yule') . '  WHERE available= 1');

    $multipage = multi($count, $_perpage, $_page, $url);
}
?>
