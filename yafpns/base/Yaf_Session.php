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

use \Iterator as Iterator;
use \ArrayAccess as ArrayAccess;
use \Countable as Countable;

final class Session implements Iterator, ArrayAccess, Countable
{
	protected static $_instance;

	protected $_session;
	protected $_started = false;

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
	 * __sleep
	 * 
	 * @param void
	 */
	private function __sleep()
	{

	}

	/**
	 * __wakeup
	 * 
	 * @param void
	 */
	private function __wakeup()
	{

	}

	/**
	 * getInstance
	 * 
	 * @param void
	 * @return Yaf_Session
	 */
	public static function getInstance()
	{
		if (self::$_instance instanceof self) {
			return self::$_instance;
		}

		self::$_instance = new self();
		self::$_instance->start();
		if (!isset($_SESSION) || !is_array($_SESSION)) {
			trigger_error('Attempt to start session failed', E_USER_WARNING);
			unset(self::$_instance);
			return null;
		}
		self::$_instance->_session = &$_SESSION;
		return self::$_instance;
	}

	/**
	 * start
	 * 
	 * @param void
	 * @return Yaf_Session
	 */
	public function start()
	{
		if (!$this->_started) {
			session_start();
			$this->_started = true;
		}
		return $this;
	}

	/**
	 * get
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function get($name = null)
	{
		if (is_null($name)) {
			return $this->_session;
		} elseif(isset($this->_session[$name])) {
			return $this->_session[$name];
		} else {
			return null;
		}
	}

	/**
	 * has
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function has($name)
	{
		return isset($this->_session[$name]);
	}

	/**
	 * set
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return boolean | Yaf_Session
	 */
	public function set($name, $value)
	{
		if ($name && is_string($name)) {
			$this->_session[$name] = $value;
			return $this;
		}
		return false;
	}

	/**
	 * del
	 * 
	 * @param string $name
	 * @return boolean | Yaf_Session
	 */
	public function del($name)
	{
		if ($name && is_string($name)) {
			unset($this->_session[$name]);
			return $this;
		}
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
		return count($this->_session);
	}

	/**
	 * Iterator::rewind
	 *
	 * @param void
	 * @return void
	 */
	public function rewind()
	{
		reset($this->_session);
	}

	/**
	 * Iterator::next
	 *
	 * @param void
	 * @return void
	 */
	public function next()
	{
		next($this->_session);
	}

	/**
	 * Iterator::current
	 *
	 * @param void
	 * @return mixed
	 */
	public function current()
	{
		return current($this->_session);
	}

	/**
	 * Iterator::key
	 *
	 * @param void
	 * @return string
	 */
	public function key()
	{
 		return key($this->_session);
	}

	/**
	 * Iterator::valid
	 *
	 * @param void
	 * @return boolean
	 */
	public function valid()
	{
		return (current($this->_session) !== false);
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
	 * ArrayAccess::offsetExists
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetExists($name)
	{
		return $this->has($name);
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetUnset($name)
	{
		return $this->del($name);
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
	 * __isset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		return $this->has($name);
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
	 * __unset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __unset($name)
	{
		return $this->del($name);
	}

}