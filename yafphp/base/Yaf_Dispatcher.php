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
	protected $_plugins = array();

	protected $_render = true;
	protected $_return_response = false;
	protected $_instantly_flush = false;

	protected $_default_module;
	protected $_default_controller;
	protected $_default_action;

	/**
	 * __construct
	 *
	 * @param void
	 */
	private function __construct()
	{
		$this->_router = new Yaf_Router();
		$this->_default_module = YAF_G('default_module');
		$this->_default_controller = YAF_G('default_controller');
		$this->_default_action = YAF_G('default_action');

		self::$_instance = $this;
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
	 * enableView
	 *
	 * @param void
	 * @return Yaf_Dispatcher
	 */
	public function enableView()
	{
		$this->_render = true;
		return $this;
	}
	
	/**
	 * disableView
	 *
	 * @param void
	 * @return Yaf_Dispatcher
	 */
	public function disableView()
	{
		$this->_render = false;
		return $this;

	}

	/**
	 * initView
	 *
	 * @return boolean | Yaf_View_Interface
	 */
	public function initView()
	{
		if ($this->_view && is_object($this->_view)
				&& ($this->_view instanceof Yaf_View_Interface)) {
			return $this->_view;
		}
		return false;
	}

	/**
	 * setView
	 *
	 * @param Yaf_View_Interface $view
	 * @return boolean | Yaf_Dispatcher
	 */
	public function setView($view)
	{
		if ($view && is_object($view)
				&& ($view instanceof Yaf_View_Interface)) {
			$this->_view = $view;
			return $this;
		}
		return false;
	}

	/**
	 * setRequest
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean | Yaf_Dispatcher
	 */
	public function setRequest($request)
	{
		if (is_object($request)
				&& ($request instanceof Yaf_Request_Abstract)) {
			$this->_request = $request;
			return $this;
		}
		trigger_error('Expects a Yaf_Request_Abstract instance', E_USER_WARNING);
		return false;
	}

	/**
	 * getApplication
	 *
	 * @param void
	 * @return Yaf_Application
	 */
	public function getApplication()
	{
		return Yaf_Application::app();
	}

	/**
	 * getRouter
	 *
	 * @param void
	 * @return Yaf_Router
	 */
	public function getRouter()
	{
		return $this->_router;
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
	 * setErrorHandler
	 *
	 * @param string $callback
	 * @param int $error_type
	 * @return boolean | Yaf_Dispatcher
	 */
	public function setErrorHandler($callback, $error_type = null)
	{
		if (is_null($error_type))
			$error_type = E_ALL | E_STRICT;

		if (set_error_handler($callback, $error_type)) {
			return $this;
		}
		return false;
	}

	/**
	 * setDefaultModule
	 *
	 * @param string $module_name
	 * @return boolean | Yaf_Dispatcher
	 */
	public function setDefaultModule($module_name)
	{
		if ($module_name && is_string($module_name)
				&& $this->_is_module_name($module_name)) {
			$this->_default_module = ucfirst(strtolower($module_name));
			return $this;
		}
		return false;
	}

	/**
	 * setDefaultController
	 *
	 * @param string $controller_name
	 * @return boolean | Yaf_Dispatcher
	 */
	public function setDefaultController($controller_name)
	{
		if ($controller_name && is_string($controller_name)) {
			$this->_default_controller = ucfirst(strtolower($controller_name));
			return $this;
		}
		return false;
	}

	/**
	 * setDefaultAction
	 *
	 * @param string $action_name
	 * @return boolean | Yaf_Dispatcher
	 */
	public function setDefaultAction($action_name)
	{
		if ($action_name && is_string($action_name)) {
			$this->_default_action = strtolower($action_name);
			return $this;
		}
		return false;
	}

	/**
	 * returnResponse
	 *
	 * @param void | boolean $flag
	 * @return boolean | Yaf_Dispatcher
	 */
	public function returnResponse($flag = false)
	{
		if (func_num_args()) {
			$this->_return_response = (boolean)$flag;
			return $this;
		} else {
			return (boolean)$this->_return_response;
		}
	}

	/**
	 * autoRender
	 *
	 * @param void | boolean $flag
	 * @return boolean | Yaf_Dispatcher
	 */
	public function autoRender($flag = false)
	{
		if (func_num_args()) {
			$this->_render = (boolean)$flag;
			return $this;
		} else {
			return (boolean)$this->_render;
		}
	}

	/**
	 * flushInstantly
	 *
	 * @param void | boolean $flag
	 * @return boolean | Yaf_Dispatcher
	 */
	public function flushInstantly($flag = false)
	{
		if (func_num_args()) {
			$this->_instantly_flush = (boolean)$flag;
			return $this;
		} else {
			return (boolean)$this->_instantly_flush;
		}
	}

	/**
	 * getInstance
	 *
	 * @param void
	 * @return Yaf_Dispatcher
	 */
	public static function getInstance()
	{
		if (self::$_instance instanceof self) {
			return self::$_instance;
		}

		return self::$_instance = new self();
	}

	/**
	 * dispatch
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean | string
	 */
	public function dispatch($request)
	{
		if ($request instanceof Yaf_Request_Abstract) {
			$this->_request = $request;

			if (strncasecmp(PHP_SAPI, 'cli', 3)) {
				$response = new Yaf_Response_Http();
			} else {
				$response = new Yaf_Response_Cli();
			}

			if (!$request || !is_object($request)) {
				throw new Yaf_Exception_TypeError('Expect a Yaf_Request_Abstract instance');
				unset($response);
				return false;
			}

			/* route request */
			if (!$request->isRouted()) {
				// routerStartup
				try {
					foreach ($this->_plugins as $plugin) {
						$methods = get_class_methods($plugin);
						if (in_array('routerStartup', $methods)) {
							call_user_func(array($plugin, 'routerStartup'), $request, $response);
						}
					}
				} catch (Exception $e) {
					if (YAF_G('catch_exception')) {
						$this->_exception_handler($request, $response, $e);
					}
				}

				if (!$this->_route($request)) {
					throw new Yaf_Exception_RouterFailed('Routing request failed');
					unset($response);
					return false;
				}

				$this->_fix_default($request);

				// routerShutdown
				try {
					foreach ($this->_plugins as $plugin) {
						$methods = get_class_methods($plugin);
						if (in_array('routerShutdown', $methods)) {
							call_user_func(array($plugin, 'routerShutdown'), $request, $response);
						}
					}
				} catch (Exception $e) {
					if (YAF_G('catch_exception')) {
						$this->_exception_handler($request, $response, $e);
						unset($response);
					}
				}

				$request->setRouted();
			} else {
				$this->_fix_default($request);
			}

			// dispatchLoopStartup
			try {
				foreach ($this->_plugins as $plugin) {
					$methods = get_class_methods($plugin);
					if (in_array('dispatchLoopStartup', $methods)) {
						call_user_func(array($plugin, 'dispatchLoopStartup'), $request, $response);
					}
				}
			} catch (Exception $e) {
				if (YAF_G('catch_exception')) {
					$this->_exception_handler($request, $response, $e);
					unset($response);
				}
			}

			$view = $this->initView();
			$nesting = YAF_FORWARD_LIMIT;
			do {
				// preDispatch
				try {
					foreach ($this->_plugins as $plugin) {
						$methods = get_class_methods($plugin);
						if (in_array('preDispatch', $methods)) {
							call_user_func(array($plugin, 'preDispatch'), $request, $response);
						}
					}
				} catch (Exception $e) {
					if (!$this->_handle($request, $response, $view)) {
						if (YAF_G('catch_exception')) {
							$this->_exception_handler($request, $response, $e);
							unset($response);
						}
						return false;
					}
				}

				$this->_fix_default($request);

				// postDispatch
				try {
					foreach ($this->_plugins as $plugin) {
						$methods = get_class_methods($plugin);
						if (in_array('postDispatch', $methods)) {
							call_user_func(array($plugin, 'postDispatch'), $request, $response);
						}
					}
				} catch (Exception $e) {
					if (YAF_G('catch_exception')) {
						$this->_exception_handler($request, $response, $e);
						unset($response);
					}
				}
			} while (--$nesting > 0 && !$request->isDispatched());

			// dispatchLoopShutdown
			try {
				foreach ($this->_plugins as $plugin) {
					$methods = get_class_methods($plugin);
					if (in_array('dispatchLoopShutdown', $methods)) {
						call_user_func(array($plugin, 'dispatchLoopShutdown'), $request, $response);
					}
				}
			} catch (Exception $e) {
				if (YAF_G('catch_exception')) {
					$this->_exception_handler($request, $response, $e);
					unset($response);
				}
			}

			if (0 == $nesting && !$request->isDispatched()) {
				try {
					throw new Yaf_Exception_DispatchFailed('The max dispatch nesting ' . YAF_FORWARD_LIMIT . ' was reached');
				} catch (Exception $e) {
					if (YAF_G('catch_exception')) {
						$this->_exception_handler($request, $response, $e);
					}
				}
				unset($response);
				return false;
			}

			if (!$this->_return_response) {
				$response->response();
				$response->clearBody();
			}

			return $response;
		}

		return false;
	}

	/**
	 * throwException
	 *
	 * @param void | boolean $flag
	 * @return boolean | Yaf_Dispatcher
	 */
	public function throwException($flag = false)
	{
		if (func_num_args()) {
			YAF_G('throw_exception', (boolean)$flag);
			return $this;
		} else {
			return YAF_G('throw_exception');
		}
	}

	/**
	 * catchException
	 *
	 * @param void | boolean $flag
	 * @return boolean | Yaf_Dispatcher
	 */
	public function catchException($flag = false)
	{
		if (func_num_args()) {
			YAF_G('catch_exception', (boolean)$flag);
			return $this;
		} else {
			return YAF_G('catch_exception');
		}
	}

	/**
	 * registerPlugin
	 *
	 * @param Yaf_Plugin_Abstract $plugin
	 * @return boolean | Yaf_Dispatcher
	 */
	public function registerPlugin($plugin)
	{
		if (is_object($plugin)
				&& ($plugin instanceof Yaf_Plugin_Abstract)) {
			$this->_plugins[] = $plugin;
			return $this;
		} 
		trigger_error('Expects a Yaf_Plugin_Abstract instance', E_USER_WARNING);
		return false;
	}

	/**
	 * yaf_dispatcher_exception_handler
	 *
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @param Exception $exception
	 */
	private function _exception_handler($request, $response, &$exception)
	{
		if (YAF_G('in_exception') || !$exception) {
			return;
		}

		YAF_G('in_exception', true);

		$module = $request->getModuleName();
		if (!$module || !is_string($module)) {
			$request->setModuleName($this->_default_module);
		}
		$request->setControllerName('Error');
		$request->setActionName('error');
		$request->setException($exception);
		$request->setParam('exception', $exception);
		$request->setDispatched(false);
		unset($exception);

		$view = $this->initView();
		try {
			$this->_handle($request, $response, $view);
		} catch (Exception $e) {
			if ($e && ($e instanceof Yaf_Exception_LoadFailed_Controller)) {
				/* failover to default module error catcher */
				$request->setModuleName($this->_default_module);
				$this->_handle($request, $response, $view);
				unset($e);
			}
		}

		$response->response();
	}

	/**
	 * yaf_dispatcher_route
	 *
	 * @param Yaf_Request_Abstract $request
	 */
	private function _route($request)
	{
		if (is_object($this->_router)) {
			if ($this->_router instanceof Yaf_Router) {
				/* use built-in router */
				$this->_router->route($request);
			} else {
				/* user custom router */
				if (!method_exists($this->_router, 'route')
						|| $this->_router->route($request) === false) {
					throw new Yaf_Exception_RouterFailed('Routing request failed');
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * yaf_dispatcher_fix_default
	 *
	 * @param Yaf_Request_Abstract $request
	 */
	private function _fix_default($request)
	{
		// module
		$module = $request->getModuleName();
		if ($module && is_string($module)) {
			$request->setModuleName(strtolower($module));
		} else {
			$request->setModuleName($this->_default_module);
		}

		// controller
		$controller = $request->getControllerName();
		if ($controller && is_string($controller)) {
			/**
			 * upper controller name
			 * eg: Index_sub -> Index_Sub
			 */
			$request->setControllerName(ucwords($controller));
		} else {
			$request->setModuleName($this->_default_controller);
		}

		// action
		$action = $request->getActionName();
		if ($action && is_string($action)) {
			$request->seActionName(strtolower($action));
		} else {
			$request->setModuleName($this->_default_action);
		}

	}
	
	/**
	 * yaf_dispatcher_handle
	 *
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @param Yaf_View_Interface $view
	 */
	private function _handle($request, $response, $view)
	{
		$app_dir = YAF_G('directory');

		$request->setDispatched(true);

		if (!$app_dir) {
			throw new Yaf_Exception_StartupError('Yaf_Dispatcher requires Yaf_Application(which set the application.directory) to be initialized first');
			return false;
		} else {
			$is_def_module = false;
			/* $is_def_ctr = false; */

			// module
			$module = $request->getModuleName();
			if (empty($module) || !is_string($module)) {
				throw new Yaf_Exception_DispatchFailed('Unexcepted a empty module name');
				return false;
			} elseif (!$this->_is_module_name($module)) {
				throw new Yaf_Exception_LoadFailed_Module('There is no module ' . $module);
				return false;
			}
			// controller
			$controller	= $request->getControllerName();
			if (empty($controller) || !is_string($controller)) {
				throw new Yaf_Exception_DispatchFailed('Unexcepted a empty controller name');
				return false;
			}

			if(strcasecmp($this->_default_module, $module) == 0) {
				$is_def_module = true;
			}

			/* if (strcasecmp($this->_default_controller), $controller) == 0) {
				$is_def_ctr = true;
			} */

			$ce = $this->_get_controller($app_dir, $module, $controller, $is_def_module);
			if (!$ce) {
				return false;
			} else {
/*
				zend_class_entry *view_ce = NULL;
				zval  *action, *render, *view_dir = NULL, *ret = NULL;
				char  *action_lower, *func_name;
				uint  func_name_len;

				yaf_controller_t *icontroller;

				MAKE_STD_ZVAL(icontroller);
				object_init_ex(icontroller, ce);

				/* cause controller's constructor is a final method, so it must be a internal function
				   do {
				   zend_function *constructor = NULL;
				   constructor = Z_OBJ_HT_P(exec_ctr)->get_constructor(exec_ctr TSRMLS_CC);
				   if (constructor != NULL) {
				   if (zend_call_method_with_2_params(&exec_ctr, *ce
				   , &constructor, NULL, &ret, request, response) == NULL) {
				   yaf_trigger_error(YAF_ERR_CALL_FAILED, "function call for %s::__construct failed", (*ce)->name);
				   return 0;
				   }
				   }
				   } while(0);
				   * /
				yaf_controller_construct(ce, icontroller, request, response, view, NULL TSRMLS_CC);
				if (EG(exception)) {
					zval_ptr_dtor(&icontroller);
					return 0;
				}
			

				if ((view_ce = Z_OBJCE_P(view)) == yaf_view_simple_ce) {
					view_dir = zend_read_property(view_ce, view, ZEND_STRL(YAF_VIEW_PROPERTY_NAME_TPLDIR), 1 TSRMLS_CC);
				} else {
					zend_call_method_with_0_params(&view, view_ce, NULL, "getscriptpath", &view_dir);
					if (EG(exception)) {
						if (view_dir) {
							zval_ptr_dtor(&view_dir);
						}
						zval_ptr_dtor(&icontroller);
						return 0;
					}
				}

				if (!view_dir || IS_STRING != Z_TYPE_P(view_dir) || !Z_STRLEN_P(view_dir)) {
					/* view directory might be set by _constructor * /
					MAKE_STD_ZVAL(view_dir);
					Z_TYPE_P(view_dir) = IS_STRING;

					if (is_def_module) {
						Z_STRLEN_P(view_dir) = spprintf(&(Z_STRVAL_P(view_dir)), 0, "%s/%s", app_dir ,"views");
					} else {
						Z_STRLEN_P(view_dir) = spprintf(&(Z_STRVAL_P(view_dir)), 0, "%s/%s/%s/%s", app_dir,
								"modules", Z_STRVAL_P(module), "views");
					}

					/** tell the view engine where to find templates * /
					if (view_ce == yaf_view_simple_ce) {
						zend_update_property(view_ce, view,  ZEND_STRL(YAF_VIEW_PROPERTY_NAME_TPLDIR), view_dir TSRMLS_CC);
					} else {
						zend_call_method_with_1_params(&view, view_ce, NULL, "setscriptpath", &ret, view_dir);
					}

					if (ret) {
						zval_ptr_dtor(&ret);
						ret = NULL;
					}

				    zval_ptr_dtor(&view_dir);

					if (EG(exception)) {
						zval_ptr_dtor(&icontroller);
						return 0;
					}
				} else if (view_ce != yaf_view_simple_ce) {
					zval_ptr_dtor(&view_dir);
				}

				zend_update_property(ce, icontroller, ZEND_STRL(YAF_CONTROLLER_PROPERTY_NAME_NAME),	controller TSRMLS_CC);

				action		 = zend_read_property(request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_ACTION), 1 TSRMLS_CC);
				action_lower = zend_str_tolower_dup(Z_STRVAL_P(action), Z_STRLEN_P(action));

				/* because the action might call the forward to override the old action * /
				Z_ADDREF_P(action);

				func_name_len = spprintf(&func_name,  0, "%s%s", action_lower, "action");
				efree(action_lower);

				if (zend_hash_find(&((ce)->function_table), func_name, func_name_len + 1, (void **)&fptr) == SUCCESS) {
					uint count = 0;
					zval ***call_args = NULL;

					ret = NULL;

					executor = icontroller;
					if (fptr->common.num_args) {
						zval *method_name;

						yaf_dispatcher_get_call_parmaters(request_ce, request, fptr, &call_args, &count TSRMLS_CC);
						MAKE_STD_ZVAL(method_name);
						ZVAL_STRINGL(method_name, func_name, func_name_len, 0);

						call_user_function_ex(&(ce)->function_table, &icontroller, method_name, &ret, count, call_args, 1, NULL TSRMLS_CC);

						efree(method_name);
						efree(call_args);
					} else {
						zend_call_method(&icontroller, ce, NULL, func_name, func_name_len, &ret, 0, NULL, NULL TSRMLS_CC);
					}

					efree(func_name);

					if (!ret) {
						zval_ptr_dtor(&action);
						zval_ptr_dtor(&icontroller);
						return 0;
					}

					if ((Z_TYPE_P(ret) == IS_BOOL
								&& !Z_BVAL_P(ret))) {
						/* no auto-render * /
						zval_ptr_dtor(&ret);
						zval_ptr_dtor(&action);
						zval_ptr_dtor(&icontroller);
						return 1;
					}
					zval_ptr_dtor(&ret);
				} else if ((ce = yaf_dispatcher_get_action(app_dir, icontroller,
								Z_STRVAL_P(module), is_def_module, Z_STRVAL_P(action), Z_STRLEN_P(action) TSRMLS_CC))
						&& (zend_hash_find(&(ce)->function_table, YAF_ACTION_EXECUTOR_NAME,
								sizeof(YAF_ACTION_EXECUTOR_NAME), (void **)&fptr) == SUCCESS)) {
					zval ***call_args;
					yaf_action_t *iaction;
					uint count = 0;

					MAKE_STD_ZVAL(iaction);
					object_init_ex(iaction, ce);

					yaf_controller_construct(ce, iaction, request, response, view, NULL TSRMLS_CC);
					executor = iaction;

					zend_update_property(ce, iaction, ZEND_STRL(YAF_CONTROLLER_PROPERTY_NAME_NAME), controller TSRMLS_CC);
					zend_update_property(ce, iaction, ZEND_STRL(YAF_ACTION_PROPERTY_NAME_CTRL), icontroller TSRMLS_CC);
					zval_ptr_dtor(&icontroller);

					if (fptr->common.num_args) {
						zval *method_name = NULL;

						yaf_dispatcher_get_call_parmaters(request_ce, request, fptr, &call_args, &count TSRMLS_CC);
						MAKE_STD_ZVAL(method_name);
						ZVAL_STRINGL(method_name, YAF_ACTION_EXECUTOR_NAME, sizeof(YAF_ACTION_EXECUTOR_NAME) - 1, 0);

						call_user_function_ex(&(ce)->function_table, &iaction, method_name, &ret, count, call_args, 1, NULL TSRMLS_CC);

						efree(method_name);
						efree(call_args);
					} else {
						zend_call_method_with_0_params(&iaction, ce, NULL, "execute", &ret);
					}

					if (!ret) {
						zval_ptr_dtor(&action);
						zval_ptr_dtor(&iaction);
						zval_ptr_dtor(&icontroller);
						return 0;
					}

					if (( Z_TYPE_P(ret) == IS_BOOL
								&& !Z_BVAL_P(ret))) {
						/* no auto-render * /
						zval_ptr_dtor(&ret);
						zval_ptr_dtor(&action);
						zval_ptr_dtor(&iaction);
						zval_ptr_dtor(&icontroller);
						return 1;
					}
				} else {
					zval_ptr_dtor(&icontroller);
					return 0;
				}

				if (executor) {
					/* controller's property can override the Dispatcher's * /
					int auto_render = 1;
					render = zend_read_property(ce, executor, ZEND_STRL(YAF_CONTROLLER_PROPERTY_NAME_RENDER), 1 TSRMLS_CC);
					instantly_flush	= zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_FLUSH), 1 TSRMLS_CC);
					if (render == EG(uninitialized_zval_ptr)) {
						render = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_RENDER), 1 TSRMLS_CC);
						auto_render = Z_BVAL_P(render);
					} else if (Z_TYPE_P(render) <= IS_BOOL && !Z_BVAL_P(render)) {
						auto_render = 0;
					}

					if (auto_render) {
						ret = NULL;
						if (!Z_BVAL_P(instantly_flush)) {
							zend_call_method_with_1_params(&executor, ce, NULL, "render", &ret, action);
							zval_ptr_dtor(&executor);

							if (!ret) {
								zval_ptr_dtor(&action);
								return 0;
							} else if (IS_BOOL == Z_TYPE_P(ret) && !Z_BVAL_P(ret)) {
								zval_ptr_dtor(&ret);
								zval_ptr_dtor(&action);
								return 0;
							}

							if (Z_TYPE_P(ret) == IS_STRING && Z_STRLEN_P(ret)) {
								yaf_response_alter_body(response, NULL, 0, Z_STRVAL_P(ret), Z_STRLEN_P(ret), YAF_RESPONSE_APPEND  TSRMLS_CC);
							} 

							zval_ptr_dtor(&ret);
						} else {
							zend_call_method_with_1_params(&executor, ce, NULL, "display", &ret, action);
							zval_ptr_dtor(&executor);

							if (!ret) {
								zval_ptr_dtor(&action);
								return 0;
							}

							if ((Z_TYPE_P(ret) == IS_BOOL && !Z_BVAL_P(ret))) {
								zval_ptr_dtor(&ret);
								zval_ptr_dtor(&action);
								return 0;
							} else {
								zval_ptr_dtor(&ret);
							}
						}
					} else {
						zval_ptr_dtor(&executor);
					}
				}
				zval_ptr_dtor(&action);
*/				
			}
			return true;
		}

		return false;
	}


	/**
	 * yaf_dispatcher_get_controller
	 *
	 * @param string $app_dir
	 * @param string $module
	 * @param string $controller
	 * @param boolean $def_module
	 * @return boolean | string
	 */
	private function _get_controller($app_dir, $module, $controller, $def_module)
	{
		if ($def_module) {
			$directory = $app_dir . '/controllers';
		} else {
			$directory = $app_dir . '/modules/' . $module . '/controllers';
		}

		if ($directory) {
			if (YAF_NAME_SUFFIX) {
				$class = $controller . YAF_NAME_SEPARATOR . 'Controller';
			} else {
				$class = 'Controller' . YAF_NAME_SEPARATOR . $controller;
			}

			if (!class_exists($class)) {

			}
/*
			if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void *)&ce) != SUCCESS) {

				if (!yaf_internal_autoload(controller, len, &directory TSRMLS_CC)) {
					yaf_trigger_error(YAF_ERR_NOTFOUND_CONTROLLER TSRMLS_CC, "Failed opening controller script %s: %s", directory, strerror(errno));
					efree(class);
					efree(class_lowercase);
					efree(directory);
					return NULL;
				} else if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void **) &ce) != SUCCESS)  {
					yaf_trigger_error(YAF_ERR_AUTOLOAD_FAILED TSRMLS_CC, "Could not find class %s in controller script %s", class, directory);
					efree(class);
					efree(class_lowercase);
					efree(directory);
					return 0;
				} else if (!instanceof_function(*ce, yaf_controller_ce TSRMLS_CC)) {
					yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "Controller must be an instance of %s", yaf_controller_ce->name);
					efree(class);
					efree(class_lowercase);
					efree(directory);
					return 0;
				}
			}
*/
			return $class;
		}

		return false;
	}

	/**
	 * yaf_application_is_module_name
	 *
	 * @param string $name
	 * @return boolean
	 */
	private function _is_module_name($name)
	{
		if ($name && is_string($name)) {
			$modules = $this->getApplication()->getModules();
			if ($modules && is_array($modules)) {
				foreach ($modules as $value) {
					if (strcasecmp($name, $value) == 0) {
						return true;
					}
				}
			}
		}
		return false;
	}

}
