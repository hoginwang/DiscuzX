<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
//include template('common/header');
$op = $_G['gp_op'];
$subop = $_G['gp_subop'];
$paymoney = $_G['gp_paymoney'];
if ($op == "account") {
    if (in_array($subop, array('unlock', 'check')) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = trim($_G['gp_email']);
        $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($isEmail === false) {
            echo '<script>alert("邮箱名非法！");window.history.go(-1);</script>';
            exit;
        }
        $user = C::t('common_member')->fetch_by_email($email, 1);
        if (empty($user)) {
            echo '<script>alert("用户不存在！");window.history.go(-1);</script>';
            exit;
        }

        if ($user['freeze'] == 1 && $user['status'] == -1 && $user['freezetime']) {//检测账户状态：是否处于锁定状态
            $wheresql[] = "uid = " . $user['uid'];
            $wheresql[] = "action = 6";
            $wheresql[] = "operatorid = 0";
            $wheresql[] = "dateline = " . $user['freezetime'];
            $wheresql[] = "reason like '%不活跃%'";
            $flag = C::t('common_member_crime')->count_by_where('WHERE ' . implode(' AND ', $wheresql), 0, 1);
            if (!$flag) {//非系统自动锁定，不允许自助接近
                echo '<script>alert("该用户不支持自助解封！");window.history.go(-1);</script>';
                exit;
            }
            $user = array_merge($user, C::t('common_member_status')->fetch($user['uid']), C::t('common_member_count')->fetch($user['uid']));
            $monthSum = (date("Y", TIMESTAMP) - date("Y", $user['freezetime'])) * 12 + (date("m", TIMESTAMP) - date("m", $user['freezetime']));
            //解封需要悦币
            $needMoney = max(abs($monthSum * 10 + 10), 10);

            if ($subop == 'unlock' && ($needMoney <= $user['extcredits2'] || $paymoney)) {
                if ($needMoney <= $user['extcredits2']) {
                    updatecreditbyaction('', $user['uid'], array('extcredits2' => "-$needMoney"));
                    C::t('common_member')->update($user['uid'], array('status' => 0, 'freezetime' => 0));
                } elseif ($paymoney) {
                    updatecreditbyaction('', $user['uid'], array('extcredits2' => "-" . abs($user['extcredits2'])));
                    C::t('common_member')->update($user['uid'], array('status' => 0, 'freezetime' => 0));
                    C::t('common_member_status')->update($user['uid'], array('lastpost' => time()));
                }
                notification_add(
                    $user['uid'], 'system', '温馨提示：为避免再次被系统锁定，请24小时内回复任意主题，以解除违规状态！<br/>总版规：<a href="forum.php?mod=viewthread&tid=2">前往查看</a><br/>如有疑问请至接待中心反馈!',
                    array(),
                    1
                );
                showmessage('您的账号已解除锁定，请前往登录！', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
            } else {
                include template('zeroze007_auto:auto_unlock_confirm');
            }
        } else {
            echo '<script>alert("该用户不适用自助解封！");window.history.go(-1);</script>';
            exit;
        }
    } else {
        include template('zeroze007_auto:auto_unlock');
    }
} else {
    include template('zeroze007_auto:auto_unlock');
}


