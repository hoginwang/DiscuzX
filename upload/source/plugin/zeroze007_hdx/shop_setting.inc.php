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
//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', '1');

require 'include/function.inc.php';


$itemType = array(
    'weapon' => lang('plugin/zeroze007_hdx', 'weapon'),
    'armor' => lang('plugin/zeroze007_hdx', 'armor'),
    'food' => lang('plugin/zeroze007_hdx', 'food'),
);

$itemTypeAry = array(
    'weapon' => toUTF8(lang('plugin/zeroze007_hdx', 'weapon')),
    'armor' => toUTF8(lang('plugin/zeroze007_hdx', 'armor')),
    'food' => toUTF8(lang('plugin/zeroze007_hdx', 'food'))
);


$typeOptions = array(array('weapon', $itemType['weapon']), array('armor', $itemType['armor']), array('food', $itemType['food']));

$itemTypeJSON = json_encode($itemTypeAry);


// 语言包

$op = $_G['gp_op'];
$pmod = $_G['gp_pmod'];
$formAction = 'plugins&operation=config&do=' . $pluginid . '&identifier=' . $plugin['identifier'] . '&pmod=' . $pmod;

//cpheader();


if (!$op) {
    if (!submitcheck('itemsubmit') || submitcheck('itemsearch')) {

        $keyword = $_G['gp_keyword'];

        showtips(lang('plugin/zeroze007_hdx', 'setting_shop_tips'));

        showformheader($formAction);

        showtableheader(lang('plugin/zeroze007_hdx', 'setting_item_search'), 'fixpadding');


        showsetting(lang('plugin/zeroze007_hdx', 'keyword'), 'keyword', $keyword, 'text', '', 0, $_lang['keyword_tip']);

        showsubmit('itemsearch', 'search', '');
        showtablefooter();
        showformfooter();

        showformheader($formAction);
        showtableheader($_lang['item_list'], 'fixpadding');
        showsubtitle(array(
            '',
            'display_order',
            'available',
            lang('plugin/zeroze007_hdx', 'type'),
            'name',
            'price',
            lang('plugin/zeroze007_hdx', 'setting_success_rate_range'),
            lang('plugin/zeroze007_hdx', 'durability'),
            lang('plugin/zeroze007_hdx', 'loss_rate'),
            'description',
            'pics',
            ''
        ));
        ?>



        <script type="text/JavaScript">
                                                                            
            var itemTypes = <?php echo $itemTypeJSON ?>;
            var itemTypeOption = [];
            jq.each(itemTypes, function(key, itemType) {
                itemTypeOption.push('<option value='+ key +'>'+ itemType +'</option>');	
            });
                                                                        	
                                                                            
            var rowtypedata = [
                [
                    [1,'', 'td25'],
                    [1,'<input type="text" class="txt" name="newdisporder[]" size="3">', 'td28'],
                    [1,'<input class="checkbox" type="checkbox" name="newavailable[]" value="1"', 'td25'],
                    [1,'<select name="newtype[]">'+ itemTypeOption.join('') +'</select>', 'td23'],
                    [1,'<input type="text" class="txt" name="newname[]" size="10">'],
                    [1,'<input type="text" class="txt" name="newprice[]" size="30">'],
                    [1,'<input type="text" class="txt" name="newrate[]" size="30">', 'td31'],
                    [1,'<input type="text" class="txt" name="newdurability[]" size="30">', 'td31'],
                    [1,'<input type="text" class="txt" name="newlossrate[]" size="30">', 'td31'],
                    [1,'<input type="text" class="txt" name="newdescription[]" size="30">', 'td31'],
                    [1,'', 'td24'],
                    [1,'', 'td24']
                ]
            ];
        </script>
        <?php
        $url = ADMINSCRIPT . "?action=" . $formAction . "&page=" . $page . "&keyword=" . $keyword;

        $perpage = max(20, empty($_G['gp_perpage']) ? 20 : intval($_G['gp_perpage']));
        $ratert_limit = ($page - 1) * $perpage;

        $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('hdx_shop_item') . " WHERE name LIKE '%" . dhtmlspecialchars($keyword) . "%'");
        if ($count) {
            $multipage = multi($count, $perpage, $page, $url, 0, 3);

            $query = DB::query("SELECT * FROM " . DB::table('hdx_shop_item') . " 
					WHERE name LIKE '%" . $keyword . "%' ORDER BY disp_order,id DESC  LIMIT $ratert_limit, $perpage");
            while ($item = DB::fetch($query)) {
                $checkavailable = $item['available'] ? 'checked' : '';
                $type = $item['type'];
                $item['type'] = $itemType[$type];
                showtablerow('', array(
                    'class="td25"',
                    'class="td28"',
                    'class="td25"',
                    'class="td25"',
                    '',
                    'class="td31"',
                    'class="td31"',
                    'class="td31"',
                    'class="td31"',
                    'class="td24"',
                    'class="td24"'
                        ), array(
                    "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$item[id]\">",
                    "<input type=\"text\" class=\"txt\" size=\"3\" name=\"disporder[$item[id]]\" value=\"$item[disp_order]\">",
                    "<input class=\"checkbox\" type=\"checkbox\" name=\"available[$item[id]]\" value=\"1\" $checkavailable>",
                    $item['type'],
                    "<input type=\"text\" class=\"txt\" size=\"10\" name=\"name[$item[id]]\" value=\"$item[name]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"price[$item[id]]\" value=\"$item[price]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"rate[$item[id]]\" value=\"$item[rate]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"durability[$item[id]]\" value=\"$item[durability]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"lossrate[$item[id]]\" value=\"$item[d_loss_rate]\">",
                    "<input type=\"text\" class=\"txt\" size=\"30\" name=\"description[$item[id]]\" value=\"$item[description]\">",
                    "<img src='" . $item['img_file'] . "' height=100 width=100>",
                    "<a href=\"" . ADMINSCRIPT . "?action=" . $formAction . "&op=edit&itemId=" . $item[id] . "\" class=\"act\">$lang[detail]</a>"
                ));
            }
        }
        echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">' . lang('plugin/zeroze007_hdx', 'setting_add_new') . '</a></div></td></tr>';
        showsubmit('itemsubmit', 'submit', 'del', '', $multipage, false);

        showtablefooter();
        showformfooter();
    } else {

        if (is_array($_G['gp_delete'])) {
            $ids = $comma = '';
            foreach ($_G['gp_delete'] as $id) {
                $ids .= "$comma'$id'";
                $comma = ',';
            }
            DB::query("DELETE FROM " . DB::table('hdx_shop_item') . " WHERE id IN ($ids)");
        }
        if (is_array($_G['gp_name'])) {

            foreach ($_G['gp_name'] as $id => $val) {
                if (!validValue($_G['gp_rate'][$id], true)) {

                    cpmsg(lang('plugin/zeroze007_hdx', 'setting_success_rate_error'), '', 'error');
                }

                $updateData = array();
                $updateData['name'] = dhtmlspecialchars($_G['gp_name'][$id]);
                $updateData['price'] = floatval($_G['gp_price'][$id]);
                $updateData['disp_order'] = intval($_G['gp_disporder'][$id]);
                $updateData['available'] = intval($_G['gp_available'][$id]);
                $updateData['rate'] = $_G['gp_rate'][$id];
                $updateData['durability'] = $_G['gp_durability'][$id];
                $updateData['d_loss_rate'] = $_G['gp_lossrate'][$id];
                $updateData['description'] = dhtmlspecialchars($_G['gp_description'][$id]);

                DB::update('hdx_shop_item', $updateData, "id='$id'");
            }
        }

        if (is_array($_G['gp_newname'])) {
            foreach ($_G['gp_newname'] as $key => $value) {
                if ($value != '') {
                    $data = array(
                        'type' => $_G['gp_newtype'][$key],
                        'name' => dhtmlspecialchars($value),
                        'available' => intval($_G['gp_newavailable'][$key]),
                        'disp_order' => intval($_G['gp_newdisporder'][$key]),
                        'price' => floatval($_G['gp_newprice'][$key]),
                        'rate' => $_G['gp_newrate'][$key],
                        'durability' => $_G['gp_newdurability'][$key],
                        'd_loss_rate' => $_G['gp_newlossrate'][$key],
                        'description' => dhtmlspecialchars($_G['gp_newdescription'][$key])
                    );

                    DB::insert('hdx_shop_item', $data);
                }
            }
        }
        cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=' . $formAction, 'succeed');
    }
} elseif ($op == 'edit') {

    $itemId = intval($_G['gp_itemId']);

    if (!submitcheck('itemeditsubmit')) {

        $item = DB::fetch_first("SELECT * FROM " . DB::table('hdx_shop_item') . " WHERE id='$itemId'");


        showformheader($formAction . "&op=edit&itemId=" . $itemId, 'enctype');
        showtableheader(lang('plugin/zeroze007_hdx', 'edit_item') . ' - ' . $item['name'], 'nobottom');
        // type
        showsetting(lang('plugin/zeroze007_hdx', 'item_type'), array(
            'type',
            $typeOptions
                ), $item['type'], 'select', '', 0, lang('plugin/zeroze007_hdx', 'shop_item_type_desc'));

        showsetting('name', 'name', $item['name'], 'text');
        showsetting('price', 'price', $item['price'], 'text');
        showsetting(lang('plugin/zeroze007_hdx', 'rate_range'), 'rate', $item['rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'rate_range_desc'));
        showsetting(lang('plugin/zeroze007_hdx', 'durability'), 'durability', $item['durability'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'durability_desc'));
        showsetting(lang('plugin/zeroze007_hdx', 'loss_rate'), 'lossrate', $item['d_loss_rate'], 'text', '', 0, lang('plugin/zeroze007_hdx', 'loss_rate_desc'));
        showsetting('description', 'description', $item['description'], 'text');
        showsetting('pics', '', '', '<input type="file" class="txt uploadbtn marginbot" value="" name="imgfile">' . ($item['img_file'] != '' ? '<br><img src=' . $item['img_file'] . ' width=100 height=100>' : ''), '', 0, lang('plugin/zeroze007_hdx', 'item_image_desc'));

        showtagfooter('tbody');
        showtablefooter();

        showsubmit('itemeditsubmit');
        showtablefooter();
        showformfooter();
    } else {
        $updateData = array();

        // 上传文件
        if ($_FILES['imgfile']['name']) {
            if (!is_dir(DISCUZ_ROOT . './data/hdx/item')) {
                if (!@mkdir(DISCUZ_ROOT . './data/hdx/item', 0777, true)) {
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


            $upload->attach['target'] = DISCUZ_ROOT . './data/hdx/item/' . $itemId . '.' . $attach['ext'];
            if (!$upload->error()) {
                $upload->save();
            }
            if ($upload->error()) {
                cpmsg(lang('plugin/zeroze007_hdx', 'error_with_info') . $upload->error(), '', 'error');
            } else {
                $filename = 'data/hdx/item/' . $itemId . '.' . $attach['ext'];
            }
            $updateData['img_file'] = $filename;
        }
        $updateData['type'] = dhtmlspecialchars($_G['gp_type']);
        $updateData['name'] = dhtmlspecialchars($_G['gp_name']);
        $updateData['price'] = floatval($_G['gp_price']);

        if (!validValue($_G['gp_rate'], true)) {
            cpmsg(lang('plugin/zeroze007_hdx', 'setting_success_rate_error'), '', 'error');
        }

        $updateData['rate'] = $_G['gp_rate'];
        $updateData['durability'] = intval($_G['gp_durability']);
        $updateData['d_loss_rate'] = $_G['gp_lossrate'];
        $updateData['description'] = dhtmlspecialchars($_G['gp_description']);

        DB::update('hdx_shop_item', $updateData, "id='$itemId'");

        cpmsg(lang('plugin/zeroze007_hdx', 'done_successfully'), 'action=' . $formAction, 'succeed');
    }
}
?>
