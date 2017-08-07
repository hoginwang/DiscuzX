<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class plugin_zeroze007_auto
{

    function global_cpnav_extra1()
    {
        return '<a href="plugin.php?id=zeroze007_auto:index">自助中心</a>';
    }

}

class plugin_zeroze007_auto_forum extends plugin_zeroze007_auto
{

}

class mobileplugin_zeroze007_auto extends plugin_zeroze007_auto
{
}
