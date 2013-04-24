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

final class Yaf_Application
{
	protected static $_app;

	protected $config;
	protected $dispatcher;

	protected $_modules;
	protected $_running = false;
	protected $_environ = YAF_ENVIRON;

	protected $_err_no = 0;
	protected $_err_msg = '';

	/**
	 * __construct
	 * 
	 * @param mixed $config
	 * @param string $section
	 */
	public function __construct($config, $section = null)
	{
		if (empty($config)) return false;

		if (!is_null(self::$_app)) {
			unset($this);
			$this->_trigger_error('Only one application can be initialized');
			return false;
		}

		if (empty($section)) {
			$section = $this->_environ;
		}

		// yaf_config_instance
		if (is_string($config)) {
			$this->config = new Yaf_Config_Ini($config, $section);
		}
		if (is_array($config)) {
			$this->config = new Yaf_Config_Simple($config, true);
		}

		if (is_null($this->config)
				|| !is_object($this->config)
				|| !($this->config instanceof Yaf_Config_Abstract)
				|| $this->_parse_option() == false) {
			unset($this);
			$this->_trigger_error('Initialization of application config failed');
			return false;
		}

		// yaf_request_instance
		$request = new Yaf_Request_Http(null, YAF_G('base_uri'));
		YAF_G('base_uri', null);

		if(!$request){
			$this->_trigger_error('Initialization of request failed');
			return false;
		}

		// yaf_dispatcher_instance
		$this->dispatcher = Yaf_Dispatcher::getInstance();
		if (is_null($this->dispatcher)
				|| !is_object($this->dispatcher)
				|| !($this->dispatcher instanceof Yaf_Dispatcher)) {
			unset($this);
			$this->_trigger_error('Instantiation of application dispatcher failed');
			return false;
		}
		$this->dispatcher->setRequest($request);

		// yaf_loader_instance
		if (YAF_G('local_library')) {
			$loader = Yaf_Loader::getInstance(YAF_G('local_library'), YAF_G('global_library'));
		} else {
			$local_library = YAF_G('directory') . '/library';
			$loader = Yaf_Loader::getInstance($local_library, YAF_G('global_library'));
		}
		YAF_G('local_library', null);

		if (!$loader) {
			unset($this);
			$this->_trigger_error('Initialization of application auto loader failed');
			return false;
		}

		$this->_running = false;

		if (YAF_G('modules')) {
			$this->_modules = YAF_G('modules');
		} else {
			$this->_modules = null;
		}
		YAF_G('modules', null);

		self::$_app = $this;
	}

	/**
	 * run
	 *
	 * @param void
	 * @return boolean | string
	 */
	public function run()
	{
		if (is_bool($this->_running) && $this->_running) {
			$this->_trigger_error('An application instance already run');
			return true;
		}
		$this->_running = true;
		$request = $this->dispatcher->getRequest();
		if ($response = $this->dispatcher->dispatch($request)) {
			return $response;
		}
		return false;
	}

	/**
	 * execute
	 *
	 * @param callback $function
	 * @param mixed $parameter
	 * @return mixed
	 */
	public function execute($function, $parameter = null)
	{
		if (!is_string($function) && !is_array($function)) {
			$function = (string) $function;
		}

		if (!is_callable($function)) {
			trigger_error('First argument is expected to be a valid callback', E_USER_WARNING);
			return null;
		}

		$arguments = func_get_args();
		array_shift($arguments);
		if (($retval = call_user_func_array($function, $arguments)) == false) {
			$numargs = func_num_args();
			$function = is_array($function) ? implode('::', $function) : (string) $function;
			if ($numargs > 1) {
				$arguments1 = (string) $arguments[0];
				if ($numargs > 2) {
					$arguments2 = (string) $arguments[1];
					if ($numargs > 3) {
						trigger_error("Unable to call {$function}({$arguments1},{$arguments2},...)", E_USER_WARNING);
					} else {
						trigger_error("Unable to call {$function}({$arguments1},{$arguments2})", E_USER_WARNING);
					}
				} else {
					trigger_error("Unable to call {$function}({$arguments1})", E_USER_WARNING);
				}
			} else {
				trigger_error("Unable to call {$function}()", E_USER_WARNING);
			}
		}

		return $retval;
	}

	/**
	 * app
	 *
	 * @param void
	 * @return Yaf_Application
	 */
	public static function app()
	{
		return self::$_app;
	}

	/**
	 * environ
	 *
	 * @param void
	 * @return string
	 */
	public function environ()
	{
		return $this->_environ;
	}

	/**
	 * bootstrap
	 *
	 * @param void
	 * @return boolean | Yaf_Application
	 */
	public function bootstrap()
	{
		$retval = true;
		if (!class_exists('Bootstrap', false)) {
			if (YAF_G('bootstrap')) {
				$bootstrap_path = YAF_G('bootstrap');
			} else {
				$bootstrap_path = YAF_G('directory') . '/Bootstrap.' . YAF_G('ext');
			}

			if (!Yaf_Loader::import($bootstrap_path)) {
				trigger_error('Couldn\'t find bootstrap file ' . $bootstrap_path, E_USER_WARNING);
				return false;
			} elseif (!class_exists('Bootstrap', false)) {
				trigger_error('Couldn\'t find class Bootstrap in ' . $bootstrap_path, E_USER_WARNING);
				return false;
			} else {
				$bootstrap = new Bootstrap();
				if (!($bootstrap instanceof Yaf_Bootstrap_Abstract)) {
					trigger_error('Expect a Yaf_Bootstrap_Abstract instance, Bootstrap give', E_USER_WARNING);
					return false;
				}
			}
		}

		$methods = get_class_methods($bootstrap);
		foreach ($methods as $func) {
			if (strncasecmp($func, '_init', 5)) {
				continue;
			}
			call_user_func(array($bootstrap, $func), $this->dispatcher);
		}
		unset($bootstrap);

		return $this;
	}

	/**
	 * getConfig
	 *
	 * @param void
	 * @return Yaf_Config_Abstract
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * getModules
	 *
	 * @param void
	 * @return array
	 */
	public function getModules()
	{
		return $this->_modules;
	}

	/**
	 * getDispatcher
	 *
	 * @param void
	 * @return Yaf_Dispatcher
	 */
	public function getDispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * setAppDirectory
	 *
	 * @param string $directory
	 * @return boolean | Yaf_Application
	 */
	public function setAppDirectory($directory)
	{
		if (is_string($directory)
				&& ($directory = realpath($directory))) {
			YAF_G('directory', $directory);
			return $this;
		}
		return false;
	}

	/**
	 * getAppDirectory
	 *
	 * @param void
	 * @return string
	 */
	public function getAppDirectory()
	{
		return YAF_G('directory');
	}

	/**
	 * getLastErrorNo
	 *
	 * @param void
	 * @return integer
	 */
	public function getLastErrorNo()
	{
		return $this->_err_no;
	}

	/**
	 * getLastErrorMsg
	 *
	 * @param void
	 * @return string
	 */
	public function getLastErrorMsg()
	{
		return $this->_err_msg;
	}

	/**
	 * clearLastError
	 *
	 * @param void
	 * @return Yaf_Application
	 */
	public function clearLastError()
	{
		$this->_err_no = 0;
		$this->_err_msg = '';
		return $this;
	}

	/**
	 * setLastError
	 *
	 * @param string $err_msg
	 * @param integer $err_no
	 * @return Yaf_Application
	 */
	public function setLastError($err_msg, $err_no = 0)
	{
		$this->_err_msg = $err_msg;
		$this->_err_no = $err_no;
		return $this;
	}

	/**
	 * __destruct
	 *
	 * @param void
	 */
	public function __destruct()
	{
		// runtime
		if (YAF_DEBUG) {
			$runtime = round((microtime(true) - YAF_RUNTIME) * 1000, 2);
			echo '<hr>[' . $runtime . 'ms]';
		}
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
	 * yaf_application_parse_option
	 *
	 * @param mixed $config
	 * @return boolean
	 */
	private function _parse_option($config = null)
	{
		global $YAF_G;

		if (is_null($config)) $config = $this->config;

		if (!($config instanceof Yaf_Config_Abstract)){
			return false;
		}

		if (!isset($config->application)) {
			/* For back compatibilty */
			if (!isset($config->yaf)) {
				$this->_trigger_error('Expected an array of application configure', YAF_ERR_TYPE_ERROR);
				return false;
			}
		}

		$app = isset($config->application) ? $config->application : $config->yaf;
		if (!($app instanceof Yaf_Config_Abstract)) {
			$this->_trigger_error('Expected an array of application configure', YAF_ERR_TYPE_ERROR);
			return false;
		}

		if (!isset($app->directory)) {
			$this->_trigger_error('Expected a directory entry in application configures');
			return false;
		}

		$YAF_G['directory'] = rtrim($app->directory, '\\ /');

		if (isset($app->ext) && is_string($app->ext)) {
			$YAF_G['ext'] = $app['ext'];
		}

		if (isset($app->bootstrap) && is_string($app->bootstrap)) {
			$YAF_G['bootstrap'] = $app->bootstrap;
		}

		if (isset($app->library)) {
			if (is_string($app->library)) {
				$YAF_G['local_library'] = $app->library;
			} elseif ($app->library instanceof Yaf_Config_Abstract) {
				if (isset($app->library->directory) && is_string($app->library->directory)) {
					$YAF_G['local_library'] = $app->library->directory;
				}
				if (isset($app->library->namespace) && is_string($app->library->namespace)) {
					$target = str_replace(',', DIRECTORY_SEPARATOR, $app->library->namespace);
					if (empty($YAF_G['namespaces'])) {
						$YAF_G['local_namespaces'] = $target;
					} else {
						$YAF_G['local_namespaces'] .= $target;
					}
				}
			}
		}

		if (isset($app->view) && ($app->view instanceof Yaf_Config_Abstract)) {
			if (isset($app->view->ext) && is_string($app->view->ext)) {
				$YAF_G['view_ext'] = $app->view->ext;
			}
		}

		if (isset($app->baseUri) && is_string($app->baseUri)) {
			$YAF_G['base_uri'] = $app->baseUri;
		}

		if (isset($app->dispatcher) && ($app->dispatcher instanceof Yaf_Config_Abstract)) {
			if (isset($app->dispatcher->defaultModule)
					&& is_string($app->dispatcher->defaultModule)) {
				$YAF_G['default_module'] = $app->dispatcher->defaultModule;
			}

			if (isset($app->dispatcher->defaultController)
					&& is_string($app->dispatcher->defaultController)) {
				$YAF_G['default_controller'] = $app->dispatcher->defaultController;
			}

			if (isset($app->dispatcher->defaultAction)
					&& is_string($app->dispatcher->defaultAction)) {
				$YAF_G['default_action'] = $app->dispatcher->defaultAction;
			}

			if (isset($app->dispatcher->defaultRoute)
					&& ($app->dispatcher->defaultRoute instanceof Yaf_Config_Abstract)) {
				$YAF_G['default_route'] = $app->dispatcher->defaultRoute->toArray();
			}

			if (isset($app->dispatcher->throwException)) {
				$YAF_G['throw_exception'] = (boolean)$app->dispatcher->throwException;
			}

			if (isset($app->dispatcher->catchException)) {
				$YAF_G['catch_exception'] = (boolean)$app->dispatcher->catchException;
			}
		}

		if (isset($app->modules) && is_string($app->modules)) {
			$seg = strtok($app->modules, ',');
			while ($seg) {
				$seg = trim($seg);
				if (strlen($seg)) {
					$YAF_G['modules'][] = $seg;
				}
				$seg = strtok(',');
			}
		} else {
			$YAF_G['modules'][] = YAF_G('default_module');
		}

		if (isset($app->system) && ($app->system instanceof Yaf_Config_Abstract)) {
			foreach ($app->system as $key => $value) {
				if (is_string($key)) {
					$YAF_G[$key] = (string)$value;
				}
			}
		}

		return true;
	}

	/**
	 * yaf_trigger_error
	 * 
	 * @param string $message
	 * @param integer $code
	 */
	private function _trigger_error($message, $code = YAF_ERR_STARTUP_FAILED)
	{
		if (YAF_G('throw_exception')) {
			switch ($code) {
				case YAF_ERR_STARTUP_FAILED:
					throw new Yaf_Exception_StartupError($message);
					break;
				case YAF_ERR_TYPE_ERROR:
					throw new Yaf_Exception_TypeError($message);
					break;
				default:
					throw new Yaf_Exception($message, $code);
					break;
			}
		} else {
			$this->_err_no = $code;
			$this->_err_msg = $message;
			trigger_error($message, E_USER_NOTICE);
		}
	}

}
