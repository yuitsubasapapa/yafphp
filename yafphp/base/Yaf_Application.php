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
	protected static $_app = null;

	protected $_config = null;
	protected $_dispatcher;
	protected $_modules = array();

	protected $_run = false;
	protected $_environ = YAF_ENVIRON;

	protected $_err_no = 0;
	protected $_err_msg = '';

	private $_g = array(
		'directory' => '',
		'ext' => 'php',
		'global_library' => YAF_LIBRARY,
		'local_library' => null,
		'local_namespaces' => '',
		'view_ext' => 'phtml',
		'base_uri' => null,
		'default_module' => 'index',
		'default_controller' => 'index',
		'default_action' => 'index',
		'default_route' => array(),
		'throw_exception' => true,
		'catch_exception' => false,
		'in_exception' => false,
		'modules' => array(),
	);

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
			throw new Yaf_Exception_StartupError('Only one application can be initialized');
			return false;
		}

		if (empty($section)) {
			$section = $this->_environ;
		}

		// yaf_config_instance
		if (is_string($config)) {
			$this->_config = new Yaf_Config_Ini($config, $section);
		}
		if (is_array($config)) {
			$this->_config = new Yaf_Config_Simple($config, true);
		}

		if (is_null($this->_config)
				|| !is_object($this->_config)
				|| !($this->_config instanceof Yaf_Config_Abstract)
				|| $this->_parse_option() == false) {
			unset($this);
			throw new Yaf_Exception_StartupError('Initialization of application config failed');
			return false;
		}

		// yaf_request_instance
		$request = new Yaf_Request_Http(null, $this->_g['base_uri']);
		unset($this->_g['base_uri']);

		if(!$request){
			throw new Yaf_Exception_StartupError('Initialization of request failed');
			return false;
		}

		// yaf_dispatcher_instance
		$this->_dispatcher = Yaf_Dispatcher::getInstance($this->_g);
		if (is_null($this->_dispatcher)
				|| !is_object($this->_dispatcher)
				|| !($this->_dispatcher instanceof Yaf_Dispatcher)) {
			unset($this);
			throw new Yaf_Exception_StartupError('Instantiation of application dispatcher failed');
			return false;
		}
		$this->_dispatcher->setRequest($request);

		// yaf_loader_instance
		if ($this->_g['local_library']) {
			$loader = Yaf_Loader::getInstance($this->_g['local_library'], $this->_g['global_library']);
		} else {
			$local_library = $this->_g['directory'] . '/library';
			$loader = Yaf_Loader::getInstance($local_library, $this->_g['global_library']);
		}
		unset($this->_g['local_library']);

		if (!$loader) {
			unset($this);
			throw new Yaf_Exception_StartupError('Initialization of application auto loader failed');
			return false;
		}

		$this->_run = false;

		if ($this->_g['modules']) {
			$this->_modules = $this->_g['modules'];
			
		} else {
			$this->_modules = null;
		}
		unset($this->_g['modules']);

		self::$_app = $this;
	}


	/**
	 * run
	 *
	 * @param void
	 * @return mixed
	 */
	public function run()
	{
		if (is_bool($this->_run) && $this->_run) {
			throw new Yaf_Exception_StartupError('An application instance already run');
			return true;
		}

		$this->_run = true;

		if ($response = $this->_dispatcher->dispatch($this->_dispatcher->getRequest())) {
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
	 * @return Yaf_Application
	 */
	public function bootstrap()
	{
		$retval = true;
		if (!class_exists('Bootstrap')) {
			if (isset($this->_g['bootstrap'])) {
				$bootstrap_path = $this->_g['bootstrap'];
			} else {
				$bootstrap_path = $this->_g['directory'] . '/Bootstrap.' . $this->_g['ext'];
			}

			if (!Yaf_Loader::import($bootstrap_path)) {
				trigger_error('Couldn\'t find bootstrap file ' . $bootstrap_path, E_USER_WARNING);
				return false;
			} elseif (!class_exists('Bootstrap')) {
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
			call_user_func(array($bootstrap, $func), $this->_dispatcher);
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
		return $this->_config;
	}

	/**
	 * geModules
	 *
	 * @param void
	 * @return array
	 */
	public function geModules()
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
		return $this->_dispatcher;
	}

	/**
	 * setAppDirectory
	 *
	 * @param string $directory
	 * @return Yaf_Application
	 */
	public function setAppDirectory($directory)
	{
		if (is_string($directory)
				&& ($directory = realpath($directory))) {
			$this->_g['directory'] = $directory;
		}

		return $this;
	}

	/**
	 * getAppDirectory
	 *
	 * @param void
	 * @return string
	 */
	public function getAppDirectory()
	{
		return $this->_g['directory'];
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
	 * __destruct
	 *
	 * @param void
	 */
	public function __destruct()
	{
		
	}

	/**
	 * __clone
	 *
	 * @param void
	 */
	public function __clone()
	{
		
	}

	/**
	 * __sleep
	 *
	 * @param void
	 */
	public function __sleep()
	{
		
	}

	/**
	 * __wakeup
	 *
	 * @param void
	 */
	public function __wakeup()
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
		if (is_null($config)) $config = $this->_config;

		if (!($config instanceof Yaf_Config_Abstract)){
			return false;
		}

		if (!isset($config->application)) {
			/* For back compatibilty */
			if (!isset($config->yaf)) {
				throw new Yaf_Exception_TypeError('Expected an array of application configure');
				return false;
			}
		}

		$app = isset($config->application) ? $config->application : $config->yaf;
		if (!($app instanceof Yaf_Config_Abstract)) {
			throw new Yaf_Exception_TypeError('Expected an array of application configure');
			return false;
		}

		if (!isset($app->directory)) {
			throw new Yaf_Exception_StartupError('Expected a directory entry in application configures');
			return false;
		}

		$this->_g['directory'] = rtrim($app->directory, '\\ /');

		if (isset($app->ext) && is_string($app->ext)) {
			$this->_g['ext'] = $app['ext'];
		}

		if (isset($app->bootstrap) && is_string($app->bootstrap)) {
			$this->_g['bootstrap'] = $app->bootstrap;
		}

		if (isset($app->library)) {
			if (is_string($app->library)) {
				$this->_g['local_library'] = $app->library;
			} elseif ($app->library instanceof Yaf_Config_Abstract) {
				if (isset($app->library->directory) && is_string($app->library->directory)) {
					$this->_g['local_library'] = $app->library->directory;
				}
				if (isset($app->library->namespace) && is_string($app->library->namespace)) {
					$target = str_replace(',', DIRECTORY_SEPARATOR, $app->library->namespace);
					if (empty($this->_g['namespaces'])) {
						$this->_g['local_namespaces'] = $target;
					} else {
						$this->_g['local_namespaces'] .= $target;
					}
				}
			}
		}

		if (isset($app->view) && ($app->view instanceof Yaf_Config_Abstract)) {
			if (isset($app->view->ext) && is_string($app->view->ext)) {
				$this->_g['view_ext'] = $app->view->ext;
			}
		}

		if (isset($app->baseUri) && is_string($app->baseUri)) {
			$this->_g['base_uri'] = $app->baseUri;
		}

		if (isset($app->dispatcher) && ($app->dispatcher instanceof Yaf_Config_Abstract)) {
			if (isset($app->dispatcher->defaultModule)
					&& is_string($app->dispatcher->defaultModule)) {
				$this->_g['default_module'] = $app->dispatcher->defaultModule;
			}

			if (isset($app->dispatcher->defaultController)
					&& is_string($app->dispatcher->defaultController)) {
				$this->_g['default_controller'] = $app->dispatcher->defaultController;
			}

			if (isset($app->dispatcher->defaultAction)
					&& is_string($app->dispatcher->defaultAction)) {
				$this->_g['default_action'] = $app->dispatcher->defaultAction;
			}

			if (isset($app->dispatcher->defaultRoute)
					&& ($app->dispatcher->defaultRoute instanceof Yaf_Config_Abstract)) {
				$this->_g['default_route'] = $app->dispatcher->defaultRoute->toArray();
			}

			if (isset($app->dispatcher->throwException)) {
				$this->_g['throw_exception'] = (boolean) $app->dispatcher->throwException;
			}

			if (isset($app->dispatcher->catchException)) {
				$this->_g['catch_exception'] = (boolean) $app->dispatcher->catchException;
			}
		}

		if (isset($app->modules) && is_string($app->modules)) {
			$seg = strtok($app->modules, ',');
			while ($seg) {
				$seg = trim($seg);
				if (strlen($seg)) {
					$this->_g['modules'][] = $seg;
				}
				$seg = strtok(',');
			}
		} else {
			$this->_g['modules'][] = $this->_g['default_module'];
		}

		if (isset($app->system) && ($app->system instanceof Yaf_Config_Abstract)) {
			foreach ($app->system as $key => $value) {
				if (is_string($key)) @ini_set($key, (string)$value);
			}
		}

		return true;
	}

}
