<?php
if(!function_exists('get_headers')){
	function get_headers($url,$format=0){
		$url = parse_url($url);
		$end = "£Ür£Ün£Ür£Ün";
		$fp = fsockopen($url['host'],(empty($url['port'])?80:$url['port']),$errno,$errstr,30);
		if($fp){
			$out="GET / HTTP/1.1£Ür£Ün";
			$out.="Host: ".$url['host']."£Ür£Ün";
			$out.="Connection: Close£Ür£Ün£Ür£Ün";
			$var='';
			fwrite($fp,$out);
			while(!feof($fp)){
				$var.=fgets($fp,1280);
				if(strpos($var,$end))
					break;
			}
			fclose($fp);
			$var=preg_replace("/£Ür£Ün£Ür£Ün.*£Ü$/",'',$var);
			$var=explode("£Ür£Ün",$var);
			if($format){
				foreach($var as $i){
					if(preg_match('/^([a-zA-Z -]+): +(.*)$/',$i,$parts))
						$v[$parts[1]]=$parts[2];
				}
				return $v;
			}else{
				return $var;
			}
		}
	}
}
if(!function_exists('_get_client_ip')){
	function _get_client_ip() {
		$ip = $_SERVER['REMOTE_ADDR'];
		if(isset($_SERVER['HTTP_X_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}elseif(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(isset($_SERVER['HTTP_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip == '::1' ? '127.0.0.1' : $ip;
	}
}

if(!empty($_SERVER["HTTP_USER_AGENT"])){
	echo 'USER_AGENT:'.$_SERVER["HTTP_USER_AGENT"];
	echo '<br>';
}
echo 'IP_Address:'._get_client_ip().'<br/>';
if(isset($_SERVER["HTTP_REFERER"])){
	echo 'Referer:'.$_SERVER["HTTP_REFERER"];
	echo '<br>';
}
echo '<pre>';
#print_r(get_headers('http://www.yueing.org'));

#var_dump($_SERVER);


?>
