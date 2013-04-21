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
	 * @param array $config
	 * @param boolean $readonly
	 */
	public function __construct($config, $readonly = null)
	{
		if (is_array($config)) {
			$this->_config = $config;
			if (!is_null($readonly)) {
				$this->_readonly = (boolean) $readonly;
			}
		} else {
			throw new Yaf_Exception_TypeError('Invalid parameters provided, must be an array');
			return false;
		}
	}

	/**
	 * __isset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		return isset($this->_config[$name]);
	}

	/**
	 * get
	 *
	 * @param string $name
	 * @return mixed
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
	 * set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function set($name, $value)
	{
		if ($this->_readonly) return false;

		if (is_string($name)) {
			$this->_config[$name] = $value;
			return true;
		}

		trigger_error('Expect a string key name', E_USER_WARNING);
		return false;
	}

	/**
	 * Countable::count
	 *
	 * @param void
	 * @return integer
	 */
	public function count()
	{
		return count($this->_config);
	}

	/**
	 * Iterator::rewind
	 *
	 * @param void
	 * @return void
	 */
	public function rewind()
	{
		reset($this->_config);
	}

	/**
	 * Iterator::current
	 *
	 * @param void
	 * @return mixed
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

	/**
	 * Iterator::next
	 *
	 * @param void
	 * @return void
	 */
	public function next()
	{
		next($this->_config);
	}

	/**
	 * Iterator::valid
	 *
	 * @param void
	 * @return boolean
	 */
	public function valid()
	{
		return (current($this->_config) !== false);
	}

	/**
	 * Iterator::key
	 *
	 * @param void
	 * @return string
	 */
	public function key()
	{
 		return key($this->_config);
	}

	/**
	 * toArray
	 *
	 * @param void
	 * @return array
	 */
	public function toArray()
	{
		return $this->_config;
	}

	/**
	 * readOnly
	 *
	 * @param void
	 * @return boolean
	 */
	public function readOnly()
	{
		return $this->_readonly;
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetUnset($name)
	{
		if ($this->_readonly) return false;
		if (is_string($name)) {
			unset($this->_config[$name]);
			return true;
		}

		trigger_error('Expect a string key name', E_USER_WARNING);
		return false;
	}

	/**
	 * ArrayAccess:: offsetGet
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * ArrayAccess::offsetExists
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetExists($name)
	{
		return $this->__isset($name);
	}
	
	/**
	 * ArrayAccess:: offsetSet
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function offsetSet($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * __get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * __set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

}
