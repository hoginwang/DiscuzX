<?php

/**
 * To front end HTML escape
 * 
 * if set type, escape string when query sql
 * @param type $string
 * @param type $type
 * @return type 
 */
function escape($string, $type = 'sql') {
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = escape($val, $type);
        }
    } else {
        switch ($type) {
            case 'sql':
                //$string = mysql_real_escape_string($string);
				$string = addslashes(sprintf("%s",$string));
                break;
            case 'html':
                $string = htmlspecialchars(stripslashes($string), ENT_QUOTES);
                break;
            case 'json':
                // UTF8 only
                $string = json_encode($string);
                break;
            case 'url':
                $string = urlencode($string);
                break;
            default:
                die('Undefined type');
        }
    }

    return $string;
}

function validValue($val, $allowRange = false, $allowPercentSign = false) {
    $val = strval($val);
    $pattern[] = '^(\d+)$';
    if ($allowRange) {
        $pattern[] = '^(\d+),(\d+)$';
    }

    if ($allowPercentSign) {
        if ($allowRange) {
            $pattern[] = '^(\d+)(%)$';
        }
        $pattern[] = '^(\d+)(%),(\d+)(%)$';
    }

    $patternStr = implode('|', $pattern);

    return preg_match('/' . $patternStr . '/', $val);
}

// 检测POST表单是否是正整数
function isNumeric($num) {
    return (ctype_digit($num) && !empty($num));
}

// 检测是否ajax request
function isAjax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest");
}

function msgFilter($msg, $username, $replace) {
    $patterns = '/\{\{' . $username . '\}\}/';
    $msg = preg_replace($patterns, $replace, $msg);
    $msg = str_replace("{", "", $msg);
    $msg = str_replace("}", "", $msg);

    return $msg;
}

function showError($msg, $param = array()) {
    return showMsg($msg, 0, $param);
}

/**
 * 
 * @global type $_charset
 * @global type $_charset
 * @param type $msg
 * @param type $result
 * @param int $param
 * 
 * 	$param = array(
  'header'	=> false,
  'timeout'	=> null,
  'refreshtime'	=> null,
  'closetime'	=> null,
  'locationtime'	=> null,
  'alert'		=> null,
  'return'	=> false,
  'redirectmsg'	=> 0,
  'msgtype'	=> 1,
  'showmsg'	=> true,
  'showdialog'	=> false,
  'login'		=> false,
  'handle'	=> false,
  'extrajs'	=> '',
  'striptags'	=> true,
  );
 * 
 * 
 * @return type
 */
// throw new Exception($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
function showMsg($msg, $type = 1, $param = array()) {
    $type = intval($type);
    switch ($type) {
        case 0:
            $param['alert'] = 'error';
            break;
        case 1:
            $param['alert'] = 'right';
            break;
        case 2:
        default:
            $param['alert'] = 'info';
            break;
    }
    if (isAjax()) {
        if ($_REQUEST['showmessage'] == 1) {
            if (!empty($param['url'])) {
                $param['locationtime'] = 0;
            }
            $param['showdialog'] = 1;
            $param['striptags'] = false;
            showmessage($msg, $param['url'], array(), $param);
        } else {
            global $_charset;
            if (in_array($_charset, array(
                        'utf-8',
                        'gbk',
                        'big5'
                    ))) {

                if ($_charset != 'utf-8') {
                    $msg = iconv($_charset, 'utf-8', $msg);
                }
            } else {
                $msg = "Error: \$_charset empty, could not fetch charset value.";
            }

            $resultAry = array(
                'result' => $type,
                'url' => $param['url'],
                'msg' => $msg
            );

            die(json_encode($resultAry));
        }
    } else {
        showmessage($msg, $param['url'], array(), $param);
    }
}

/**
 * get ramdom number from format 5,10 or 5
 * @param type $val 
 * @param type $num if %, then the percentage of $num
 * @return type
 */
function getRandomNumber($val, $num = 0) {

    if (preg_match('/^(\d+)$|^(\d+),(\d+)$|^(\d+)(%)$|^(\d+)(%),(\d+)(%)$/', $val, $matches)) {


        $count = count($matches);
        switch ($count) {
            case 2:
                // match 5
                $min = $max = $matches[0];
                break;
            case 10:
                // match 5%,10%
                $min = $matches[6];
                $max = $matches[8];

                if ($matches[7] == $matches[9] && $matches[7] == '%') {
                    $percentage = true;
                }
                break;
            case 4:
                // match 1,5
                $min = $matches[2];
                $max = $matches[3];
                break;
        }

        if ($min != $max) {
            $val = rand($min, $max);
        } else {
            $val = $min;
        }
        if ($percentage) {
            $val = $val / 100;
            $val = ceil($num * $val);
        }
        return $val;
    } else {
        return 0;
    }
}

// 转换编码到UTF8输出
function toUTF8($str) {
    global $_G;
    return iconv($_G['charset'], 'utf-8', $str);
}

function getPlayerSta($uid) {
    global $_setting;
    // sta设置
    $_staExtNum = intval($_setting['sta_ext']);
    $_ext = 'extcredits' . $_staExtNum;
    if ($_staExtNum > 0 && $_staExtNum < 9) {
        return DB::result_first("SELECT " . $_ext . " FROM " . DB::table('common_member_count') . " WHERE uid = '" . intval($uid) . "'");
    } else {
        return DB::result_first("SELECT sta FROM " . DB::table('hdx_player') . " WHERE uid = '" . intval($uid) . "'");
    }
}

function updateSw($uid, $val) {
    global $_setting;
    // sw设置
    $_swExtNum = intval($_setting['sw_ext']);
    $_ext = 'extcredits' . $_swExtNum;
    if ($_swExtNum > 0 && $_swExtNum < 9) {
        DB::query("UPDATE " . DB::table('common_member_count') . " SET `" . $_ext . "`=`" . $_ext . "`+" . intval($val) . " WHERE uid='" . intval($uid) . "'");
    } else {
        DB::query("UPDATE " . DB::table('hdx_player') . " SET `sw`=`sw`+" . intval($val) . " WHERE uid='" . intval($uid) . "'");
    }
}

function updateSta($uid, $val) {
    global $_setting, $_sta;

    // sta设置
    $_staExtNum = intval($_setting['sta_ext']);
    $_ext = 'extcredits' . $_staExtNum;
    if ($val < 0 && $_sta + $val <= 0) {
        // when reduct sta, sta under 0
        $finalSta = 0;
    } else if ($val > 0 && $_sta + $val >= intval($_setting['max_sta'])) {
        $finalSta = $_setting['max_sta'];
    } else {
        $finalSta = $_sta + $val;
    }
    if ($_staExtNum > 0 && $_staExtNum < 9) {
        DB::query("UPDATE " . DB::table('common_member_count') . " SET " . $_ext . "=" . intval($finalSta) . " WHERE uid='" . intval($uid) . "'");
    } else {
        DB::query("UPDATE " . DB::table('hdx_player') . " SET sta='" . intval($finalSta) . "' WHERE uid='" . intval($uid) . "'");
    }

    return $finalSta;
}

function nextLevelExp($level, $rate) {
    $nextLevel = $level + 1;
    $nextExp = round((($level + $nextLevel) * ($nextLevel + 1) + ($level + $nextLevel) * ($level + $nextLevel)) * $rate);
    return $nextExp;
}

function chkUpgrade($curExp, $nextExp, $addExp) {
    if ($curExp + $addExp >= $nextExp) {
        return true;
    }

    return false;
}

function getPlayerTitle($level) {
    $titles = array();
    $query = DB::query("SELECT name FROM " . DB::table('hdx_title') . " WHERE " . $level . " >= high AND " . $level . " < low");
    while ($data = DB::fetch($query)) {
        $titles[] = $data;
    }
    if (count($titles) > 1) {
        throw new Exception(lang('plugin/zeroze007_hdx', 'player_title_level_range_duplicate'));
    }
    return $titles[0]['name'];
}

?>