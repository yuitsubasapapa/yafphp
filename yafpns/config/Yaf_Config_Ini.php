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

final class Config_Ini extends Config_Abstract
{
	/**
	 * __construct
	 *
	 * @param mixed $config
	 * @param string $section
	 */
	public function __construct($config, $section = null)
	{
		if (is_array($config)) {
			$this->_config = $config;
		} elseif (is_string($config)) {
			if (file_exists($config)) {
				if (is_file($config)) {
					$this->_config = self::_parser_cb($config, $section);
					if ($this->_config == false || !is_array($this->_config)) {
						trigger_error('Parsing ini file '. $config .' failed', E_USER_ERROR);
						return false;
					}
				} else {
					trigger_error('Argument is not a valid ini file '. $config, E_USER_ERROR);
					return false;
				}
			} else {
				trigger_error('Unable to find config file '. $config, E_USER_ERROR);
				return false;
			}
		} else {
			trigger_error('Invalid parameters provided, must be path of ini file', E_USER_ERROR);
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
		
		if ($seg = strtok($name, '.')) {
			$value = $this->_config;
			while ($seg) {
				if (!isset($value[$seg])) return;
				$value = $value[$seg];
				$seg = strtok('.');
			}
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
		return true;
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetUnset($name)
	{
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

	/**
	 * yaf_config_ini_parser_cb
	 *
	 * @param string $filepath
	 * @param string $section
	 * @return array | boolean
	 */
	private static function _parser_cb($filepath, $section){
		$config = parse_ini_file($filepath, true);
		if ($config && is_array($config)) {
			foreach ($config as $key => $value) {
				if($seg = ltrim(strchr($key, ':'), ': ')){
					while ($token = ltrim(strrchr($seg, ':'), ': ')) {
						if (isset($config[$token])) {
							$value = array_merge($config[$token], $value);
						}
						$seg = substr($seg, 0, -strlen($token));
						$seg = rtrim($seg, ': ');
					}

					$token = rtrim($seg, ': ');
					if (isset($config[$token])) {
						$value = array_merge($config[$token], $value);
					}

					unset($config[$key]);

					if ($key = trim(strtok($key, ':'))) {
						$config[$key] = $value;
					}
				}

				if (is_string($section) && ($key == $section)) {
					return self::_simple_parser_cb($value);
				}
			}

			return self::_simple_parser_cb($config);
		}

		return false;
	}

	/**
	 * yaf_config_ini_simple_parser_cb
	 *
	 * @param array $simple
	 * @return array
	 */
	private static function _simple_parser_cb($simple){
		if(!is_array($simple)) return;
		
		foreach ($simple as $key => $value) {
			if ($seg = strtok($key, '.')) {
				if ($subkey = ltrim(strchr($key, '.'), '.')) {
					$value = array($subkey => $value);
					if (isset($simple[$seg]) && is_array($simple[$seg])) {
						$value = array_merge($simple[$seg], $value);
					}
					$simple[$seg] = self::_simple_parser_cb($value);
					unset($simple[$key]);
				} elseif(is_array($value)) {
					$simple[$key] = self::_simple_parser_cb($value);
				}
			}
		}

		return $simple;
	}

}
