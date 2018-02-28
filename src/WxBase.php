<?php

namespace Shannan\Wx;

class WxBase {

	protected $_app_id;
	protected $_app_secret;

	public function __construct($app_id, $app_secret) {
		if (empty($app_id) || empty($app_secret)) {
			throw new \InvalidArgumentException('appid/appsecret均不能为空');
		}
		if (is_string($app_id) && is_string($app_secret)) {
			$this->_app_id = $app_id;
			$this->_app_secret = $app_secret;
		} else {
			throw new \InvalidArgumentException('appid/appsecret均为字符串');
		}
	}

	protected function getAccessToken() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->_app_id . '&secret=' . $this->_app_secret;
		$s = self::get($api_url);
		$s1 = json_decode($s, true);
		$access_token = $s1['access_token'];
		return $access_token;
	}

	protected static function get($api_url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$s = curl_exec($ch);
		curl_close($ch);
		return $s;
	}

	protected static function post($api_url, $post) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$s = curl_exec($ch);
		curl_close($ch);
		return $s;
	}

}
