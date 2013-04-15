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

final class Yaf_Dispatcher
{
	protected static $_instance;

	protected $_router;
	protected $_view;
	protected $_request;
	protected $_plugins;
	protected $_render ;
	protected $_return_response = FALSE ;
	protected $_instantly_flush = FALSE ;
	protected $_default_module;
	protected $_default_controller;
	protected $_default_action;
	
	/**
	 * __construct
	 *
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * getInstance
	 *
	 */
	public static function getInstance()
	{
		if(self::$_instance instanceof self)
			return self::$_instance;
		else
			return self::$_instance = new self();
	}
	
	/**
	 * disableView
	 *
	 */
	public function disableView()
	{

	}

	/**
	 * enableView
	 *
	 */
	public function enableView()
	{
		return $this;
	}

	/**
	 * autoRender
	 *
	 */
	public function autoRender($flag)
	{
		return $this;
	}

	/**
	 * returnResponse
	 *
	 */
	public function returnResponse($flag)
	{
		return $this;
	}

	/**
	 * flushInstantly
	 *
	 */
	public function flushInstantly($flag)
	{
		return $this;
	}

	/**
	 * setErrorHandler
	 *
	 */
	public function setErrorHandler($callback, $error_type = NULL)
	{
		if(is_null($error_type)) $error_type = E_ALL | E_STRICT;
		return $this;
	}

	/**
	 * getApplication
	 *
	 */
	public function getApplication()
	{
		return $this;
	}

	/**
	 * getRequest
	 *
	 */

	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * getRouter
	 *
	 */
	public function getRouter()
	{
		return $this;
	}

	/**
	 * registerPlugin
	 *
	 */
	public function registerPlugin($plugin)
	{
		return $this;
	}

	/**
	 * setAppDirectory
	 *
	 */
	public function setAppDirectory($directory)
	{
		return $this;
	}

	/**
	 * setRequest
	 *
	 */
	public function setRequest($request)
	{
		if (!is_object($request) || !($request instanceof Yaf_Request_Abstract)) {
			throw new Exception('Expects a %s instance', E_WARNING);
			return;
		}
		
		$this->_request = $request;
	}

	/**
	 * initView
	 *
	 */
	public function initView()
	{
		return $this;
	}

	/**
	 * setView
	 *
	 */
	public function setView($view)
	{
		return $this;
	}

	/**
	 * setDefaultModule
	 *
	 */
	public function setDefaultModule($default_module_name)
	{
		return $this;
	}

	/**
	 * setDefaultController
	 *
	 */
	public function setDefaultController($default_controller_name)
	{
		return $this;
	}

	/**
	 * setDefaultAction
	 *
	 */
	public function setDefaultAction($default_action_name)
	{
		return $this;
	}

	/**
	 * throwException
	 *
	 */
	public function throwException($switch = FALSE)
	{
		return $this;
	}

	/**
	 * catchException
	 *
	 */
	public function catchException($switch = FALSE)
	{
		return $this;
	}

	/**
	 * dispatch
	 *
	 */
	public function dispatch($request)
	{
		return $this;
	}
}
