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

final class Dispatcher
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
		$this->_router = new Router();
		$this->_default_module = ucfirst(YAF_G('default_module'));
		$this->_default_controller = ucfirst(YAF_G('default_controller'));
		$this->_default_action = strtolower(YAF_G('default_action'));

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
	 * @return Yaf\Dispatcher
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
	 * @return Yaf\Dispatcher
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
	 * @return boolean | Yaf\View_Interface
	 */
	public function initView($tpl_dir = null, $options = null)
	{
		if ($this->_view && is_object($this->_view)
				&& ($this->_view instanceof View_Interface)) {
			return $this->_view;
		}

		if ($this->_view = new View_Simple($tpl_dir, $options)) {
			return $this->_view;
		}

		return false;
	}

	/**
	 * setView
	 *
	 * @param Yaf\View_Interface $view
	 * @return boolean | Yaf\Dispatcher
	 */
	public function setView($view)
	{
		if ($view && is_object($view)
				&& ($view instanceof View_Interface)) {
			$this->_view = $view;
			return $this;
		}
		return false;
	}

	/**
	 * setRequest
	 *
	 * @param Yaf\Request_Abstract $request
	 * @return boolean | Yaf\Dispatcher
	 */
	public function setRequest($request)
	{
		if (is_object($request)
				&& ($request instanceof Request_Abstract)) {
			$this->_request = $request;
			return $this;
		}
		trigger_error('Expects a Yaf\Request_Abstract instance', E_USER_WARNING);
		return false;
	}

	/**
	 * getApplication
	 *
	 * @param void
	 * @return Yaf\Application
	 */
	public function getApplication()
	{
		return Application::app();
	}

	/**
	 * getRouter
	 *
	 * @param void
	 * @return Yaf\Router
	 */
	public function getRouter()
	{
		return $this->_router;
	}

	/**
	 * getRequest
	 *
	 * @param void
	 * @return Yaf\Request_Abstract
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
	 * @return boolean | Yaf\Dispatcher
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
	 * @return boolean | Yaf\Dispatcher
	 */
	public function setDefaultModule($module_name)
	{
		if ($module_name && is_string($module_name)
				&& $this->_is_module_name($module_name)) {
			$this->_default_module = ucfirst($module_name);
			return $this;
		}
		return false;
	}

	/**
	 * setDefaultController
	 *
	 * @param string $controller_name
	 * @return boolean | Yaf\Dispatcher
	 */
	public function setDefaultController($controller_name)
	{
		if ($controller_name && is_string($controller_name)) {
			$this->_default_controller = ucfirst($controller_name);
			return $this;
		}
		return false;
	}

	/**
	 * setDefaultAction
	 *
	 * @param string $action_name
	 * @return boolean | Yaf\Dispatcher
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
	 * @return boolean | Yaf\Dispatcher
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
	 * @return boolean | Yaf\Dispatcher
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
	 * @return boolean | Yaf\Dispatcher
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
	 * @return Yaf\Dispatcher
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
	 * @param Yaf\Request_Abstract $request
	 * @return boolean | string
	 */
	public function dispatch($request)
	{
		if ($request instanceof Request_Abstract) {
			$this->_request = $request;

			if (strncasecmp(PHP_SAPI, 'cli', 3)) {
				$response = new Response_Http();
			} else {
				$response = new Response_Cli();
			}

			if (!$request || !is_object($request)) {
				$this->_trigger_error('Expect a Yaf\Request_Abstract instance', YAF_ERR_TYPE_ERROR);
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
					} else {
						$this->_trigger_error($e->getMessage(), $e->getCode());
					}
				}

				if (!$this->_route($request)) {
					$this->_trigger_error('Routing request failed', YAF_ERR_ROUTE_FAILED);
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
					} else {
						$this->_trigger_error($e->getMessage(), $e->getCode());
					}
					unset($response);
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
				} else {
					$this->_trigger_error($e->getMessage(), $e->getCode());
				}
				unset($response);
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
					if (YAF_G('catch_exception')) {
						$this->_exception_handler($request, $response, $e);
					} else {
						$this->_trigger_error($e->getMessage(), $e->getCode());
					}
					unset($response);
				}

				try {
					$this->_handle($request, $response, $view);
				} catch (Exception $e) {
					if (YAF_G('catch_exception')) {
						$this->_exception_handler($request, $response, $e);
					} else {
						$this->_trigger_error($e->getMessage(), $e->getCode());
					}
					unset($response);
					return false;
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
					} else {
						$this->_trigger_error($e->getMessage(), $e->getCode());
					}
					unset($response);
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
				} else {
					$this->_trigger_error($e->getMessage(), $e->getCode());
				}
				unset($response);
			}

			if (0 == $nesting && !$request->isDispatched()) {
				try {
					$this->_trigger_error('The max dispatch nesting ' . YAF_FORWARD_LIMIT . ' was reached', YAF_ERR_DISPATCH_FAILED);
				} catch (Exception $e) {
					if (YAF_G('catch_exception')) {
						$this->_exception_handler($request, $response, $e);
					} else {
						$this->_trigger_error($e->getMessage(), $e->getCode());
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
	 * @return boolean | Yaf\Dispatcher
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
	 * @return boolean | Yaf\Dispatcher
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
	 * @param Yaf\Plugin_Abstract $plugin
	 * @return boolean | Yaf\Dispatcher
	 */
	public function registerPlugin($plugin)
	{
		if (is_object($plugin)
				&& ($plugin instanceof Plugin_Abstract)) {
			$this->_plugins[] = $plugin;
			return $this;
		} 
		trigger_error('Expects a Yaf\Plugin_Abstract instance', E_USER_WARNING);
		return false;
	}

	/**
	 * yaf_dispatcher_exception_handler
	 *
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
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
			if ($e && ($e instanceof Exception_LoadFailed_Controller)) {
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
	 * @param Yaf\Request_Abstract $request
	 */
	private function _route($request)
	{
		if (is_object($this->_router)) {
			if ($this->_router instanceof Router) {
				/* use built-in router */
				$this->_router->route($request);
			} else {
				/* user custom router */
				if (!method_exists($this->_router, 'route')
						|| $this->_router->route($request) === false) {
					$this->_trigger_error('Routing request failed', YAF_ERR_ROUTE_FAILED);
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
	 * @param Yaf\Request_Abstract $request
	 */
	private function _fix_default($request)
	{
		// module
		$module = $request->getModuleName();
		if ($module && is_string($module)) {
			$request->setModuleName(ucfirst($module));
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
			$request->setControllerName($this->_default_controller);
		}

		// action
		$action = $request->getActionName();
		if ($action && is_string($action)) {
			$request->setActionName(strtolower($action));
		} else {
			$request->setActionName($this->_default_action);
		}

	}
	
	/**
	 * yaf_dispatcher_handle
	 *
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @param Yaf\View_Interface $view
	 */
	private function _handle($request, $response, $view)
	{
		$app_dir = YAF_G('directory');

		$request->setDispatched(true);

		if (!$app_dir) {
			$this->_trigger_error('Yaf\Dispatcher requires Yaf\Application(which set the application.directory) to be initialized first', YAF_ERR_STARTUP_FAILED);
			return false;
		} else {
			$is_def_module = false;
			/* $is_def_ctr = false; */

			// module
			$module = $request->getModuleName();
			if (empty($module) || !is_string($module)) {
				$this->_trigger_error('Unexcepted a empty module name', YAF_ERR_DISPATCH_FAILED);
				return false;
			} elseif (!$this->_is_module_name($module)) {
				$this->_trigger_error('There is no module ' . $module, YAF_ERR_NOTFOUND_MODULE);
				return false;
			}

			// controller
			$controller	= $request->getControllerName();
			if (empty($controller) || !is_string($controller)) {
				$this->_trigger_error('Unexcepted a empty controller name', YAF_ERR_DISPATCH_FAILED);
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
					try {
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
							try {
								if (($content = call_user_func(array($executor, 'render'), $action)) === false) {
									return false;
								}
							} catch(Exception $e) {
								if (YAF_G('catch_exception')) {
									$this->_exception_handler($request, $response, $e);
								} else {
									$this->_trigger_error($e->getMessage(), $e->getCode());
								}
								return false;
							}

							if ($content && is_string($content)) {
								$response->appendBody($content);
							}
						} else {
							try {
								if (call_user_func(array($executor, 'display'), $action) === false) {
									return false;
								}
							} catch(Exception $e) {
								if (YAF_G('catch_exception')) {
									$this->_exception_handler($request, $response, $e);
								} else {
									$this->_trigger_error($e->getMessage(), $e->getCode());
								}
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
			$controller = ucfirst($controller);
			
			if (YAF_NAME_SUFFIX) {
				$class = $controller . YAF_NAME_SEPARATOR . 'Controller';
			} else {
				$class = 'Controller' . YAF_NAME_SEPARATOR . $controller;
			}

			if (!class_exists($class, false)) {
				if (!$this->_internal_autoload($controller, $directory, $file_path)) {
					$this->_trigger_error('Failed opening controller script ' . $file_path . ':' . YAF_ERR_NOTFOUND_CONTROLLER, YAF_ERR_NOTFOUND_CONTROLLER);
					return false;
				} elseif (!class_exists($class, false)) {
					$this->_trigger_error('Could not find class ' . $class . ' in controller script ' . $file_path, YAF_ERR_AUTOLOAD_FAILED);
					return false;
				} else {
					$root_class = $class;
					while($root_class = get_parent_class($root_class)) {
						if ($root_class == 'Yaf\Controller_Abstract') {
							break;
						}
					}
					if (!$root_class) {
						$this->_trigger_error('Controller must be an instance of Yaf\Controller_Abstract', YAF_ERR_TYPE_ERROR);
						return false;
					}
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
	 * @param Yaf\Controller_Abstract $controller
	 * @param string $module
	 * @param boolean $def_module
	 * @param string $action
	 * @return boolean | string
	 */
	private function _get_action($app_dir, $controller, $module, $def_module, $action)
	{
		if (is_array($controller->actions)) {
			if (isset($controller->actions[$action])) {
				$action_path = $app_dir . '/' . $controller->actions[$action];

				if (Loader::import($action_path)) {
					$action = ucfirst($action);

					if (YAF_NAME_SUFFIX) {
						$class = $action . YAF_NAME_SEPARATOR . 'Action';
					} else {
						$class = 'Action' . YAF_NAME_SEPARATOR . $action;
					}

					if (class_exists($class, false)) {
						if ($class instanceof Action_Abstract) {
							return $class;
						} else {
							$this->_trigger_error('Action ' . $class . ' must extends from Yaf\Action_Abstract', YAF_ERR_TYPE_ERROR);
						}
					} else {
						$this->_trigger_error('Could not find action ' . $action . ' in '. $action_path, YAF_ERR_NOTFOUND_ACTION);
					}
				} else {
					$this->_trigger_error('Failed opening action script ' . $action_path. ':'. YAF_ERR_NOTFOUND_ACTION, YAF_ERR_NOTFOUND_ACTION);
				}
			} else {
				$this->_trigger_error('There is no method ' . $action . 'Action in ' . get_class($controller) . '::actions', YAF_ERR_NOTFOUND_ACTION);
			}
		} else
/* {{{ This only effects internally */
		if (YAF_G('st_compatible')) {
			/**
			 * upper Action Name
			 * eg: Index_sub -> Index_Sub
			 */
			$action = ucwords($action);

			if ($def_module) {
				$directory = $app_dir . '/actions';
			} else {
				$directory = $app_dir . '/modules/' . $module . '/actions';
			}

			if (YAF_NAME_SUFFIX) {
				$class = $action . YAF_NAME_SEPARATOR . 'Action';
			} else {
				$class = 'Action' . YAF_NAME_SEPARATOR . $action;
			}

			if (!class_exists($class, false)) {
				if (!$this->_internal_autoload($action, $directory, $file_path)) {
					$this->_trigger_error('Failed opening action script ' . $file_path . ':' . YAF_ERR_NOTFOUND_ACTION, YAF_ERR_NOTFOUND_ACTION);
					return false;
				} elseif(!class_exists($class, false)) {
					$this->_trigger_error('Could not find class ' . $class . ' in action script ' . $file_path, YAF_ERR_AUTOLOAD_FAILED);
					return false;
				} elseif(!($class instanceof Action_Abstract)) {
					$this->_trigger_error('Action must be an instance of Yaf\Action_Abstract', YAF_ERR_TYPE_ERROR);
					return false;
				}
			}

			return $class;
		} else
/* }}} */
		{
			$this->_trigger_error('There is no method ' . $action . 'Action in ' . get_class($controller), YAF_ERR_NOTFOUND_ACTION);
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

	/**
	 * yaf_internal_autoload
	 *
	 * @param string $file_name
	 * @param string $directory
	 * @param string $file_path
	 * @return boolean
	 */
	private function _internal_autoload($file_name, $directory = null, &$file_path = null)
	{
		if (is_null($directory)) {
			$loader = Loader::getInstance();
			if (!$loader) {
				/* since only call from userspace can cause loader is NULL, exception throw will works well */
				trigger_error('Yaf\Loader need to be initialize first', E_USER_WARNING);
				return false;
			} else {
				if ($loader->isLocalName($file_name)) {
					$library_path = $loader->getLibraryPath();
				} else {
					$library_path = $loader->getLibraryPath(true);
				}
			}

			if (empty($library_path)) {
				trigger_error('Yaf\Loader requires Yaf\Application(which set the library_directory) to be initialized first', E_USER_WARNING);
				return false;
			}
		}

		if (($pos = strpos($file_name, '_')) !== false) {
			$file_name[$pos] = '/';
		}

		if (YAF_G('lowcase_path')) {
			$file_name = strtolower($file_name);
		}
		
		$file_path = $directory . '/' . $file_name . '.' . YAF_G('ext');

		return Loader::import($file_path);
	}

	/**
	 * yaf_trigger_error
	 * 
	 * @param string $message
	 * @param integer $code
	 */
	private function _trigger_error($message, $code = 0)
	{
		if (YAF_G('throw_exception')) {
			switch ($code) {
				case YAF_ERR_STARTUP_FAILED:
					throw new Exception_StartupError($message);
					break;
				case YAF_ERR_ROUTE_FAILED:
					throw new Exception_RouterFailed($message);
					break;
				case YAF_ERR_DISPATCH_FAILED:
					throw new Exception_DispatchFailed($message);
					break;
				case YAF_ERR_NOTFOUND_MODULE:
					throw new Exception_LoadFailed_Module($message);
					break;
				case YAF_ERR_NOTFOUND_CONTROLLER:
					throw new Exception_LoadFailed_Controller($message);
					break;
				case YAF_ERR_NOTFOUND_ACTION:
					throw new Exception_LoadFailed_Action($message);
					break;
				case YAF_ERR_NOTFOUND_VIEW:
					throw new Exception_LoadFailed_View($message);
					break;
				case YAF_ERR_CALL_FAILED:
					throw new Exception($message, $code);
					break;
				case YAF_ERR_AUTOLOAD_FAILED:
					throw new Exception_LoadFailed($message);
					break;
				case YAF_ERR_TYPE_ERROR:
					throw new Exception_TypeError($message);
					break;
				default:
					throw new Exception($message, $code);
					break;
			}
		} else {
			$this->getApplication()->setLastError($message, $code);
			trigger_error($message, E_USER_NOTICE);
		}
	}

}
