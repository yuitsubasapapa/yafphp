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
	 * @param string $tpl_dir
	 * @param array $options
	 * @return boolean | Yaf_View_Interface
	 */
	public function initView($tpl_dir = null, $options = null)
	{
		if ($this->_view && is_object($this->_view)
				&& ($this->_view instanceof Yaf_View_Interface)) {
			return $this->_view;
		}

		if ($this->_view = new Yaf_View_Simple($tpl_dir, $options)) {
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

			if (!($view = $this->initView())) {
				return false;
			}

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

		if (!($view = $this->initView())) {
			return false;
		}

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

			$ccontroller = $this->_get_controller($app_dir, $module, $controller, $is_def_module);
			if (!$ccontroller) {
				return false;
			} else {
				try {
					$icontroller = new $ccontroller($request, $response, $view);
				} catch(Exception $e) {
					return false;
				}

				try {
					$view_dir = $view->getScriptPath();
				} catch(Exception $e) {
					return false;
				}

				if (empty($view_dir) || !is_string($view_dir)) {
					/* view directory might be set by _constructor */
					if ($is_def_module) {
						$view_dir = $app_dir . '/views';
					} else {
						$view_dir = $app_dir . '/modules/' . $module . '/views';
					}
					/** tell the view engine where to find templates */
					try{
						$view->setScriptPath($view_dir);
					} catch(Exception $e) {
						return false;
					}
				}

				// action
				$action = $request->getActionName();
				$func_name = strtolower($action) . 'Action';
				$func_args = $request->getParams();

				/* because the action might call the forward to override the old action */
				if (method_exists($icontroller, $func_name)) {
					try {
						if (call_user_func_array(array($icontroller, $func_name), $func_args) === false) {
							/* no auto-render */
							return true;
						}
					} catch(Exception $e) {
						return false;
					}

					$executor = $icontroller;
				} elseif($caction = $this->_get_action($app_dir, $icontroller, $module, $is_def_module, $action)) {
					if (!method_exists($caction, 'execute')) {
						return false;
					}

					try {
						$iaction = new $caction($icontroller, $request, $response, $view);
						if (call_user_func_array(array($iaction, 'execute'), $func_args) === false) {
							/* no auto-render */
							return true;
						}
					} catch(Exception $e) {
						return false;
					}

					$executor = $iaction;
				} else {
					return false;
				}

				if ($executor) {
					/* controller's property can override the Dispatcher's */
					
					if (property_exists($executor, 'yafAutoRender')) {
						$auto_render = (boolean)$executor->yafAutoRender;
					} else {
						$auto_render = (boolean)$this->_router;
					}

					if ($auto_render) {
						if (!$this->_instantly_flush) {
							try{
								if (call_user_func(array($executor, 'render'), $action) === false) {
									return false;
								}
							} catch(Exception $e) {
								return false;
							}
						} else {
							try{
								if (call_user_func(array($executor, 'display'), $action) === false) {
									return false;
								}
							} catch(Exception $e) {
								return false;
							}
						}
					}
					
				}				
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

			if (!class_exists($class, false)) {
				$file_name = $controller;
				if (($pos = strpos($file_name, '_')) !== false) {
					$file_name[$pos] = '/';
				}

				if (YAF_G('lowcase_path')) {
					$file_name = strtolower($file_name);
				}
				
				$file_path = $directory . '/' . $file_name . '.' . YAF_G('ext');
				if (is_file($file_path) && include($file_path)) {
					if (!class_exists($class, false)) {
						throw new Yaf_Exception_LoadFailed('Could not find class ' . $class . ' in controller script ' . $file_path);
						return false;
					} else {
						$root_class = $class;
						while($root_class = get_parent_class($root_class)) {
							if ($root_class == 'Yaf_Controller_Abstract') {
								break;
							}
						}
						if (!$root_class) {
							throw new Yaf_Exception_TypeError('Controller must be an instance of Yaf_Controller_Abstract');
							return false;
						}
					}
					echo get_parent_class();
				} else {
					throw new Yaf_Exception_LoadFailed_Controller('Failed opening controller script ' . $file_path . ':' . YAF_ERR_NOTFOUND_CONTROLLER);
					return false;
				}
			}

			return $class;
		}

		return false;
	}

	/**
	 * yaf_dispatcher_get_action
	 *
	 * @param string $app_dir
	 * @param Yaf_Controller_Abstract $controller
	 * @param string $module
	 * @param boolean $def_module
	 * @param string $action
	 * @return boolean | string
	 */
	private function _get_action($app_dir, $controller, $module, $def_module, $action)
	{
		include($app_dir . '/controllers/'. $action .'Action.php');
		return $action.'Action';
		
/*
		zval **ppaction, *actions_map;

		actions_map = zend_read_property(Z_OBJCE_P(controller), controller, ZEND_STRL(YAF_CONTROLLER_PROPERTY_NAME_ACTIONS), 1 TSRMLS_CC);
		if (IS_ARRAY == Z_TYPE_P(actions_map)) {
			if (zend_hash_find(Z_ARRVAL_P(actions_map), action, len + 1, (void **)&ppaction) == SUCCESS) {
				char *action_path;
				uint action_path_len;

				action_path_len = spprintf(&action_path, 0, "%s%c%s", app_dir, DEFAULT_SLASH, Z_STRVAL_PP(ppaction));
				if (yaf_loader_import(action_path, action_path_len, 0 TSRMLS_CC)) {
					zend_class_entry **ce;
					char *class, *class_lowercase;
					uint  class_len;
					char *action_upper = estrndup(action, len);

					*(action_upper) = toupper(*action_upper);

					if (YAF_G(name_suffix)) {
						class_len = spprintf(&class, 0, "%s%s%s", action_upper, YAF_G(name_separator), "Action");
					} else {
						class_len = spprintf(&class, 0, "%s%s%s", "Action", YAF_G(name_separator), action_upper);
					}

					class_lowercase = zend_str_tolower_dup(class, class_len);

					if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void **) &ce) == SUCCESS) {
						efree(action_path);
						efree(action_upper);
						efree(class_lowercase);

						if (instanceof_function(*ce, yaf_action_ce TSRMLS_CC)) {
							efree(class);
							return *ce;
						} else {
							yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "Action %s must extends from %s", class, yaf_action_ce->name);
							efree(class);
						}

					} else {
						yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "Could not find action %s in %s", class, action_path);
					}

					efree(action_path);
					efree(action_upper);
					efree(class);
					efree(class_lowercase);

				} else {
					yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "Failed opening action script %s: %s", action_path, strerror(errno));
					efree(action_path);
				}
			} else {
				yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "There is no method %s%s in %s::$%s",
						action, "Action", Z_OBJCE_P(controller)->name, YAF_CONTROLLER_PROPERTY_NAME_ACTIONS);
			}
		} else
	/* {{{ This only effects internally * /
		   	if (YAF_G(st_compatible)) {
			char *directory, *class, *class_lowercase, *p;
			uint class_len;
			zend_class_entry **ce;
			char *action_upper = estrndup(action, len);

			/**
			 * upper Action Name
			 * eg: Index_sub -> Index_Sub
			 * /
			p = action_upper;
			*(p) = toupper(*p);
			while (*p != '\0') {
				if (*p == '_'
	#ifdef YAF_HAVE_NAMESPACE
						|| *p == '\\'
	#endif
				   ) {
					if (*(p+1) != '\0') {
						*(p+1) = toupper(*(p+1));
						p++;
					}
				}
				p++;
			}

			if (def_module) {
				spprintf(&directory, 0, "%s%c%s", app_dir, DEFAULT_SLASH, "actions");
			} else {
				spprintf(&directory, 0, "%s%c%s%c%s%c%s", app_dir, DEFAULT_SLASH,
						"modules", DEFAULT_SLASH, module, DEFAULT_SLASH, "actions");
			}

			if (YAF_G(name_suffix)) {
				class_len = spprintf(&class, 0, "%s%s%s", action_upper, YAF_G(name_separator), "Action");
			} else {
				class_len = spprintf(&class, 0, "%s%s%s", "Action", YAF_G(name_separator), action_upper);
			}

			class_lowercase = zend_str_tolower_dup(class, class_len);

			if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void *)&ce) != SUCCESS) {
				if (!yaf_internal_autoload(action_upper, len, &directory TSRMLS_CC)) {
					yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "Failed opening action script %s: %s", directory, strerror(errno));

					efree(class);
					efree(action_upper);
					efree(class_lowercase);
					efree(directory);
					return NULL;
				} else if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void **) &ce) != SUCCESS)  {
					yaf_trigger_error(YAF_ERR_AUTOLOAD_FAILED TSRMLS_CC, "Could find class %s in action script %s", class, directory);

					efree(class);
					efree(action_upper);
					efree(class_lowercase);
					efree(directory);
					return NULL;
				} else if (!instanceof_function(*ce, yaf_action_ce TSRMLS_CC)) {
					yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "Action must be an instance of %s", yaf_action_ce->name);

					efree(class);
					efree(action_upper);
					efree(class_lowercase);
					efree(directory);
					return NULL;
				}
			}

			efree(class);
			efree(action_upper);
			efree(class_lowercase);
			efree(directory);

			return *ce;
		} else
	/* }}} * /
		{
			yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "There is no method %s%s in %s", action, "Action", Z_OBJCE_P(controller)->name);
		}
*/
		return null;
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
