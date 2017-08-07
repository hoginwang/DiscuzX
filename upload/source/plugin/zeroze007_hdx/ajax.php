<?php

// 必须使用此判断避免外部调用
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}




switch ($subop) {
    case 'rob':
        require PLUGIN_PATH . '/ajax/rob.inc.php';
        break;
    case 'msg':
        require PLUGIN_PATH . '/ajax/msg.inc.php';
        break;
    case 'jail':
        require PLUGIN_PATH . '/ajax/jail.inc.php';
        break;
    case 'mybag':
        require PLUGIN_PATH . '/ajax/mybag.inc.php';
    case 'shop':
        require PLUGIN_PATH . '/ajax/shop.inc.php';
        break;
    case 'yule':
    case 'gift':
        require PLUGIN_PATH . '/ajax/yule.inc.php';
        break;
    case 'guard':
        require PLUGIN_PATH . '/ajax/guard.inc.php';
        break;
    case 'away':
        require PLUGIN_PATH . '/ajax/away.inc.php';
        break;
    case 'quit':
        require PLUGIN_PATH . '/ajax/quit.inc.php';
        break;
    case 'setting':
        require PLUGIN_PATH . '/ajax/setting.inc.php';
        break;
    default:
        showError('Invalid Action!');
}
?>
