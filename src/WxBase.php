<?php

namespace Shannan\Wx;

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
			throw new InvalidArgumentException('appid/appsecret均为字符串');
		}
	}

}
