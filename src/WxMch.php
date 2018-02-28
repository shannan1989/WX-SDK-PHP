<?php

namespace Shannan\Wx;

/**
 * 微信商户平台
 */
class WxMch {

	private $_mch_id;
	private $_mch_key;
	private $_mch_ssl_cert;
	private $_mch_ssl_key;

	function __construct__($mch_id, $mch_key, $mch_ssl_cert, $mch_ssl_key) {
		$this->_mch_id = $mch_id;
		$this->_mch_key = $mch_key;
		$this->_mch_ssl_cert = $mch_ssl_cert;
		$this->_mch_ssl_key = $mch_ssl_key;
	}

	private function createNonceStr($length = 16) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	private function getClientIp() {
		$ip = substr(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', 0, 16);
		return $ip;
	}

	private function curl_post_ssl($url, $vars, $second = 30, $headers = array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		//以下两种方式需选择一种
		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, $this->_mch_ssl_cert);
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLKEY, $this->_mch_ssl_key);
		//第二种方式，两个文件合成一个.pem文件
		//curl_setopt($ch, CURLOPT_SSLCERT, '');

		if (count($headers) >= 1) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		$data = curl_exec($ch);
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			curl_close($ch);
			return false;
		}
	}

	/**
	 * 企业付款接口
	 * @param string $trade_no
	 * @param string $app_id
	 * @param string $open_id
	 * @param float $amount
	 * @param string $desc
	 * @return array
	 */
	public function transfers($trade_no, $app_id, $open_id, $amount, $desc) {
		$data = array(
			'partner_trade_no' => $trade_no,
			'mchid' => $this->_mch_id,
			'mch_appid' => $app_id,
			'openid' => $open_id,
			'amount' => $amount * 100,
			'check_name' => 'NO_CHECK',
			'desc' => $desc,
			'spbill_create_ip' => $this->getClientIp(),
			'nonce_str' => $this->createNonceStr(32)
		);
		unset($data['sign']);
		ksort($data);
		$s = '';
		foreach ($data as $key => $value) {
			$s = "{$s}{$key}={$value}&";
		}
		$s .= 'key=' . $this->_mch_key;
		$sign = strtoupper(md5($s));
		$tpl = "
<xml>
	<mch_appid><![CDATA[{$data['mch_appid']}]]></mch_appid>
	<mchid><![CDATA[{$data['mchid']}]]></mchid>
	<nonce_str><![CDATA[{$data['nonce_str']}]]></nonce_str>
	<partner_trade_no><![CDATA[{$data['partner_trade_no']}]]></partner_trade_no>
	<openid><![CDATA[{$data['openid']}]]></openid>
	<check_name><![CDATA[{$data['check_name']}]]></check_name>
	<amount><![CDATA[{$data['amount']}]]></amount>
	<desc><![CDATA[{$data['desc']}]]></desc>
	<spbill_create_ip><![CDATA[{$data['spbill_create_ip']}]]></spbill_create_ip>
	<sign><![CDATA[$sign]]></sign>
</xml>";
		$ret = $this->curl_post_ssl('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', $tpl);
		$t = simplexml_load_string($ret);
		if ($t->return_code == 'SUCCESS' && $t->result_code == 'SUCCESS') {
			return array(
				'success' => true,
				'msg' => (string) $t->return_msg,
				'channel_guid' => (string) $t->payment_no,
				'pay_time' => (string) $t->payment_time
			);
		} else {
			return array(
				'success' => false,
				'msg' => (string) $t->return_msg,
				'err_msg' => (string) $t->err_code_des,
				'err_code' => (string) $t->err_code
			);
		}
	}

}
