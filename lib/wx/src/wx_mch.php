<?php

/**
 * 微信商户平台
 * @author 山南
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

	/**
	 * 随机字符串
	 * @param int $length 长度
	 * @return string
	 */
	private static function getNonceStr($length = 32) {
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
		$wx_data = new WxData();
		$wx_data->setMchKey($this->_mch_key);
		$wx_data->setValue('partner_trade_no', $trade_no);
		$wx_data->setValue('mchid', $this->_mch_id);
		$wx_data->setValue('mch_appid', $app_id);
		$wx_data->setValue('openid', $open_id);
		$wx_data->setValue('amount', $amount * 100);
		$wx_data->setValue('check_name', 'NO_CHECK');
		$wx_data->setValue('desc', $desc);
		$wx_data->setValue('spbill_create_ip', $this->getClientIp());
		$wx_data->setValue('nonce_str', self::getNonceStr(32));
		$wx_data->setSign();
		$xml = $wx_data->toXML();

		$ret = $this->postSSLCurl('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', $xml);
		$t = WxData::initFromXML($ret);
		if ($t['return_code'] == 'SUCCESS' && $t['result_code'] == 'SUCCESS') {
			return array(
				'success' => true,
				'msg' => $t['return_msg'],
				'channel_guid' => $t['payment_no'],
				'pay_time' => $t['payment_time']
			);
		} else {
			return array(
				'success' => false,
				'msg' => $t['return_msg'],
				'err_msg' => $t['err_code_des'],
				'err_code' => $t['err_code']
			);
		}
	}

	/**
	 * 发放普通红包
	 * @param string $app_id 微信分配的公众账号ID（企业号corpid即为此appId）。接口传入的所有appid应该为公众号的appid（在mp.weixin.qq.com申请的），不能为APP的appid（在open.weixin.qq.com申请的）。
	 * @param string $open_id 接受红包的用户在appid下的openid
	 * @param string $trade_no 商户订单号
	 * @param string $send_name 红包发送者名称
	 * @param float $amount 付款金额，单位元
	 * @param string $wishing 红包祝福语
	 * @param string $act_name 活动名称
	 * @param string $remark 备注信息
	 * @return array
	 */
	public function sendRedPack($app_id, $open_id, $trade_no, $send_name, $amount, $wishing, $act_name, $remark) {
		$wx_data = new WxData();
		$wx_data->setMchKey($this->_mch_key);
		$wx_data->setValue('mch_billno', $trade_no);
		$wx_data->setValue('mch_id', $this->_mch_id);
		$wx_data->setValue('wxappid', $app_id);
		$wx_data->setValue('send_name', $send_name);
		$wx_data->setValue('re_openid', $open_id);
		$wx_data->setValue('total_amount', $amount * 100);
		$wx_data->setValue('total_num', 1);
		$wx_data->setValue('wishing', $wishing);
		$wx_data->setValue('client_ip', $this->getClientIp());
		$wx_data->setValue('act_name', $act_name);
		$wx_data->setValue('remark', $remark);
		$wx_data->setValue('nonce_str', self::getNonceStr(32));
		$wx_data->setSign();
		$xml = $wx_data->toXML();

		$ret = $this->postSSLCurl('https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack', $xml);
		$t = WxData::initFromXML($ret);
		if ($t['return_code'] == 'SUCCESS' && $t['result_code'] == 'SUCCESS') {
			return array(
				'success' => true,
				'msg' => $t['return_msg'],
				'send_listid' => $t['send_listid'] //红包订单的微信单号
			);
		} else {
			return array(
				'success' => false,
				'msg' => $t['return_msg'],
				'err_msg' => $t['err_code_des'],
				'err_code' => $t['err_code']
			);
		}
	}

	/**
	 * 申请退款
	 * @param string $app_id 微信分配的公众号/小程序ID
	 * @param string $out_trade_no 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|*@ ，且在同一个商户号下唯一。
	 * @param string $transaction_id 微信生成的订单号，在支付通知中有返回
	 * @param string $refund_no 商户系统内部的退款单号，商户系统内部唯一，只能是数字、大小写字母_-|*@ ，同一退款单号多次请求只退一笔。
	 * @param float $refund_fee 退款总金额，单位为元
	 * @param float $total_fee 订单总金额，单位为元
	 * @param string $refund_desc 若商户传入，会在下发给用户的退款消息中体现退款原因
	 * @return array 若退款成功，则返回结果包含微信退款单号refund_id
	 */
	public function refund($app_id, $out_trade_no, $transaction_id, $refund_no, $refund_fee, $total_fee, $refund_desc = '') {
		$wx_data = new WxData();
		$wx_data->setMchKey($this->_mch_key);
		$wx_data->setValue('mch_id', $this->_mch_id);
		$wx_data->setValue('appid', $app_id);
		$wx_data->setValue('out_trade_no', $out_trade_no);
		$wx_data->setValue('transaction_id', $transaction_id);
		$wx_data->setValue('out_refund_no', $refund_no);
		$wx_data->setValue('refund_fee', $refund_fee * 100);
		$wx_data->setValue('total_fee', $total_fee * 100);
		$wx_data->setValue('refund_desc', $refund_desc);
		$wx_data->setValue('nonce_str', self::getNonceStr(32));
		$wx_data->setSign();
		$xml = $wx_data->toXML();

		$ret = $this->postSSLCurl('https://api.mch.weixin.qq.com/secapi/pay/refund', $xml);
		$t = WxData::initFromXML($ret);
		if ($t['return_code'] == 'SUCCESS' && $t['result_code'] == 'SUCCESS') {
			return array(
				'success' => true,
				'msg' => $t['return_msg'],
				'refund_id' => $t['refund_id'] //微信退款单号
			);
		} else {
			return array(
				'success' => false,
				'msg' => $t['return_msg'],
				'err_msg' => $t['err_code_des'],
				'err_code' => $t['err_code']
			);
		}
	}

	/**
	 * 查询订单
	 * @param string $app_id 微信分配的公众号/小程序ID
	 * @param string $transaction_id 微信的订单号，优先使用
	 * @param string $out_trade_no 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|*@ ，且在同一个商户号下唯一。
	 * @return array 若查询成功，返回结果中包含交易状态 trade_state
	 */
	public function orderQuery($app_id, $transaction_id, $out_trade_no) {
		$wx_data = new WxData();
		$wx_data->setMchKey($this->_mch_key);
		$wx_data->setValue('appid', $app_id);
		$wx_data->setValue('mch_id', $this->_mch_id);
		$wx_data->setValue('transaction_id', $transaction_id);
		$wx_data->setValue('out_trade_no', $out_trade_no);
		$wx_data->setValue('nonce_str', self::getNonceStr(32));
		$wx_data->setSign();
		$xml = $wx_data->toXML();

		$ret = $this->postSSLCurl('https://api.mch.weixin.qq.com/pay/orderquery', $xml);
		$t = WxData::initFromXML($ret);
		if ($t['return_code'] == 'SUCCESS' && $t['result_code'] == 'SUCCESS') {
			return array(
				'success' => true,
				'msg' => $t['return_msg'],
				'trade_state' => $t['trade_state'] //交易状态
			);
		} else {
			return array(
				'success' => false,
				'msg' => $t['return_msg'],
				'err_msg' => $t['err_code_des'],
				'err_code' => $t['err_code']
			);
		}
	}

}
