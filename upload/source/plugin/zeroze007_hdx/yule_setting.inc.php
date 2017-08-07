<style>
    select.item-id {
        width: 120px;
    }
</style>
<script src="source/plugin/zeroze007_hdx/jquery/jquery.min.js"
type="text/javascript"></script>
<script type="text/javascript">
    var jq = jQuery.noConflict();
</script>

<?php
// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

// 后台插件设置
loadcache('plugin');
$_setting = $_G['cache']['plugin']['zeroze007_hdx'];

// 调试开关
if ($_setting['debug']) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', '1');
}

require 'include/function.inc.php';

// 语言包

$op = $_G['gp_op'];
$pmod = $_G['gp_pmod'];
$formAction = 'plugins&operation=config&do=' . $pluginid . '&identifier=' . $plugin['identifier'] . '&pmod=' . $pmod;

//cpheader();


if (!$op) {
    if (!submitcheck('yulesubmit') || submitcheck('yulesearch')) {

        $keyword = $_G['gp_keyword'];

        //showtips($_lang['tips']);
        showformheader($formAction);


        showtableheader(lang('plugin/zeroze007_hdx', 'search_yule'), 'fixpadding');

        showsetting(lang('plugin/zeroze007_hdx', 'keyword'), 'keyword', $keyword, 'text', '', 0, $_lang['keyword_tip']);

        showsubmit('yulesearch', 'search', '');
        showtablefooter();
        showformfooter();

        showformheader($formAction);
        showtableheader(lang('plugin/zeroze007_hdx', 'yule_list'), 'fixpadding');
        showsubtitle(array(
            '',
            'display_order',
            'available',
            'name',
            'price',
            lang('plugin/zeroze007_hdx', 'add_sta'),
            'description',
            'pics',
            ''
        ));
        ?>



        <script type="text/JavaScript">
            var rowtypedata = [
                [
                    [1,'', 'td25'],
                    [1,'<input type="text" class="txt" name="newdisporder[]" size="3">', 'td28'],
                    [1,'<input class="checkbox" type="checkbox" name="newavailable[]" value="1"', 'td25'],
                    [1,'<input type="text" class="txt" name="newname[]" size="10">'],
                    [1,'<input type="text" class="txt" name="newprice[]" size="30">'],
                    [1,'<input type="text" class="txt" name="newaddsta[]" size="30">', 'td31'],
                    [1,'<input type="text" class="txt" name="newdescription[]" size="30">', 'td31'],
                    [1,'', 'td24'],
                    [1,'', 'td24'],
                ]
            ];
        </script>
        <?php
        $url = ADMINSCRIPT . "?action=" . $formAction . "&page=" . $page . "&keyword=" . $keyword;

        $perpage = max(20, empty($_G['gp_perpage']) ? 20 : intval($_G['gp_perpage']));
        $start_limit = ($page - 1) * $perpage;

        $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('hdx_yule') . " WHERE name LIKE '%" . dhtmlspecialchars($keyword) . "%'");
        if ($count) {
            $multipage = multi($count, $perpage, $page, $url, 0, 3);

            $query = DB::query("SELECT * FROM " . DB::table('hdx_yule') . " 
					WHERE name LIKE '%" . $keyword . "%' ORDER BY disp_order,id DESC  LIMIT $start_limit, $perpage");
            while ($yule = DB::fetch($query)) {
                $checkavailable = $yule['available'] ? 'checked' : '';

                showtablerow('', array(
                    'class="td25"',
                    'class="td28"',
                    'class="td25"',
                    '',
                    '',
                    'class="td31"',
                    'class="td31"',
                    'class="td24"',
                    'class="td24"'
                        ), array(
                    "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$yule[id]\">",
                    "<input type=\"text\" class=\"txt\" size=\"3\" name=\"disporder[$yule[id]]\" value=\"$yule[disp_order]\">",
                    "<input class=\"checkbox\" type=\"checkbox\" name=\"available[$yule[id]]\" value=\"1\" $checkavailable>",
                    "<input type=\"text\" class=\"txt\" size=\"10\" name=\"name[$yule[id]]\" value=\"$yule[name]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"price[$yule[id]]\" value=\"$yule[price]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"addsta[$yule[id]]\" value=\"$yule[add_sta]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"description[$yule[id]]\" value=\"$yule[description]\">",
                    "<img src='" . $yule['img_file'] . "' height=100 width=100>",
                    "<a href=\"" . ADMINSCRIPT . "?action=" . $formAction . "&op=edit&yuleId=" . $yule[id] . "\" class=\"act\">$lang[detail]</a>"
                ));
            }
        }
        echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">' . lang('plugin/zeroze007_hdx', 'setting_add_new') . '</a></div></td></tr>';
        showsubmit('yulesubmit', 'submit', 'del', '', $multipage, false);

        showtablefooter();
        showformfooter();
    } else {

        if (is_array($_G['gp_delete'])) {
            $ids = $comma = '';
            foreach ($_G['gp_delete'] as $id) {
                $ids .= "$comma'$id'";
                $comma = ',';
            }
            DB::query("DELETE FROM " . DB::table('hdx_yule') . " WHERE id IN ($ids)");
        }
        if (is_array($_G['gp_name'])) {

            foreach ($_G['gp_name'] as $id => $val) {

                $updateData = array();
                $updateData['name'] = dhtmlspecialchars($_G['gp_name'][$id]);
                $updateData['price'] = floatval($_G['gp_price'][$id]);
                $updateData['disp_order'] = intval($_G['gp_disporder'][$id]);
                $updateData['available'] = intval($_G['gp_available'][$id]);
                $updateData['add_sta'] = intval($_G['gp_addsta'][$id]);
                $updateData['description'] = dhtmlspecialchars($_G['gp_description'][$id]);

                DB::update('hdx_yule', $updateData, "id='$id'");
            }
        }

        if (is_array($_G['gp_newname'])) {
            foreach ($_G['gp_newname'] as $key => $value) {
                if ($value != '') {
                    $data = array(
                        'name' => dhtmlspecialchars($value),
                        'available' => intval($_G['gp_newavailable'][$key]),
                        'disp_order' => intval($_G['gp_newdisporder'][$key]),
                        'price' => floatval($_G['gp_newprice'][$key]),
                        'add_sta' => intval($_G['gp_newaddsta'][$key]),
                        'description' => dhtmlspecialchars($_G['gp_newdescription'][$key])
                    );

                    DB::insert('hdx_yule', $data);
                }
            }
        }
        cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=' . $formAction, 'succeed');
    }
} elseif ($op == 'edit') {

    $yuleId = intval($_G['gp_yuleId']);

    if (!submitcheck('yuleeditsubmit')) {

        $yule = DB::fetch_first("SELECT * FROM " . DB::table('hdx_yule') . " WHERE id='$yuleId'");

        showformheader($formAction . "&op=edit&yuleId=" . $yuleId, 'enctype');
        showtableheader(lang('plugin/zeroze007_hdx', 'edit_yule') . ' - ' . $yule['name'], 'nobottom');
        showsetting('name', 'name', $yule['name'], 'text');
        showsetting('price', 'price', $yule['price'], 'text');
        showsetting(lang('plugin/zeroze007_hdx', 'add_sta'), 'addsta', $yule['add_sta'], 'text');
        showsetting('description', 'description', $yule['description'], 'text');
        showsetting('pics', '', '', '<input type="file" class="txt uploadbtn marginbot" value="" name="imgfile">' . ($yule['img_file'] != '' ? '<br><img src=' . $yule['img_file'] . ' width=100 height=100>' : ''), '', 0, lang('plugin/zeroze007_hdx', 'item_image_desc'));

        showtagfooter('tbody');
        showtablefooter();

        showsubmit('yuleeditsubmit');
        showtablefooter();
        showformfooter();
    } else {
        $updateData = array();

        // 上传文件
        if ($_FILES['imgfile']['name']) {
            if (!is_dir(DISCUZ_ROOT . './data/hdx/yule')) {
                if (!@mkdir(DISCUZ_ROOT . './data/hdx/yule', 0777, true)) {
                    cpmsg(lang('plugin/zeroze007_hdx', 'create_folder_error'), '', 'error');
                }
            }


            if ($_G['setting']['version'] == 'X1.5' || $_G['setting']['version'] == 'X2') {
                require_once libfile('class/upload');
            }
            $upload = new discuz_upload();

            $upload->init($_FILES['imgfile'], 'temp');
            $attach = $upload->attach;

            if (!in_array($attach['type'], array(
                        'image/pjpeg',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/bmp'
                    ))) {
                cpmsg(lang('plugin/zeroze007_hdx', 'only_upload_image_file'), '', 'error');
            }
            //print_r($attach);
            //die;

            $upload->attach['target'] = DISCUZ_ROOT . './data/hdx/yule/' . $yuleId . '.' . $attach['ext'];
            if (!$upload->error()) {
                $upload->save();
            }
            if ($upload->error()) {
                cpmsg(lang('plugin/zeroze007_hdx', 'error_with_info') . $upload->error(), '', 'error');
            } else {
                $filename = 'data/hdx/yule/' . $yuleId . '.' . $attach['ext'];
            }
            $updateData['img_file'] = $filename;
        }
        $updateData['name'] = dhtmlspecialchars($_G['gp_name']);
        $updateData['price'] = floatval($_G['gp_price']);
        $updateData['add_sta'] = intval($_G['gp_addsta']);
        $updateData['description'] = dhtmlspecialchars($_G['gp_description']);

        DB::update('hdx_yule', $updateData, "id='$yuleId'");

        cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=' . $formAction, 'succeed');
    }
}
?>
