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

abstract class Yaf_Config_Abstract implements Iterator, ArrayAccess, Countable
{
	// array _config
	protected $_config = array();
	// boolean _readonly
	protected $_readonly = true;

	/**
	 * __construct
	 *
	 */
	public function __construct($config, $section = YAF_ENVIRON)
	{
		if (is_string($config)) {
			return new Yaf_Config_Ini($config, $section);
		}

		if (is_array($config)) {
			return new Yaf_Config_Simple($config, true);
		}

		throw new Yaf_Exception_TypeError('Expects a string or an array as parameter');
		return;
	}



	/**
	 * __get
	 *
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * __set
	 *
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * __isset
	 *
	 */
	public function __isset($name)
	{
		return isset($this->_config[$name]);
	}

	/**
	 * get
	 *
	 */
	public function get($name = null)
	{
		if (is_null($name)) return $this;
		if (isset($this->_config[$name])) return $this->_config[$name];
	}

	/**
	 * set
	 *
	 */
	public function set($name, $value)
	{
		if ($this->_readonly) return;
		if (is_string($name)) $this->_config[$name] = $value;
		throw new Exception('Expect a string key name', E_WARNING);
	}

	/**
	 * Countable::count
	 *
	 */
	public function count()
	{
		return count($this->_config);
	}

	/**
	 * ArrayAccess::offsetExists
	 *
	 */
	public function offsetExists($name)
	{
		return $this->__isset($name);
	}

	/**
	 * ArrayAccess:: offsetGet
	 *
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * ArrayAccess:: offsetSet
	 *
	 */
	public function offsetSet($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 */
	public function offsetUnset($name)
	{
		if ($this->_readonly) return;
		if (is_string($name)) unset($this->_config[$name]);
		throw new Exception('Expect a string key name', E_WARNING);
	}

	/**
	 * Iterator::current
	 *
	 */
	public function current()
	{
		return current($this->_config);
	}

	/**
	 * Iterator::key
	 *
	 */
	public function key()
	{
 		return key($this->_config);
	}

	/**
	 * Iterator::next
	 *
	 */
	public function next()
	{
		next($this->_config);
	}

	/**
	 * Iterator::rewind
	 *
	 */
	public function rewind()
	{
		reset($this->_config);
	}

	/**
	 * Iterator::valid
	 *
	 */
	public function valid()
	{
		return (current($this->_config) !== false);
	}

	/**
	 * toArray
	 *
	 */
	public function toArray()
	{
		return $this->_config;
	}

	/**
	 * readOnly
	 *
	 */
	public function readOnly()
	{
		return $this->_readonly;
	}

}
