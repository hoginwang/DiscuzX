<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: pay_ali.php 30871 2021-05-01 09:32:37Z Aphly $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class pay_ali extends pay_aliconfig
{
	
	public function status(){
		if($this->status && $this->appId && $this->seller_id && $this->alipayPublicKey && $this->merchantPrivateKey){
			return true;
		}else{
			return false;
		}
	}

	public function pay(){
		$requestConfigs = array(
            'out_trade_no'=>$this->outTradeNo,
            'product_code'=>$this->product_code,
            'total_amount'=>$this->totalAmount,
            'subject'=>$this->subject,
			'passback_params'=>$this->passback_params,
        );
		$commonConfigs = array(
            'app_id' => $this->appId,
            'method' => $this->method,
            'return_url' => $this->returnUrl,
            'charset'=>$this->charset,
            'sign_type'=>$this->signType,
            'timestamp'=>date('Y-m-d H:i:s'),
			'version'=>$this->version,
            'notify_url' => $this->notifyUrl,
            'biz_content'=>json_encode($requestConfigs),
        );
        $commonConfigs["sign"] = self::sign(self::getSignContent($commonConfigs), $this->merchantPrivateKey);
		return $this->buildForm($this->getGatewayServerUrl(),$commonConfigs);
    }

	public function query(string $out_trade_no,string $trade_no=''){
		if(!$out_trade_no){
			return;
		}
		$requestConfigs = array(
            'out_trade_no'=>$out_trade_no,
            'trade_no'=>$trade_no,
        );
        $commonConfigs = array(
            'app_id' => $this->appId,
            'method' => 'alipay.trade.query',
			'sign_type'=>$this->signType,
            'charset'=>$this->charset,
            'timestamp'=>date('Y-m-d H:i:s'),
            'version'=>$this->version,
            'biz_content'=>json_encode($requestConfigs),
        );
        $commonConfigs["sign"] = self::sign(self::getSignContent($commonConfigs), $this->merchantPrivateKey);
		$result = dfsockopen($this->getGatewayServerUrl().'?charset='.$this->charset, 0, $commonConfigs);
		$result = json_decode($result,true);
		return  $result['alipay_trade_query_response'];
	}

	public function refund(string $out_trade_no,float $refund_amount,string $trade_no=''){
		$requestConfigs = array(
            'trade_no'=>$trade_no,
            'out_trade_no'=>$out_trade_no,
            'refund_amount'=>$refund_amount,
        );
        $commonConfigs = array(
            'app_id' => $this->appId,
            'method' => 'alipay.trade.refund',
            'charset'=>$this->charset,
            'sign_type'=>$this->signType,
            'timestamp'=>date('Y-m-d H:i:s'),
            'version'=>$this->version,
            'biz_content'=>json_encode($requestConfigs),
        );
        $commonConfigs["sign"] = self::sign(self::getSignContent($commonConfigs), $this->merchantPrivateKey);
		$result = dfsockopen($this->getGatewayServerUrl().'?charset='.$this->charset, 0, $commonConfigs);
        $resultArr = json_decode($result,true);
		return $resultArr['alipay_trade_refund_response'];
    }


	static public function sign($content, $privateKeyPem){
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($privateKeyPem, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        ($res) or die('privateKeyPem_error');

        openssl_sign($content, $sign, $res, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }

    
	static public function verify($content, $sign, $publicKeyPem){
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($publicKeyPem, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        ($res) or die('publicKeyPem_error');

        $result = FALSE;
        $result = (openssl_verify($content, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1);
		return $result;
    }

	public function notifyverify($parameters){
		return self::verifyParams($parameters,$this->alipayPublicKey);
	}

    static public function verifyParams($parameters, $publicKey){
        $sign = $parameters['sign'];
        $content = self::getSignContent($parameters,true);
        return self::verify($content, $sign, $publicKey);
    }

    static public function getSignContent($params,bool $verify=false){
		ksort($params);
        unset($params['sign']);
		if($verify){
			unset($params['sign_type']);
		}
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

	static public function checkEmpty($value){
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

	public function buildForm($actionUrl,$parameters){
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $actionUrl . "?charset=" . trim($this->charset) . "' method='POST'>";
        while (list ($key, $val) = $this->fun_adm_each($parameters)) {
            if (false === $this->checkEmpty($val)) {
                $val = str_replace("'", "&apos;", $val);
                //$val = str_replace("\"","&quot;",$val);
                $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
            }
        }
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }

	protected function fun_adm_each(&$array){
        $res = array();
        $key = key($array);
        if ($key !== null) {
            next($array);
            $res[1] = $res['value'] = $array[$key];
            $res[0] = $res['key'] = $key;
        } else {
            $res = false;
        }
        return $res;
    }
}