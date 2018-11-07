<?php

/**
 * 微信公众号（含服务号与订阅号）
 * @author 山南
 */
class WxMp extends WxBase {

	/**
	 * 获取微信服务器IP地址
	 * 如果公众号基于安全等考虑，需要获知微信服务器的IP地址列表，以便进行相关限制，可以通过该接口获得微信服务器IP地址列表或者IP网段信息。
	 * @return array
	 */
	public function getCallbackIp() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=' . $this->getAccessToken();
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 获取用户基本信息（包括UnionID机制）
	 * @param string $open_id 普通用户的标识，对当前公众号唯一
	 * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
	 * @return array
	 */
	public function getUserInfo($open_id, $lang = 'zh_CN') {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->getAccessToken() . '&openid=' . $open_id . '&lang=' . $lang;
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 获取用户列表
	 * 公众号可通过本接口来获取帐号的关注者列表，关注者列表由一串OpenID（加密后的微信号，每个用户对每个公众号的OpenID是唯一的）组成。一次拉取调用最多拉取10000个关注者的OpenID，可以通过多次拉取的方式来满足需求。
	 * @param string $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
	 * @return array 返回值
	 * 	total：关注该公众账号的总用户数
	 * 	count：拉取的OPENID个数，最大值为10000
	 *  data：列表数据，OPENID的列表
	 * 	next_openid：拉取列表的最后一个用户的OPENID
	 */
	public function getUserList($next_openid) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $this->getAccessToken() . '&next_openid=' . $next_openid;
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 设置用户备注名
	 * @param string $openid 用户标识
	 * @param string $remark 新的备注名，长度必须小于30字符
	 * @return array
	 */
	public function updateUserRemark($openid, $remark) {
		$post = array(
			'openid' => $openid,
			'remark' => $remark
		);
		$api_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->getAccessToken();
		$s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 公众号发送模板消息
	 * @param array $msg 模板消息内容
	 * @return array
	 */
	public function sendTemplateMessage($msg) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->getAccessToken();
		$s = self::post($api_url, json_encode($msg, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 公众号发送客服消息
	 * @param array $msg 客服消息内容
	 * @return array
	 */
	public function sendCustomMessage($msg) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $this->getAccessToken();
		$s = self::post($api_url, json_encode($msg, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 获取公众号已创建的标签
	 * @return array
	 */
	public function getTags() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/tags/get?access_token=' . $this->getAccessToken();
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 获取默认菜单和全部个性化菜单信息
	 * @return array
	 */
	public function getMenu() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $this->getAccessToken();
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 创建自定义菜单
	 * @param array $menu
	 * @return array
	 */
	public function createMenu($menu) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getAccessToken();
		$s = self::post($api_url, json_encode($menu, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 删除默认菜单及全部个性化菜单
	 * @return array
	 */
	public function deleteMenu() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $this->getAccessToken();
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 创建个性化菜单
	 * @param array $menu
	 * @return array
	 */
	public function addConditionalMenu($menu) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=' . $this->getAccessToken();
		$s = self::post($api_url, json_encode($menu, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 获取自定义菜单配置
	 * 本接口与自定义菜单查询接口的不同之处在于，本接口无论公众号的接口是如何设置的，都能查询到接口，而自定义菜单查询接口则仅能查询到使用API设置的菜单配置。
	 * @return array
	 */
	public function getCurrentSelfMenu() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=' . $this->getAccessToken();
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 获取公众号的自动回复规则，包括关注后自动回复、消息自动回复（60分钟内触发一次）、关键词自动回复
	 * @return array
	 */
	public function getCurrentAutoreplyInfo() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/get_current_autoreply_info?access_token=' . $this->getAccessToken();
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 获取素材总数
	 * @return array
	 */
	public function getMaterialCount() {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=' . $this->getAccessToken();
		$s = self::get($api_url);
		return json_decode($s, true);
	}

	/**
	 * 获取素材列表，可以分类型获取永久素材的列表
	 * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材返回
	 * @param int $count 返回素材的数量，取值在1到20之间
	 * @return array
	 */
	public function getMaterials($type, $offset, $count) {
		$post = array(
			'type' => $type,
			'offset' => $offset,
			'count' => $count
		);
		$api_url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $this->getAccessToken();
		$s = self::post($api_url, json_encode($post, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 将一条长链接转成短链接。
	 * 主要使用场景： 开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。
	 * @param string $long_url 需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
	 * @return array
	 */
	public function shortUrl($long_url) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=' . $this->getAccessToken();
		$data = array(
			'action' => 'long2short',
			'long_url' => $long_url
		);
		$s = self::post($api_url, json_encode($data, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 生成带参数的二维码
	 * @param string $action_name 二维码类型，QR_SCENE为临时的整型参数值，QR_STR_SCENE为临时的字符串参数值，QR_LIMIT_SCENE为永久的整型参数值，QR_LIMIT_STR_SCENE为永久的字符串参数值
	 * @param int $scene_id 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
	 * @param string $scene_str 场景值ID（字符串形式的ID），字符串类型，长度限制为1到64
	 * @param int $expire_seconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为30秒。
	 */
	public function createQrCode($action_name, $scene_id, $scene_str, $expire_seconds = 600) {
		$api_url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getAccessToken();
		$data = array(
			'action_name' => $action_name,
			'action_info' => array(
				'scene' => array(
					'scene_id' => $scene_id,
					'scene_str' => $scene_str
				)
			),
			'expire_seconds' => $expire_seconds
		);
		$s = self::post($api_url, json_encode($data, JSON_UNESCAPED_UNICODE));
		return json_decode($s, true);
	}

	/**
	 * 通过ticket换取二维码
	 * @param string $ticket
	 * @return string ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。错误情况下（如ticket非法）返回HTTP错误码404。
	 */
	public static function showQrCode($ticket) {
		return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
	}

	/**
	 * 获取微信授权页面地址
	 * @param string $redirect_uri 授权后重定向的回调链接地址
	 * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
	 * @param bool $userinfo 应用授权作用域，false -> snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），true -> snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）
	 * @return string
	 */
	public function getOAuthUrl($redirect_uri, $state, $userinfo = false) {
		$query = array(
			'appid' => $this->_app_id,
			'redirect_uri' => $redirect_uri,
			'response_type' => 'code',
			'scope' => $userinfo ? 'snsapi_userinfo' : 'snsapi_base',
			'state' => $state
		);
		return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($query) . '#wechat_redirect';
	}

	/**
	 * 通过code换取网页授权access_token。本步骤中获取到网页授权access_token的同时，也获取到了openid。
	 * @param string $code 授权code
	 * @return array
	 */
	public function getOAuthAccessToken($code) {
		$query = array(
			'appid' => $this->_app_id,
			'secret' => $this->_app_secret,
			'code' => $code,
			'grant_type' => 'authorization_code'
		);
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query($query);
		$s = self::get($url);
		return json_decode($s, true);
	}

	/**
	 * 拉取用户信息(需scope为 snsapi_userinfo)
	 * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
	 * @param string $openid 用户的唯一标识
	 * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
	 * @return array
	 */
	public function getOAuthUserInfo($access_token, $openid, $lang = 'zh_CN') {
		$query = array(
			'access_token' => $access_token,
			'openid' => $openid,
			'lang' => $lang
		);
		$url = 'https://api.weixin.qq.com/sns/userinfo?' . http_build_query($query);
		$s = self::get($url);
		return json_decode($s, true);
	}

}
