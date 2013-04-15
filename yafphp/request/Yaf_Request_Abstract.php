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

abstract class Yaf_Request_Abstract
{
	protected $_method;
	protected $_module;
	protected $_controller;
	protected $_action;
	protected $_params = array();
	protected $_language;
	protected $_base_uri;
	protected $_request_uri;
	protected $_dispatched = false;
	protected $_routed = false;

	private $_exception;
	
	/**
	 * getModuleName
	 *
	 */
	public function getModuleName()
	{
		return $this->_module;
	}
	
	/**
	 * getControllerName
	 *
	 */
	public function getControllerName()
	{
		return $this->_controller;
	}
	
	/**
	 * getActionName
	 *
	 */
	public function getActionName()
	{
		return $this->_action;
	}
	
	/**
	 * setModuleName
	 *
	 */
	public function setModuleName($name)
	{
		if (!is_string($name)) {
			throw new Yaf_Exception('Expect a string module name', E_WARNING);
			return false;
		}
		$this->_module = $name;
		return $this;
	}
	
	/**
	 * setControllerName
	 *
	 */
	public function setControllerName($name)
	{
		if (!is_string($name)) {
			throw new Yaf_Exception('Expect a string controller name', E_WARNING);
			return false;
		}
		$this->_controller = $name;
		return $this;
	}
	
	/**
	 * setActionName
	 *
	 */
	public function setActionName($name)
	{
		if (!is_string($name)) {
			throw new Yaf_Exception('Expect a string action name', E_WARNING);
			return false;
		}

		$this->_action = $name;

		return $this;
	}
	
	/**
	 * getException
	 *
	 */
	public function getException()
	{
		if (is_object($this->_exception)
				&& ($this->_exception instanceof Exception)) {
			return $this->_exception;
		}

		return null;
	}
	
	/**
	 * getParams
	 *
	 */
	public function getParams()
	{
		return $this->_params;
	}
	
	/**
	 * getParam
	 *
	 */
	public function getParam($name, $dafault = null)
	{
		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		}

		if (!is_null($dafault)) {
			return $dafault;
		}

		return null;
	}
	
	/**
	 * setParam
	 *
	 */
	public function setParam($name, $value = null)
	{
		if (is_null($value)) {
			if (is_array($name)) {
				$this->_params = array_merge($this->_params, $name);
				return $this;
			}
		} elseif(is_string($name)) {
			$this->_params[$name] = $value;
			return $this;
		}

		return false;
	}
	
	/**
	 * getMethod
	 *
	 */
	public function getMethod()
	{
		return $this->_method;
	}
	
	/**
	 * isDispatched
	 *
	 */
	public function isDispatched()
	{
		return $this->_dispatched;
	}
	
	/**
	 * setDispatched
	 *
	 */
	public function setDispatched($flag = true)
	{
		if (is_bool($flag) && $flag == false) {
			$this->_dispatched = false;
		} else {
			$this->_dispatched = true;
		}

		return true;
	}
	
	/**
	 * isRouted
	 *
	 */
	public function isRouted()
	{
		return $this->_routed;
	}
	
	/**
	 * setRouted
	 *
	 */
	public function setRouted($flag = true)
	{
		if (is_bool($flag) && $flag == false) {
			$this->_routed = false;
		} else {
			$this->_routed = true;
		}

		return $this;
	}

	
	/**
	 * getLanguage
	 *
	 */
	abstract public function getLanguage();
	
	/**
	 * getQuery
	 *
	 */
	abstract public function getQuery($name = null);
	
	/**
	 * getPost
	 *
	 */
	abstract public function getPost($name = null);
	
	/**
	 * getEnv
	 *
	 */
	abstract public function getEnv($name = null);
	
	/**
	 * getServer
	 *
	 */
	abstract public function getServer($name = null);
	
	/**
	 * getCookie
	 *
	 */
	abstract public function getCookie($name = null);
	
	/**
	 * getFiles
	 *
	 */
	abstract public function getFiles($name = null);
	
	/**
	 * isGet
	 *
	 */
	abstract public function isGet();
	
	/**
	 * isPost
	 *
	 */
	abstract public function isPost();
	
	/**
	 * isHead
	 *
	 */
	abstract public function isHead();
	
	/**
	 * isXmlHttpRequest
	 *
	 */
	abstract public function isXmlHttpRequest();
	
	/**
	 * isPut
	 *
	 */
	abstract public function isPut();
	
	/**
	 * isDelete
	 *
	 */
	abstract public function isDelete();
	
	/**
	 * isOptions
	 *
	 */
	abstract public function isOptions();
	
	/**
	 * isCli
	 *
	 */
	abstract public function isCli();

}
