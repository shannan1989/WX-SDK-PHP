<?php

/**
 * 数据对象基础类，该类中定义数据类最基本的行为，包括：
 * 计算/设置/获取签名、
 * 将数据输出为xml格式、从xml中读取数据等
 * @author 山南
 */
class WxData {

	protected $_values = [];
	protected $_mch_key;

	/**
	 * 设置商户KEY，用于生成签名
	 * @param string $key
	 */
	public function setMchKey($key) {
		$this->_mch_key = $key;
	}

	/**
	 * 生成签名
	 * @return string 签名，本函数不覆盖sign成员变量，如要设置签名需要调用setSign方法赋值
	 */
	public function makeSign() {
		//签名步骤一：按字典序排序参数
		ksort($this->_values);
		$s = '';
		foreach ($this->_values as $k => $v) {
			if ($k != 'sign' && $v != '' && !is_array($v)) {
				$s .= $k . '=' . $v . '&';
			}
		}
		//签名步骤二：在string后加入KEY
		$s = trim($s, '&') . '&key=' . $this->_mch_key;
		//签名步骤三：MD5加密 签名步骤四：所有字符转为大写
		$sign = strtoupper(md5($s));
		return $sign;
	}

	/**
	 * 获取签名，详见签名生成算法
	 * @return string
	 */
	public function getSign() {
		return $this->_values['sign'];
	}

	/**
	 * 设置签名，详见签名生成算法
	 * @return string
	 */
	public function setSign() {
		$sign = $this->makeSign();
		$this->_values['sign'] = $sign;
		return $sign;
	}

	/**
	 * 判断签名是否存在
	 * @return bool
	 */
	public function isSignSet() {
		return array_key_exists('sign', $this->_values);
	}

	/**
	 * 设置参数
	 * @param string $key
	 * @param string $value
	 */
	public function setValue($key, $value) {
		$this->_values[$key] = $value;
	}

	/**
	 * 判断是否设置相应的参数
	 * @param string $key
	 * @return bool
	 */
	public function isValueSet($key) {
		return array_key_exists($key, $this->_values);
	}

	/**
	 * 将数据输出为xml格式
	 * @return string
	 * @throws Exception
	 */
	public function toXML() {
		if (!is_array($this->_values) || count($this->_values) <= 0) {
			throw new Exception('数组数据异常');
		}
		$xml = '<xml>';
		foreach ($this->_values as $key => $val) {
			if (is_numeric($val)) {
				$xml .= '<' . $key . '>' . $val . '</' . $key . '>';
			} else {
				$xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
			}
		}
		$xml .= '</xml>';
		return $xml;
	}

	/**
	 * 将XML转为array
	 * @param string $xml
	 * @return array
	 * @throws Exception
	 */
	public function fromXML($xml) {
		if (!$xml) {
			throw new Exception('XML数据异常');
		}
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$this->_values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $this->_values;
	}

	public static function initFromXML($xml) {
		$obj = new self();
		$obj->fromXML($xml);
		return $obj->_values;
	}

}
