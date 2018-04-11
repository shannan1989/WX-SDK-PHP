<?php

/**
 * 微信公众号和小程序的基础类
 * @author 山南
 */
class WxBase {

	protected $_app_id;
	protected $_app_secret;

	public function __construct($app_id, $app_secret) {
		if (empty($app_id) || empty($app_secret)) {
			throw new InvalidArgumentException('appid/appsecret均不能为空');
		}
		if (is_string($app_id) && is_string($app_secret)) {
			$this->_app_id = $app_id;
			$this->_app_secret = $app_secret;
		} else {
			throw new InvalidArgumentException('appid/appsecret均应为字符串');
		}
	}

	/**
	 * 获取access_token
	 * access_token是公众号的全局唯一接口调用凭据，公众号调用各接口时都需使用access_token。开发者需要进行妥善保存。
	 * access_token的存储至少要保留512个字符空间。
	 * access_token的有效期目前为2个小时，需定时刷新，重复获取将导致上次获取的access_token失效。
	 * @return string access_token
	 */
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
