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

abstract class Yaf_Controller_Abstract
{
	public $actions;

	protected $_module;
	protected $_name;
	protected $_request;
	protected $_response;
	protected $_invoke_args;
	protected $_view;

	//protected $_script_path;

	/**
	 * __construct
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @param Yaf_View_Interface $view
	 * @param array $invoke_args
	 */
	public function __construct($request, $response, $view, $invoke_args = null)
	{
		if (($request instanceof Yaf_Request_Abstract)
				&& ($response instanceof Yaf_Response_Abstract)
				&& ($view instanceof Yaf_View_Interface)) {

			if (is_array($invoke_args)) {
				$this->_invoke_args = $invoke_args;
			}

			$this->_name = get_class($this);
			$this->_request = $request;
			$this->_response = $response;
			$this->_module = $request->getModuleName();
			$this->_view = $view;

			if (!($this instanceof Yaf_Action_Abstract)
					&& method_exists($this, 'init')) {
				call_user_func(array($this, 'init'));
			}
			return;
		}

		return false;
	}

	public function init()
	{

	}

	public function getModuleName()
	{

	}

	public function getRequest()
	{

	}

	public function getResponse()
	{

	}

	public function getView()
	{

	}

	public function initView()
	{

	}

	public function setViewPath($view_directory)
	{

	}

	public function getViewPath()
	{

	}

	public function render($action_name, $tpl_vars = null)
	{

	}

	public function display($action_name, $tpl_vars = null)
	{

	}

	public function forward($action, $invoke_args = null)
	{

	}
/*
	public function forward ( $controller , $action , $invoke_args = NULL )
	{

	}

	public function forward ( $module , $controller , $action , $invoke_args = NULL )
	{

	}
*/
	public function redirect($url)
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

}