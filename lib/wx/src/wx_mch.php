<?php

/**
 * 微信商户平台
 */
class WxMch {

	private $_mch_id;
	private $_mch_key;
	private $_mch_ssl_cert;
	private $_mch_ssl_key;

	function __construct($mch_id, $mch_key, $mch_ssl_cert, $mch_ssl_key) {
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

	private function postSSLCurl($url, $vars, $second = 30, $headers = []) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second); //设置超时
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

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

		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		$data = curl_exec($ch);
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$errno = curl_errno($ch);
			curl_close($ch);
			throw new Exception('curl出错，错误码:' . $errno);
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
		$ret = $this->postSSLCurl('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', $tpl);
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

	/**
	 * 发放普通红包
	 * @param string $app_id 微信分配的公众账号ID（企业号corpid即为此appId）。接口传入的所有appid应该为公众号的appid（在mp.weixin.qq.com申请的），不能为APP的appid（在open.weixin.qq.com申请的）。
	 * @param string $open_id 接受红包的用户在appid下的openid
	 * @param string $trade_no 商户订单号
	 * @param string $send_name 红包发送者名称
	 * @param int $amount 付款金额，单位分
	 * @param string $wishing 红包祝福语
	 * @param string $act_name 活动名称
	 * @param string $remark 备注信息
	 * @return array
	 */
	public function sendRedPack($app_id, $open_id, $trade_no, $send_name, $amount, $wishing, $act_name, $remark) {
		$data = array(
			'mch_billno' => $trade_no,
			'mch_id' => $this->_mch_id,
			'wxappid' => $app_id,
			'send_name' => $send_name,
			're_openid' => $open_id,
			'total_amount' => $amount,
			'total_num' => 1,
			'wishing' => $wishing,
			'client_ip' => $this->getClientIp(),
			'act_name' => $act_name,
			'remark' => $remark,
			'nonce_str' => $this->createNonceStr(32),
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
    <sign><![CDATA[$sign]]></sign>
    <mch_billno><![CDATA[{$data['mch_billno']}]]></mch_billno>
    <mch_id><![CDATA[{$data['mch_id']}]]></mch_id>
    <wxappid><![CDATA[{$data['wxappid']}]]></wxappid>
    <send_name><![CDATA[{$data['send_name']}]]></send_name>
    <re_openid><![CDATA[{$data['re_openid']}]]></re_openid>
    <total_amount><![CDATA[{$data['total_amount']}]]></total_amount>
    <total_num><![CDATA[{$data['total_num']}]]></total_num>
    <wishing><![CDATA[{$data['wishing']}]]></wishing>
    <client_ip><![CDATA[{$data['client_ip']}]]></client_ip>
    <act_name><![CDATA[{$data['act_name']}]]></act_name>
    <remark><![CDATA[{$data['remark']}]]></remark>
    <nonce_str><![CDATA[{$data['nonce_str']}]]></nonce_str>
</xml>";

		$ret = $this->postSSLCurl('https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack', $tpl);
		$t = simplexml_load_string($ret);
		if ($t->return_code == 'SUCCESS' && $t->result_code == 'SUCCESS') {
			return array(
				'success' => true,
				'msg' => (string) $t->return_msg,
				'send_listid' => (string) $t->send_listid //红包订单的微信单号
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
