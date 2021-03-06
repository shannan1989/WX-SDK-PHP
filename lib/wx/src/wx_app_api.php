<?php

/**
 * 微信小程序消息推送处理接口
 * @author 山南
 */
abstract class WxAppApi extends WxApp {

	private $_token;

	public function __construct($app_id, $app_secret, $token) {
		if (empty($app_id) || empty($app_secret) || empty($token)) {
			throw new InvalidArgumentException('appid/appsecret/token均不能为空');
		}
		if (is_string($app_id) && is_string($app_secret) && is_string($token)) {
			$this->_app_id = $app_id;
			$this->_app_secret = $app_secret;
			$this->_token = $token;
		} else {
			throw new InvalidArgumentException('appid/appsecret/token均应为字符串');
		}

		parent::__construct($app_id, $app_secret);
	}

	/**
	 * valid signature
	 */
	final public function valid() {
		$tmpArr = array($this->_token, $_GET['timestamp'], $_GET['nonce']);
		sort($tmpArr, SORT_STRING);
		$signature = sha1(implode($tmpArr));
		if ($signature == $_GET['signature']) {
			echo $_GET['echostr'];
		}
	}

	/**
	 * 对微信服务器转发至开发者服务器的消息进行响应
	 */
	final public function responseMessage() {
		$xmlReceived = file_get_contents('php://input');
		if (empty($xmlReceived)) {
			echo '';
		} else {
			$dataReceived = WxData::initFromXML($xmlReceived);
			$msg_type = trim($dataReceived['MsgType']);
			switch ($msg_type) {
				case 'event':
					$response = $this->handleEvent($dataReceived);
					break;
				case 'text':
					$response = $this->handleText($dataReceived);
					break;
				case 'image':
					$response = $this->handleImage($dataReceived);
					break;
				default :
					$response = '';
					break;
			}
			echo $response;
		}
	}

	abstract protected function handleEvent($dataReceived);

	abstract protected function handleText($dataReceived);

	abstract protected function handleImage($dataReceived);

	/**
	 * 转发消息至微信网页版客服工具
	 * 如果小程序设置了消息推送，普通微信用户向小程序客服发消息时，微信服务器会先将消息 POST 到开发者填写的 url 上，如果希望将消息转发到网页版客服工具，则需要开发者在响应包中返回 MsgType 为 transfer_customer_service 的消息，微信服务器收到响应后会把当次发送的消息转发至客服系统。
	 * @param array $dataReceived
	 * @return string
	 */
	final protected function transferCustomerService($dataReceived) {
		$tpl = "
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
		return sprintf($tpl, $dataReceived['FromUserName'], $dataReceived['ToUserName'], time());
	}

	/**
	 * 发送文本客服消息
	 * @param string $openid 接收消息的openid
	 * @param string $content 内容
	 * @return array
	 */
	final protected function sendTextCustomMessage($openid, $content) {
		$msg = array(
			'touser' => $openid,
			'msgtype' => 'text',
			'text' => array('content' => $content)
		);
		return $this->sendCustomMessage($msg);
	}

	/**
	 * 发送图片客服消息
	 * @param string $openid 接收消息的openid
	 * @param string $media_id 发送的图片的媒体ID
	 * @return array
	 */
	final protected function sendImageCustomMessage($openid, $media_id) {
		$msg = array(
			'touser' => $openid,
			'msgtype' => 'image',
			'image' => array('media_id' => $media_id)
		);
		return $this->sendCustomMessage($msg);
	}

	/**
	 * 发送图文链接客服消息
	 * @param string $openid 接收消息的openid
	 * @param string $title 标题
	 * @param string $description 描述
	 * @param string $url 跳转链接
	 * @param string $thumb_url 缩略图地址
	 * @return array
	 */
	final protected function sendLinkCustomMessage($openid, $title, $description, $url, $thumb_url) {
		$msg = array(
			'touser' => $openid,
			'msgtype' => 'link',
			'link' => array(
				'title' => $title,
				'description' => $description,
				'url' => $url,
				'thumb_url' => $thumb_url
			)
		);
		return $this->sendCustomMessage($msg);
	}

	/**
	 * 记录日志
	 * @param string $content 日志内容
	 */
	final protected function logger($content) {
		if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') { //LOCAL
			$max_size = 10000;
			$log_filename = Settings::create()->get('app_settings.temp_dir') . 'wx/' . date('Ymd') . '.txt';
			if (file_exists($log_filename) and ( abs(filesize($log_filename)) > $max_size)) {
				unlink($log_filename);
			}
			file_put_contents($log_filename, date('Y-m-d H:i:s') . ' ' . get_class($this) . "\r\n" . $content . "\r\n\r\n", FILE_APPEND);
		}
	}

}
