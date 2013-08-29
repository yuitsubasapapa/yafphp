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
	 * render
	 * 
	 * @param string $action
	 * @param array $tpl_vars
	 * @return mixed
	 */
	public function render($action, $tpl_vars = null)
	{
		if ($action && is_string($action)) {
			$tpl_file = str_replace('_', '/', strtolower($this->_name) . '/' . $action) . '.' . YAF_G('view_ext');
			if (is_array($tpl_vars)) {
				$content = call_user_func(array($this->_view, 'render'), $tpl_file, $tpl_vars);
			} else {
				$content = call_user_func(array($this->_view, 'render'), $tpl_file);
			}

			if ($content === false) {
				return null;
			}

			return $content;
		}
		
		return null;
	}

	/**
	 * display
	 * 
	 * @param string $action
	 * @param array $tpl_vars
	 * @return boolean | string
	 */
	public function display($action, $tpl_vars = null)
	{
		if ($action && is_string($action)) {
			$tpl_file = str_replace('_', '/', strtolower($this->_name) . '/' . $action) . '.' . YAF_G('view_ext');
			if (is_array($tpl_vars)) {
				$content = call_user_func(array($this->_view, 'render'), $tpl_file, $tpl_vars);
			} else {
				$content = call_user_func(array($this->_view, 'render'), $tpl_file);
			}

			if ($content === false) {
				return false;
			}

			return $content;
		}
		
		return false;
	}

	/**
	 * getRequest
	 * 
	 * @param void
	 * @return Yaf_Request_Abstract
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * getResponse
	 * 
	 * @param void
	 * @return Yaf_Response_Abstract
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * getModuleName
	 * 
	 * @param void
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->_module;
	}

	/**
	 * getView
	 * 
	 * @param void
	 * @return Yaf_View_Interface
	 */
	public function getView()
	{
		return $this->_view;
	}

	/**
	 * initView
	 * 
	 * @param array $options
	 * @return Yaf_Controller_Abstract
	 */
	public function initView($options = null)
	{
		return $this;
	}

	/**
	 * setViewPath
	 * 
	 * @param string $view_directory
	 * @return boolean
	 */
	public function setViewPath($view_directory)
	{
		if (!is_string($view_directory)) {
			return false;
		}

		try {
			$this->_view->setScriptPath($view_directory);
			return true;
		} catch (Exception $e) {
			return false;
		}

		return false;
	}

	/**
	 * getViewPath
	 * 
	 * @param void
	 * @return string
	 */
	public function getViewPath()
	{
		try {
			$tpl_dir = $this->_view->getScriptPath();
			if (!is_string($tpl_dir) && YAF_G('view_directory')) {
				return YAF_G('view_directory');
			}
			return $tpl_dir;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * forward
	 * 
	 * @param mixed $module
	 * @param mixed $controller
	 * @param mixed $action
	 * @param mixed $invoke_args
	 * @return boolean
	 */
	public function forward($module, $controller = null, $action = null, $invoke_args = null)
	{
		if (!is_object($this->_request)
				|| !($this->_request instanceof Yaf_Request_Abstract)) {
			return false;
		}

		if (is_null($this->_invoke_args)) {
			$this->_invoke_args = array();
		}

		$num_args = func_num_args();

		switch ($num_args) {
			case 1:
				if ($module && is_string($module)) {
					$this->_request->getActionName($module);
				} else {
					trigger_error('Expect a string action name', E_USER_WARNING);
					return false;
				}
				break;
			case 2:
				if (is_string($controller)) {
					$this->_request->getControllerName($module);
					$this->_request->getActionName($controller);
				} elseif (is_array($controller)) {
					$parameters = array_merge($this->_invoke_args, $controller);
					$this->_request->getActionName($module);
					$this->_request->setParams($parameters);
				} else {
					return false;
				}
				break;
			case 3:
				if (is_string($action)) {
					$this->_request->setModuleName($module);
					$this->_request->getControllerName($controller);
					$this->_request->getActionName($action);
				} elseif (is_array($action)) {
					$parameters = array_merge($this->_invoke_args, $action);
					$this->_request->getControllerName($module);
					$this->_request->getActionName($controller);
					$this->_request->setParams($parameters);
				} else {
					return false;
				}
				break;
			case 4:
				if (!is_array($invoke_args)) {
					trigger_error('Parameters must be an array', E_USER_WARNING);
					return false;
				}
				$parameters = array_merge($this->_invoke_args, $invoke_args);
				$this->_request->setModuleName($module);
				$this->_request->getControllerName($controller);
				$this->_request->getActionName($action);
				$this->_request->setParams($parameters);
				break;
		}

		$this->_request->setDispatched();

		return true;
	}

	/**
	 * redirect
	 * 
	 * @param string $url
	 * @return boolean
	 */
	public function redirect($url)
	{
		if ($url && is_string($url)) {
			$this->_response->setRedirect($url);
			return true;
		}
		return false;
	}

	/**
	 * getInvokeArgs
	 * 
	 * @param void
	 * @return array
	 */
	public function getInvokeArgs()
	{
		return $this->_invoke_args;
	}

	/**
	 * getInvokeArg
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function getInvokeArg($name)
	{
		if ($name && is_string($name)
				&& is_array($this->_invoke_args)
				&& isset($this->_invoke_args[$name])) {
			return $this->_invoke_args[$name];
		}
		return null;
	}

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

			$this->_request = $request;
			$this->_response = $response;
			$this->_module = $request->getModuleName();
			$this->_view = $view;

			$class = get_class($this);
			$class_len = strlen(YAF_NAME_SEPARATOR) + 10;
			if (YAF_NAME_SUFFIX) {
				$this->_name = substr($class, 0, - $class_len);
			} else {
				$this->_name = substr($class, $class_len);
			}

			if (!($this instanceof Yaf_Action_Abstract)
					&& method_exists($this, 'init')) {
				call_user_func(array($this, 'init'));
			}
			return;
		}

		return false;
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
