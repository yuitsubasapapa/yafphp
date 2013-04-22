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

final class Yaf_Registry
{
	protected static $_instance;

	protected $_entries = array();

	/**
	 * __construct
	 * 
	 * @param void
	 */
	private function __construct()
	{

	}

	/**
	 * __clone
	 * 
	 * @param void
	 */
	private function __clone()
	{

	}

	/**
	 * get
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public static function get($name)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries) && isset($registry->_entries[$name])) {
				return $registry->_entries[$name];
			}
		}
		
		return null;
	}

	/**
	 * has
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function has($name)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries)) {
				return isset($registry->_entries[$name]);
			}
		}

		return false;
	}

	/**
	 * set
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public static function set($name, $value)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries)) {
				$registry->_entries[$name] = $value;
				return true;
			}
		}

		return false;
	}

	/**
	 * del
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function del($name)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries)) {
				unset($registry->_entries[$name]);
				return true;
			}
		}

		return false;
	}

	/**
	 * yaf_registry_instance
	 * 
	 * @param void
	 */
	private static function _instance()
	{
		if (self::$_instance instanceof self) {
			return self::$_instance;
		}

		return self::$_instance = new self();
	}
}
