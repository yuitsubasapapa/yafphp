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
	protected $_render;
	protected $_return_response = false;
	protected $_instantly_flush = false;
	protected $_default_module;
	protected $_default_controller;
	protected $_default_action;

	/**
	 * __construct
	 *
	 */
	public function __construct()
	{
		$this->_router = new Yaf_Router();
		$this->_default_module = YAF_G('default_module');
		$this->_default_controller = YAF_G('default_controller');
		$this->_default_action = YAF_G('default_action');

		self::$_instance = $this;
	}

	/**
	 * getInstance
	 *
	 */
	public static function getInstance()
	{
		if (self::$_instance instanceof self) {
			return self::$_instance;
		}

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
		return $this->_router;
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
			trigger_error('Expects a Yaf_Request_Abstract instance', E_USER_WARNING);
			return false;
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
		$this->_view = $view;
		return $this;
	}

	/**
	 * setDefaultModule
	 *
	 */
	public function setDefaultModule($default_module_name)
	{
		$this->_default_module = $default_module_name;
		return $this;
	}

	/**
	 * setDefaultController
	 *
	 */
	public function setDefaultController($default_controller_name)
	{
		$this->_default_controller = $default_controller_name;
		return $this;
	}

	/**
	 * setDefaultAction
	 *
	 */
	public function setDefaultAction($default_action_name)
	{
		$this->_default_action = $default_action_name;
		return $this;
	}

	/**
	 * throwException
	 *
	 */
	public function throwException($switch = false)
	{
		if (func_num_args()) {
			YAF_G('throw_exception', (boolean)$switch);
			return $this;
		} else {
			return YAF_G('throw_exception');
		}
	}

	/**
	 * catchException
	 *
	 */
	public function catchException($switch = false)
	{
		if (func_num_args()) {
			YAF_G('catch_exception', (boolean)$switch);
			return $this;
		} else {
			return YAF_G('catch_exception');
		}
	}

	/**
	 * dispatch
	 *
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
				return null;
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
					return null;
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
						return null;
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
				return null;
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
	 * yaf_dispatcher_exception_handler
	 *
	 */
	private function _exception_handler($request, $response, &$exception)
	{
		if (YAF_G('in_exception') || !$exception) {
			return;
		}

		YAF_G('in_exception', true);

		$module = $request->getModuleName();
		if (!$module || !is_string($module) || !strlen($module)) {
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
	 */
	private function _fix_default($request)
	{
		// module
		$module = $request->getModuleName();
		if (!$module || !is_string($module) || !strlen($module)) {
			$request->setModuleName($this->_default_module);
		} else {
			$request->setModuleName(strtolower($module));
		}

		// controller
		$controller = $request->getControllerName();
		if (!$controller || !is_string($controller) || !strlen($controller)) {
			$request->setModuleName($this->_default_controller);
		} else {
			/**
			 * upper controller name
			 * eg: Index_sub -> Index_Sub
			 */
			$request->setControllerName(ucwords($controller));
		}

		// action
		$action = $request->getActionName();
		if (!$action || !is_string($action) || !strlen($action)) {
			$request->setModuleName($this->_default_action);
		} else {
			$request->seActionName(strtolower($action));
		}

	}
	
	/**
	 * yaf_dispatcher_handle
	 *
	 */
	private function _handle($request, $response, $view)
	{
		//throw new Yaf_Exception_LoadFailed_Controller('Controller load failed');
/*
		zend_class_entry *request_ce;
		char *app_dir = YAF_G(directory);

		request_ce = Z_OBJCE_P(request);

		yaf_request_set_dispatched(request, 1 TSRMLS_CC);
		if (!app_dir) {
			yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "%s requires %s(which set the application.directory) to be initialized first",
					yaf_dispatcher_ce->name, yaf_application_ce->name);
			return 0;
		} else {
			int	is_def_module = 0;
			int is_def_ctr = 0;
			zval *module, *controller, *dmodule, *dcontroller, *instantly_flush;
			zend_class_entry *ce;
			yaf_controller_t *executor;
			zend_function    *fptr;

			module		= zend_read_property(request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_MODULE), 1 TSRMLS_CC);
			controller	= zend_read_property(request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_CONTROLLER), 1 TSRMLS_CC);

			dmodule		= zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_MODULE), 1 TSRMLS_CC);
			dcontroller = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_CONTROLLER), 1 TSRMLS_CC);

			if (Z_TYPE_P(module) != IS_STRING
					|| !Z_STRLEN_P(module)) {
				yaf_trigger_error(YAF_ERR_DISPATCH_FAILED TSRMLS_CC, "Unexcepted a empty module name");
				return 0;
			} else if (!yaf_application_is_module_name(Z_STRVAL_P(module), Z_STRLEN_P(module) TSRMLS_CC)) {
				yaf_trigger_error(YAF_ERR_NOTFOUND_MODULE TSRMLS_CC, "There is no module %s", Z_STRVAL_P(module));
				return 0;
			}

			if (Z_TYPE_P(controller) != IS_STRING
					|| !Z_STRLEN_P(controller)) {
				yaf_trigger_error(YAF_ERR_DISPATCH_FAILED TSRMLS_CC, "Unexcepted a empty controller name");
				return 0;
			}

			if(strncasecmp(Z_STRVAL_P(dmodule), Z_STRVAL_P(module), Z_STRLEN_P(module)) == 0) {
				is_def_module = 1;
			}

			if (strncasecmp(Z_STRVAL_P(dcontroller), Z_STRVAL_P(controller), Z_STRLEN_P(controller)) == 0) {
				is_def_ctr = 1;
			}

			ce = yaf_dispatcher_get_controller(app_dir, Z_STRVAL_P(module), Z_STRVAL_P(controller), Z_STRLEN_P(controller), is_def_module TSRMLS_CC);
			if (!ce) {
				return 0;
			} else {
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
			}
			return 1;
		}
		return 0;
*/

		return false;
	}
	
}
