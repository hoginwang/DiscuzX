<?php
/**
 *	[DIY(zeroze007diy.{verifycenter.inc})] (C)2016-2099 Powered by jiangyou.
 *	Version：1.0
 *	Date：2016-3-12 14:24
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$typearray = array('pcs', 'qun');
if(!$_G['gp_type'] || !in_array($_G['gp_type'], $typearray)){
	exit('invalid request');
}
$type = $_G['gp_type'];
$code = $_G['gp_code'];
if($type == 'pcs'){
	$UploaderPrefix = true;
	$DownloaderPrefix = true;
	$UploaderPrefixName = lang('plugin/zeroze007diy', 'UPrefixNameDef');
	$DownloaderPrefixName = lang('plugin/zeroze007diy', 'DPrefixNameDef');
	
	$demo = C::t("#zeroze007diy#diy007_code")->fetch_first_by_code($code);
	if(empty($demo)){
		$demo = array();
		$demo['Code'] = $code;
		$demo['Status'] = '';
		$demo['Result'] = true;
		$demo['Msg'] = 'Invalid Code';
		$demo['UploaderPrefix'] = $UploaderPrefix;
		$demo['DownloaderPrefix'] = $DownloaderPrefix;
		$demo['UploaderPrefixName'] = $UploaderPrefixName;
		$demo['DownloaderPrefixName'] = $DownloaderPrefixName;
		$demo['Dateline'] = date("Y-m-d\TH:i:s", time());
	}else{
		unset($demo['uid']);
		$demo['Msg'] = 'Ok';
	}
	/*
	$demo['Code'] = $code;
	$demo['Status'] = "1";
	$demo['Result'] = true;
	$demo['Msg'] = "";
	$demo['UploaderPrefix'] = true;
	$demo['DownloaderPrefix'] = true;
	$demo['UploaderPrefixName'] = "[yueing.org 悦影天下]";
	$demo['DownloaderPrefixName'] = "[yueing.org 悦影天下]";
	$demo['Dateline'] = date("Y-m-d\TH:i:s", time());
	*/
	echo json_encode($demo ,JSON_UNESCAPED_UNICODE);
}else if ($type == 'qun'){
	if(!$_G['uid']){
		header('HTTP/1.1 404 Not Found');
		header('status：404 Not Found');
		include('404/index.html');
		dexit();
	}
	include template('common/header');
print <<<EOT
<div id="ct" class="wp cl w">
	<div class="nfl">
		<div class="f_c altw">
			<div id="messagetext" class="alert_info">
				<h1>&#21152;&#32676;&#20195;&#30721;&#39564;&#35777;&#31995;&#32479;</h1>
				<p class="alert_btnleft">
				<form method="post">
					<table>
					<tbody>
					  <tr><td align="right">&#21152;&#32676;&#20195;&#30721;：</td><td><input type="text" name="code" ><input type="submit" value="&#26597;&#35810;"></td></tr>
					</tbody>
EOT;
if($_SERVER['REQUEST_METHOD'] == 'POST' && $code){
	$currentTIme = date('Y-m-d H:i:s',time());
	print <<<EOT
					<tr><td align="right">&nbsp;</td><td>&nbsp;</td></tr>
					<tr><td align="right">&#21152;&#32676;&#20195;&#30721;：</td><td>$code</td></tr>
					<tr><td align="right">&#31995;&#32479;&#26102;&#38388;：</td><td>$currentTIme</td></tr>
EOT;
	include_once DISCUZ_ROOT.'/source/plugin/zeroze007diy/aes.class.php';
	$aes = new AES();
	if($code_des = $aes->decrypt($_POST['code'])){
		$code_arr = explode('|',$code_des);
		$uid = '<font color="red">0</font>';
		$username = '<font color="red">&#28216;&#23458;</font>';
		$usergroup = '<font color="red">&#28216;&#23458;</font>';
		$excgroup = '<font color="red">&#19981;&#20801;&#35768;</font>';
		$vipgroup = '<font color="red">&#19981;&#20801;&#35768;</font>';
		
		$generateTime = date('Y-m-d H:i:s',$code_arr[1]);
		$intervalTIme = time() - intval($code_arr[1]);
		
		if($code_arr[0]){
			$user = getuserbyuid(intval($code_arr[0]), 1);
			$group = C::t('common_usergroup')->fetch($user['groupid']);
			$membercount = C::t('common_member_count')->fetch($user['uid']);
			if($user && $group){
				$uid = $user['uid'];
				$username = $user['username'];
				$username = "<a href=\"home.php?mod=space&uid=$uid\" target=\"_blank\">$username</a>";
				$usergroup = $group['grouptitle'];
				$admGrops = Array(1, 2, 3, 16, 17, 18, 19, 21 ,22);
				$vipGrops = Array(34,35,36,37);
				if($intervalTIme < 60*60*12){
					if(in_array($user['adminid'], Array(1,2,3)) || in_array($user['groupid'], $admGrops) || in_array($user['groupid'], $vipGrops)) {
						$excgroup = '<font color="green">&#20801;&#35768;</font>';
						$vipgroup = '<font color="green">&#20801;&#35768;</font>';
					}else if($membercount['oltime'] >= 24 && $user['regdate'] <= strtotime('-7 day')){
						$excgroup = '<font color="green">&#20801;&#35768;</font>';
					}
				}
			}
		}
		print <<<EOT
						  <tr><td align="right">&#29983;&#25104;&#26102;&#38388;：</td><td>$generateTime</td></tr>
						  <tr><td align="right">&#38388;&#38548;&#26102;&#38388;：</td><td>$intervalTIme 秒</td></tr>
						  <tr><td align="right">UID：</td><td><a href="home.php?mod=space&uid=$uid" target="_blank">$uid</a></td></tr>
						  <tr><td align="right">&#29992;&#25143;&#21517;：</td><td>$username</td></tr>
						  <tr><td align="right">&#29992;&#25143;&#32452;：</td><td>$usergroup</td></tr>
						  <!--<tr><td align="right">VIP&#32676;：</td><td>$vipgroup</td></tr>-->
						  <tr><td align="right">&#20132;&#27969;&#32676;：</td><td>$excgroup</td></tr>
EOT;
	}else{
		print <<<EOT
						  <tr><td align="right">&#25552;&#31034;：</td><td><font color="red">&#20195;&#30721;&#38169;&#35823;,&#26080;&#27861;&#35299;&#26512;</font></td></tr>
EOT;
	}
}
print <<<EOT
				  </table>
				</form>
				</p>
			</div>
		</div>
	</div>
</div>
EOT;
include template('common/footer');
}
?>

