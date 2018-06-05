<?php

/**
 * 微信公众号（含服务号与订阅号）消息推送处理接口
 * @author 山南
 */
abstract class WxMpApi extends WxMp {

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
		sort($tmpArr);
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
	 * 生成回复文本内容的XML
	 * @param array $dataReceived
	 * @param string $content
	 * @return string
	 */
	final protected function transmitText($dataReceived, $content) {
		$tpl = "
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
		return sprintf($tpl, $dataReceived['FromUserName'], $dataReceived['ToUserName'], time(), $content);
	}

	/**
	 * 发送文本客服消息
	 * @param array $dataReceived
	 * @param string $content 内容
	 * @return array
	 */
	final protected function sendTextCustomMessage($dataReceived, $content) {
		$msg = array(
			'touser' => $dataReceived['FromUserName'],
			'msgtype' => 'text',
			'text' => array('content' => $content)
		);
		return $this->sendCustomMessage($msg);
	}

	/**
	 * 记录日志
	 * @param string $content 日志内容
	 */
	final protected function logger($content) {
		//return;
		if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') { //LOCAL
			$max_size = 10000;
			$log_filename = Settings::create()->get('app_settings.temp_dir') . 'weixin/' . date('Ymd') . '_log.txt';
			if (file_exists($log_filename) and ( abs(filesize($log_filename)) > $max_size)) {
				unlink($log_filename);
			}
			file_put_contents($log_filename, date('Y-m-d H:i:s') . ' ' . get_class($this) . "\r\n" . $content . "\r\n\r\n", FILE_APPEND);
		}
	}

}