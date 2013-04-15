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

final class Yaf_Config_Simple extends Yaf_Config_Abstract
{

	/**
	 * __construct
	 *
	 */
	public function __construct($config, $readonly = null)
	{
		if (is_array($config)) {
			$this->_config = $config;
			if (!is_null($readonly)) {
				$this->_readonly = (bool)$readonly;
			}
		} else {
			throw new Yaf_Exception_TypeError('Invalid parameters provided, must be an array');
			return;
		}
	}

	/**
	 * get
	 *
	 */
	public function get($name = null)
	{
		if (is_null($name)) return $this;

		if (isset($this->_config[$name])) {
			$value = $this->_config[$name];
			if (is_array($value)) {
				return new self($value);
			} else {
				return $value;
			}
		}
	}

	/**
	 * Iterator::current
	 *
	 */
	public function current()
	{
		$value = current($this->_config);
		if (is_array($value)) {
			return new self($value);
		} else {
			return $value;
		}
	}

}
