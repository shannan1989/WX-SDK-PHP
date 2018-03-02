<?php

/**
 * 微信小程序
 */
class WxApp extends WxBase {

	/**
	 * 开发者服务器使用登录凭证 code 获取 session_key 和 openid
	 * @param string $code 登录时获取的 code
	 * @return array
	 */
	public function jsCode2Session($code) {
		$api_url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->_app_id . '&secret=' . $this->_app_secret . '&js_code=' . $code . '&grant_type=authorization_code';
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 生成小程序二维码
	 * @param string $path 对应页面
	 * @param int $width 二维码宽度，默认为430
	 * @return blob 二维码的二进制内容
	 */
	public function createWxaQrcode($path, $width = 430) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=' . $this->getAccessToken();
		$post = array(
			'path' => $path,
			'width' => $width
		);
		$s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
		return $s;
	}

	/**
	 * 生成小程序码，有数量限制
	 * @param string $path 对应页面
	 * @param int $width 小程序码宽度，默认为430
	 * @return blob 小程序码的二进制内容
	 */
	public function getWxaCode($path, $width = 430) {
		$api_url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=' . $this->getAccessToken();
		$post = array(
			'path' => $path,
			'width' => $width
		);
		$s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
		return $s;
	}

	/**
	 * 生成小程序码，无数量限制
	 * @param string $scene 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
	 * @param string $page 必须是已经发布的小程序存在的页面（否则报错），例如 "pages/index/index" ,根路径前不要填加'/',不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
	 * @param int $width 小程序码宽度，默认为430
	 * @return blob 小程序码的二进制内容
	 */
	public function getWxaCodeUnlimit($scene, $page, $width = 430) {
		$api_url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getAccessToken();
		$post = array(
			'scene' => $scene,
			'page' => $page,
			'width' => $width
		);
		$s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
		return $s;
	}

}
