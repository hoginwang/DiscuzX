<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: pay_wechat.php 30871 2021-05-01 09:32:37Z Aphly $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class pay_wechat extends pay_wechatconfig
{
	public $auth;
	public $reqUrl;
	public $method = 'POST';

    const AUTH_TAG_LENGTH_BYTE = 16;

	public function status(){
		if($this->status && $this->appid && $this->mchid && $this->privateKey && $this->apiKey3 && $this->certificateSerialNumber && $this->appsecret){
			$paths = $this->getCertPaths();
			if(!empty($paths)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function native()
    {
        $reqParams = array(
            'appid' => $this->appid,
            'mchid' => $this->mchid,
            'description' => $this->description,
            'attach' => $this->attach,
            'out_trade_no' => $this->out_trade_no,
			'notify_url'=> $this->notifyUrl,
            'amount'=>array(
                'total'=>intval($this->amount),
                'currency'=>'CNY',
            )
        );
        $this->reqUrl = $this->gateWay.'/pay/transactions/native';
        $this->getAuthStr($reqParams);
        $response = $this->apiCurl($this->reqUrl,$reqParams);
		return json_decode($response,true);
    }

	public function h5()
    {
		global $_G;
        $reqParams = array(
            'appid' => $this->appid,
            'mchid' => $this->mchid,
            'description' => $this->description,
            'attach' => $this->attach,
            'out_trade_no' => $this->out_trade_no,
			'notify_url'=> $this->notifyUrl,
            'amount'=>array(
                'total'=>intval($this->amount),
                'currency'=>'CNY',
            ),
			'scene_info'=>array(
				"payer_client_ip"=>$_G['clientip'],
				"h5_info"=>array(
					"type"=>"Wap"
				)
			)
        );
        $this->reqUrl = $this->gateWay.'/pay/transactions/h5';
        $this->getAuthStr($reqParams);
        $response = $this->apiCurl($this->reqUrl,$reqParams);
		return json_decode($response,true);
    }

	public function jsapi(string $openid)
    {
        $reqParams = array(
            'appid' => $this->appid,
            'mchid' => $this->mchid,
            'description' => $this->description,
            'attach' => $this->attach,
            'out_trade_no' => $this->out_trade_no,
			'notify_url'=> $this->notifyUrl,
            'amount'=>array(
                'total'=>intval($this->amount),
                'currency'=>'CNY',
            ),
			'payer'=>array('openid'=>$openid)
        );
        $this->reqUrl = $this->gateWay.'/pay/transactions/jsapi';
        $this->getAuthStr($reqParams);
        $response = $this->apiCurl($this->reqUrl,$reqParams);
		$arr = json_decode($response,true);
		if(!empty($arr['prepay_id'])){
			$timestamp = time();
			$nonceStr = $this->getNonce();
			$prepay = 'prepay_id='.$arr['prepay_id'];
			$message = $this->buildJs($prepay,$timestamp,$nonceStr);
			$signature = $this->sign($message);
			$res=array('appId'=>$this->appid,
				'timeStamp'=>$timestamp,
				'nonceStr'=>$nonceStr,
				'package'=>$prepay,
				'signType'=>"RSA",
				'paySign'=>$signature
			);
			return $res;
		}
        return array();
    }

	private function buildJs($prepay_id,$timestamp,$nonceStr)
    {
        return $this->appid . "\n" .
            $timestamp . "\n" .
            $nonceStr . "\n" .
            $prepay_id . "\n";
    }

	public function refund()
    {
		$this->amount = intval($this->amount);
		if(!$this->refund){
			$this->refund = $this->amount;
		}
        $reqParams = array(
            'out_trade_no' => $this->out_trade_no,
			'out_refund_no' => $this->out_refund_no,
            'amount'=>array(
                'total'=>intval($this->amount),
                'currency'=>'CNY',
				"refund"=>$this->refund
            )
        );
		if($this->transaction_id){
			$reqParams['transaction_id']=$this->transaction_id;
		}
        $this->reqUrl = $this->gateWay.'/refund/domestic/refunds';
        $this->getAuthStr($reqParams);
        $response = $this->apiCurl($this->reqUrl,$reqParams);
        return json_decode($response,true);
    }
	

	public function query(string $out_trade_no)
    {	
		if(!$out_trade_no){
			return;
		}
        $this->reqUrl = $this->gateWay.'/pay/transactions/out-trade-no/'.$out_trade_no.'?mchid='.$this->mchid;
		$this->method = 'GET';
        $this->getAuthStr();
        $response = $this->apiCurl($this->reqUrl);
        return json_decode($response,true);
    }
	
	public function apiCurl($url = '',array $postData = array(),array $header = array(),$timeout=15)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if (!empty($postData)) {
            $postData = json_encode($postData);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
		if(!empty($header)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }else{
			$header = array(
				'Authorization:'.$this->auth,
				'Content-Type:application/json',
				'Accept:application/json',
				'User-Agent:'.$_SERVER['HTTP_USER_AGENT']
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $res = curl_exec($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if($errno) {
			return;
		}else{
			return $res;
		}
    }

	public function getCertificates(){
		$this->reqUrl = $this->gateWay.'/certificates';
		$this->method = 'GET';
		$this->getAuthStr($reqParams);
		$res = $this->apiCurl($this->reqUrl);
		$list = json_decode($res, true);
		if(isset($list['data'])){
			$path = $this->saveCertPath($list);
		}
		return $path;
	}

	public function getAuthStr($reqParams=array())
    {
        $token = $this->getToken($reqParams);
		$this->auth = $this->auth_type.' '.$token;
        return $this->auth;
    }

	public function getKey($privateKeyPem){
        $res = "-----BEGIN PRIVATE KEY-----\n" .
            wordwrap($privateKeyPem, 64, "\n", true) .
            "\n-----END PRIVATE KEY-----";
        ($res) or die('privateKeyPem_error');
        return $res;
    }
	
	public function getToken($reqParams)
    {
        $nonce = $this->getNonce();
        $timestamp = time();
		$body = $reqParams ? json_encode($reqParams) : '';
        $message = $this->buildMessage($nonce, $timestamp,$body);
        $signature = $this->sign($message);
        return sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->mchid, $nonce, $timestamp, $this->certificateSerialNumber, $signature
        );
    }

	public function sign($message)
    {
        if (!in_array('sha256WithRSAEncryption', openssl_get_md_methods(true))) {
            die('sha256WithRSAEncryption_error');
        }
		$private_id = openssl_get_privatekey($this->getKey($this->privateKey));
		//$private_id = openssl_get_privatekey(file_get_contents(DISCUZ_ROOT.$this->privateKeyPath));
        if (!openssl_sign($message, $raw_sign, $private_id, 'sha256WithRSAEncryption')) {
            die('sign_error');
        }
        return base64_encode($raw_sign);
    }

	public function saveCertPath($list)
	{
		$plainCerts = [];
		$x509Certs = [];

		foreach ($list['data'] as $item) {
			$encCert = $item['encrypt_certificate'];
			$plain = $this->decryptToString($encCert['associated_data'],$encCert['nonce'], $encCert['ciphertext']);
			if (!$plain) {
				echo "encrypted certificate decrypt fail!\n";
				exit(1);
			}
			$cert = openssl_x509_read($plain); 
			if (!$cert) {
				echo "downloaded certificate check fail!\n";
				exit(1);
			}
			$plainCerts[] = $plain;
			$x509Certs[] = $cert;
		}
		$this->delCertPaths();
		$path=array();
		foreach ($list['data'] as $index => $item) {
			$path[$index] = $this->cert.'/wechatpay_'.$item['serial_no'].'.pem';
			$outpath =  DISCUZ_ROOT.$path[$index];
			file_put_contents($outpath, $plainCerts[$index]);
		}
		return $path;
	}

	public function getCertPaths()
	{
		return array_diff(scandir(DISCUZ_ROOT.$this->cert), ['.', '..']);
	}

	public function delCertPaths()
	{
		$paths = $this->getCertPaths();
		foreach($paths as $v){
			if(strrchr($v , '.')=='.pem'){
				unlink(DISCUZ_ROOT.$this->cert.'/'.$v);
			}
		}
	}

	private function buildMessage($nonce, $timestamp, $body = '')
    {
        $url_parts = parse_url($this->reqUrl);
		$canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        return $this->method . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";
    }

	protected function getNonce()
    {
        static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 32; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

	protected function checkTimestamp($timestamp)
    {
        return abs((int)$timestamp - time()) <= 120;
    }

    public function decrypt($body)
    {
        $postData = json_decode($body, true);
        if($postData['resource']) {
            $data = $this->decryptToString($postData['resource']['associated_data'], $postData['resource']['nonce'], $postData['resource']['ciphertext']);
            $data = json_decode($data, true);
            return is_array($data) ? $data : false;
        }
        return false;
    }

	public function decryptToString($associatedData, $nonceStr, $ciphertext)
    {
        $ciphertext = base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return false;
        }

        // ext-sodium (default installed on >= PHP 7.2)
        if (function_exists('sodium_crypto_aead_aes256gcm_is_available') && sodium_crypto_aead_aes256gcm_is_available()) {
            return sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->apiKey3);
        }

        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->apiKey3);
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);
            return openssl_decrypt($ctext, 'aes-256-gcm', $this->apiKey3, OPENSSL_RAW_DATA, $nonceStr, $authTag, $associatedData);
        }
		//AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php
        exit('php>=7.1 or libsodium-php');
    }

    public function validate(array $notify)
    {
//        if (!$this->checkTimestamp($notify['timestamp'])) {
//            return false;
//        }
        
        $message = "{$notify['timestamp']}\n{$notify['nonce']}\n{$notify['body']}\n";
		$certificates = $this->getCertPaths();
		$this->getPublicKeys($certificates);
        return $this->verify($notify['serialNo'],$message, $notify['sign']);
    }

	public function getPublicKeys($certificates)
	{
		foreach ($certificates as $certificate) {
			$cid = openssl_x509_read(file_get_contents(DISCUZ_ROOT.$this->cert.'/'.$certificate));
			$serialNo = $this->parseCertificateSerialNo($cid);
			$this->publicKeys[$serialNo] = openssl_get_publickey($cid);
		}
	}

	public function verify($serialNumber, $message, $signature)
    {
        $serialNumber = strtoupper(ltrim($serialNumber, '0')); 
        if (!isset($this->publicKeys[$serialNumber])) {
            return false;
        }
        if (!in_array('sha256WithRSAEncryption', openssl_get_md_methods(true))) {
            exit('sha256WithRSAEncryption_error');
        }
        $signature = base64_decode($signature);
        return (openssl_verify($message, $signature, $this->publicKeys[$serialNumber],'sha256WithRSAEncryption')===1);
    }


	public function parseCertificateSerialNo($certificate)
    {
        $info = openssl_x509_parse($certificate);
        if (!isset($info['serialNumber']) && !isset($info['serialNumberHex'])) {
            exit('cert_error');
        }

        $serialNo = '';
        // PHP 7.0+ provides serialNumberHex field
        if (isset($info['serialNumberHex'])) {
            $serialNo = $info['serialNumberHex'];
        } else {
            if (strtolower(substr($info['serialNumber'], 0, 2)) == '0x') { // HEX format
                $serialNo = substr($info['serialNumber'], 2);
            } else {
                $value = $info['serialNumber'];
                $hexvalues = ['0','1','2','3','4','5','6','7',
                    '8','9','A','B','C','D','E','F'];
                while ($value != '0') {
                    $serialNo = $hexvalues[bcmod($value, '16')].$serialNo;
                    $value = bcdiv($value, '16', 0);
                }
            }
        }
        return strtoupper($serialNo);
    }


    public function GetOpenid()
	{
		global $_G;
		if (!isset($_GET['code'])){
			$cururl = base64_decode($_G['currenturl_encode']);
			$baseUrl = urlencode($cururl);
			$url = $this->_CreateOauthUrlForCode($baseUrl);
			Header("Location: $url");
			exit();
		} else {
		    $code = $_GET['code'];
			$openid = $this->getOpenidFromMp($code);
			return $openid;
		}
	}

	private function _CreateOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}

	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}

	private function __CreateOauthUrlForOpenid($code)
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["secret"] = $this->appsecret;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}

	public function GetOpenidFromMp($code)
	{
		$url = $this->__CreateOauthUrlForOpenid($code);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($res,true);
		$openid = $data['openid'];
		return $openid;
	}

}