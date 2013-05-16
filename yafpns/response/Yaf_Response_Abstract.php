<?php
// +----------------------------------------------------------------------
// | yafphp [ Yaf PHP Framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://yafphp.duapp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: baoqiang <zmrnet@qq.com>
// +----------------------------------------------------------------------

namespace Yaf;

abstract class Response_Abstract
{
	const DEFAULT_BODY = 'content';

	protected $_header = array();
	protected $_body = array();
	protected $_sendheader = false;
	
	/**
	 * __construct
	 *
	 * @param void
	 */
	public function __construct()
	{

	}

	/**
	 * __destruct
	 *
	 * @param void
	 */
	public function __destruct()
	{

	}

	/**
	 * __clone
	 *
	 * @param void
	 */
	public function __clone()
	{

	}

	/**
	 * __toString
	 *
	 * @param void
	 * @return string
	 */
	public function __toString()
	{
		return implode('', $this->_body);
	}

	/**
	 * setBody
	 *
	 * @param string $body
	 * @param string $name
	 * @return boolean | Yaf\Response_Abstract
	 */
	public function setBody($body, $name = null)
	{
		if ($this->_alter_body($name, $body, 0)) {
			return $this;
		}
		return false;
	}

	/**
	 * appendBody
	 *
	 * @param string $body
	 * @param string $name
	 * @return boolean | Yaf\Response_Abstract
	 */
	public function appendBody($body, $name = null)
	{
		if ($this->_alter_body($name, $body, 2)) {
			return $this;
		}
		return false;
	}

	/**
	 * prependBody
	 *
	 * @param string $body
	 * @param string $name
	 * @return boolean | Yaf\Response_Abstract
	 */
	public function prependBody($body, $name = null)
	{
		if ($this->_alter_body($name, $body, 1)) {
			return $this;
		}
		return false;
	}

	/**
	 * clearBody
	 *
	 * @param string $name
	 * @return Yaf\Response_Abstract
	 */
	public function clearBody($name = null)
	{
		if ($name) {
			unset($this->_body[$name]);
		} else {
			$this->_body = array();
		}
	}

	/**
	 * getBody
	 *
	 * @param string $name
	 * @return string
	 */
	public function getBody($name = null)
	{
		if (func_num_args() == 0) {
			return $this->_body[self::DEFAULT_BODY];
		} elseif (is_null($name)) {
			return $this->_body;
		} elseif (is_string($name)
					&& isset($this->_body[$name])) {
			return $this->_body[$name];
		}
		return '';
	}

	/**
	 * setHeader
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param boolean $replace
	 * @return boolean
	 */
	public function setHeader($name, $value, $replace = false)
	{
		return false;
	}

	/**
	 * setAllHeaders
	 *
	 * @param array $header
	 * @return boolean
	 */
	public function setAllHeaders($header)
	{
		return false;
	}

	/**
	 * getHeader
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getHeader($name = null)
	{
		return null;
	}

	/**
	 * clearHeaders
	 *
	 * @param void
	 * @return boolean
	 */
	public function clearHeaders()
	{
		return false;
	}

	/**
	 * setRedirect
	 *
	 * @param string $url
	 * @return boolean
	 */
	public function setRedirect($url)
	{
		if (empty($url)) {
			return false;
		}

		if (header('Location:' . $url)) {
			return true;
		}

		return false;
	}

	/**
	 * response
	 *
	 * @param void
	 * @return boolean
	 */
	public function response()
	{
		foreach ($this->_body as $value) {
			echo $value;
		}

		return true;
	}

	/**
	 * yaf_response_alter_body
	 *
	 * @param string $name
	 * @param string $body
	 * @param integer $flag
	 * @return boolean
	 */
	private function _alter_body($name, $body, $flag)
	{
		if (empty($body)) {
			return true;
		}

		if (empty($name)) {
			$name = self::DEFAULT_BODY;
		}

		if (!isset($this->_body[$name])) {
			$this->_body[$name] = '';
		}

		$obody = $this->_body[$name];

		switch ($flag) {
			case 1:
				$this->_body[$name] = $body . $obody;
				break;
			case 2:
				$this->_body[$name] = $obody . $body;
				break;
			case 0:
			default:
				$this->_body[$name] = $body;
				break;
		}

		return true;
	}

}
