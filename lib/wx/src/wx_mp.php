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

}
