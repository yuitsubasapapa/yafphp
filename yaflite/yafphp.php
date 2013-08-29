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

// yafphp runtime
defined('YAF_RUNTIME') or define('YAF_RUNTIME', microtime(true));

// yafphp constant
define('YAF_VERSION', '1.2.2');
define('YAF_ERR_STARTUP_FAILED', 512);
define('YAF_ERR_ROUTE_FAILED', 513);
define('YAF_ERR_DISPATCH_FAILED', 514);
define('YAF_ERR_NOTFOUND_MODULE', 515);
define('YAF_ERR_NOTFOUND_CONTROLLER', 516);
define('YAF_ERR_NOTFOUND_ACTION', 517);
define('YAF_ERR_NOTFOUND_VIEW', 518);
define('YAF_ERR_CALL_FAILED', 519);
define('YAF_ERR_AUTOLOAD_FAILED', 520);
define('YAF_ERR_TYPE_ERROR', 521);

// yafphp config constant
defined('YAF_ENVIRON') or define('YAF_ENVIRON', 'product');
defined('YAF_LIBRARY') or define('YAF_LIBRARY', null);
defined('YAF_NAME_SUFFIX') or define('YAF_NAME_SUFFIX', true);
defined('YAF_NAME_SEPARATOR') or define('YAF_NAME_SEPARATOR', '');
defined('YAF_FORWARD_LIMIT') or define('YAF_FORWARD_LIMIT', 5);
defined('YAF_CACHE_CONFIG') or define('YAF_CACHE_CONFIG', false);
defined('YAF_USE_NAMESPACE') or define('YAF_USE_NAMESPACE', false);
defined('YAF_USE_SPL_AUTOLOAD') or define('YAF_USE_SPL_AUTOLOAD', true);

// debug
defined('YAF_DEBUG') or define('YAF_DEBUG', false);

// YAF_G
$YAF_G = array(
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
 * YAF_G
 * 
 * @param string $name
 * @param mixed $value
 * @return mixed
 */
function YAF_G($name, $value = null)
{
	global $YAF_G;

	$num_args = func_num_args();
	if ($num_args == 1) {
		if (isset($YAF_G[$name])) {
			return $YAF_G[$name];
		} else {
			return false;
		}
	} elseif ($num_args == 2) {
		if (is_null($value)) {
			unset($YAF_G[$name]);
		} else {
			$YAF_G[$name] = $value;
		}
	} elseif ($num_args > 2) {
		$value = func_get_args();
		array_shift($value);
		$YAF_G[$name] = $value;
	}
}

/**
 * __autoload
 * 
 * @param string $classname
 */
function __autoload($classname)
{
	$classfile = $classname;
	if (strtok($classfile, '_') == 'Yaf')  // yafphp core class
	{
		$classpath = dirname(__FILE__) . '/' . strtolower(strtok('_')) . '/' . $classfile . '.php';
		if (is_file($classpath)) {
			include($classpath);
		} else {
			$classpath	= dirname(__FILE__) . '/base/' . $classfile . '.php';
			if (is_file($classpath)) include($classpath);
		}
	} else {
		if ($loader = Yaf_Loader::getInstance()) {
			$loader->autoload($classname);
		}
	}

	return class_exists($classname, false) || interface_exists($classname, false);
}

(YAF_USE_SPL_AUTOLOAD == false) or spl_autoload_register('__autoload');


/**
 * Yaf_Application
 * 
 */
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


/**
 * Yaf_Loader
 * 
 */
final class Yaf_Loader
{
	protected static $_instance;

	protected $_library_directory;
	protected $_global_library_directory;
	protected $_local_ns;

	/**
	 * __construct
	 * 
	 * @param void
	 */
	private function __construct()
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
	 * autoload
	 *
	 * @param string $class_name
	 * @return 
	 */
	public function autoload($class_name)
	{
		if (!is_string($class_name)) {
			return false;
		}

		$separator_len = strlen(YAF_NAME_SEPARATOR);
		$app_directory = YAF_G('directory');
		$origin_classname = $class_name;

		do {
			if (empty($class_name)){
				break;
			}
			
			if (YAF_USE_NAMESPACE) {
				$origin_lcname = $class_name;
				// class name with namespace in PHP 5.3
				if (strpos($class_name, '\\') !== false) {
					$class_name = str_replace('\\', '_', $class_name);
				}
			}

			if (strncmp($class_name, 'Yaf_', 3) == 0) {
				trigger_error('You should not use \'Yaf_\' as class name prefix', E_USER_WARNING);
			}

			if ($this->_is_category($class_name, 'Model')) {
				/* this is a model class */
				$directory = $app_directory . '/models';
				
				if (YAF_NAME_SUFFIX) {
					$file_name = substr($class_name, 0, -(5 + $separator_len));
				} else {
					$file_name = substr($class_name, 5 + $separator_len);
				}

				break;
			}

			if ($this->_is_category($class_name, 'Plugin')) {
				/* this is a plugin class */
				$directory = $app_directory . '/plugins';

				if (YAF_NAME_SUFFIX) {
					$file_name = substr($class_name, 0, -(6 + $separator_len));
				} else {
					$file_name = substr($class_name, 6 + $separator_len);
				}

				break;
			}

			if ($this->_is_category($class_name, 'Controller')) {
				/* this is a controller class */
				$directory = $app_directory . '/controllers';

				if (YAF_NAME_SUFFIX) {
					$file_name = substr($class_name, 0, -(10 + $separator_len));
				} else {
					$file_name = substr($class_name, 10 + $separator_len);
				}

				break;
			}

/* {{{ This only effects internally */
			if (YAF_G('st_compatible') && (strncmp($class_name, 'Dao_', 4) == 0
						|| strncmp($class_name, 'Service_', 8) == 0)) {
				/* this is a model class */
				$directory = $app_directory . '/models';
			}
/* }}} */

		} while(0);

		if (!$app_directory && $directory) {
			trigger_error('Couldn\'t load a framework MVC class without an Yaf_Application initializing', E_USER_WARNING);
			return false;
		}

		if (!YAF_USE_SPL_AUTOLOAD) {
			/** directory might be NULL since we passed a NULL */
			if ($this->_internal_autoload($file_name, $directory)) {
				if (class_exists($origin_classname, false)) {
					return true;
				} else {
					trigger_error('Could not find class ' . $class_name . ' in ' . $directory, E_USER_WARNING);
				}
			}  else {
				trigger_error('Failed opening script ' . $directory . ':' . YAF_ERR_AUTOLOAD_FAILED, E_USER_WARNING);
			}
			return true;
		} else {
			if ($this->_internal_autoload($file_name, $directory) &&
					class_exists($origin_classname, false)) {
				return true;
			}
			return false;
		}

	}
	
	/**
	 * getInstance
	 *
	 * @param string $library
	 * @param string $global_library
	 * @return Yaf_Loader
	 */
	public static function getInstance($library = null, $global_library = null)
	{
		if (!(self::$_instance instanceof self)) {
			if ((is_null($library) && is_null($global_library))
					|| (empty($library) && empty($global_library))
				){
				return;
			}

			self::$_instance = new self();
		}
		
		if ($library && is_string($library)) {
			self::$_instance->_library_directory = $library;
		}

		if ($global_library && is_string($global_library)) {
			self::$_instance->_global_library_directory = $global_library;
		}
		return self::$_instance;
	}
	
	/**
	 * registerLocalNamespace
	 *
	 * @param mixed $namespace
	 * @return boolean | Yaf_Loader
	 */
	public function registerLocalNamespace($namespace)
	{
		if (is_string($namespace)) {
			if ($local_ns = YAF_G('local_namespaces')) {
				YAF_G('local_namespaces', $local_ns . '/' . $namespace);
			} else {
				YAF_G('local_namespaces', $namespace);
			}
			return $this;
		} elseif(is_array($namespace)) {
			$local_ns = array();
			foreach ($namespace as $key => $value) {
				if (is_string($value)) {
					$local_ns[] = $value;
				}
			}
			YAF_G('local_namespaces', implode('/', $local_ns));
			return $this;
		}

		return false;
	}
	
	/**
	 * getLocalNamespace
	 *
	 * @param void
	 * @return string
	 */
	public function getLocalNamespace()
	{
		if ($local_ns = YAF_G('local_namespaces')) {
			return (string)$local_ns;
		}
		return null;
	}
	
	/**
	 * clearLocalNamespace
	 *
	 * @param void
	 * @return boolean
	 */
	public function clearLocalNamespace()
	{
		YAF_G('local_namespaces', null);
		return true;
	}
	
	/**
	 * isLocalName
	 *
	 * @param string $class_name
	 * @return boolean
	 */
	public function isLocalName($class_name)
	{
		if (!is_string($class_name)) {
			return false;
		}

		if (!($local_ns = YAF_G('local_namespaces'))) {
			return false;
		}

		if (($pos = strpos($class_name, '_')) !== false) {
			$prefix = substr($class_name, 0, $pos - 1);
		} elseif(YAF_USE_NAMESPACE && ($pos = strpos($class_name, '\\')) !== false) {
			$prefix = substr($class_name, 0, $pos - 1);
		} else{
			$prefix = $class_name;
		}

		return in_array($prefix, explode('/', $local_ns));
	}

	/**
	 * import
	 *
	 * @param string $file_name
	 * @return boolean
	 */
	public static function import($file_name)
	{
		if (is_file($file_name) && is_readable($file_name)) {
			require_once($file_name);
			return true;
		}
		return false;
	}
	
	/**
	 * setLibraryPath
	 *
	 * @param string $path
	 * @param boolean $global
	 * @return Yaf_Loader
	 */
	public function setLibraryPath($path, $global = false)
	{
		if (!$global) {
			$this->_library_directory = $path;
		} else {
			$this->_global_library_directory = $path;
		}
	}

	/**
	 * getLibraryPath
	 *
	 * @param boolean $global
	 * @return string
	 */
	public function getLibraryPath($global = false)
	{
		if (!$global) {
			return $this->_library_directory;
		} else {
			return $this->_global_library_directory;
		}
	}

	/**
	 * yaf_loader_is_category
	 *
	 * @param string $class
	 * @param string $category
	 * @return boolean
	 */
	private function _is_category($class, $category)
	{
		$class_len = strlen($class);
		$category = YAF_NAME_SUFFIX ? YAF_NAME_SEPARATOR . $category : $category . YAF_NAME_SEPARATOR;
		$category_len = strlen($category);

		if (YAF_NAME_SUFFIX) {
			if ($class_len > $category_len && strncmp(substr($class, -$category_len), $category, $category_len) == 0) {
				return true;
			}
		} else {
			if (strncmp($class, $category, $category_len) == 0) {
				return true;
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
			$loader = Yaf_Loader::getInstance();
			if (!$loader) {
				/* since only call from userspace can cause loader is NULL, exception throw will works well */
				trigger_error('Yaf_Loader need to be initialize first', E_USER_WARNING);
				return false;
			} else {
				if ($loader->isLocalName($file_name)) {
					$library_path = $loader->getLibraryPath();
				} else {
					$library_path = $loader->getLibraryPath(true);
				}
			}

			if (empty($library_path)) {
				trigger_error('Yaf_Loader requires Yaf_Application(which set the library_directory) to be initialized first', E_USER_WARNING);
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

		return Yaf_Loader::import($file_path);
	}
	
}


/**
 * Yaf_Dispatcher
 * 
 */
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
			$this->_default_module = ucfirst($module_name);
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
			$this->_default_controller = ucfirst($controller_name);
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
				$this->_trigger_error('Expect a Yaf_Request_Abstract instance', YAF_ERR_TYPE_ERROR);
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
	 * @param Yaf_Request_Abstract $request
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
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @param Yaf_View_Interface $view
	 */
	private function _handle($request, $response, $view)
	{
		$app_dir = YAF_G('directory');

		$request->setDispatched(true);

		if (!$app_dir) {
			$this->_trigger_error('Yaf_Dispatcher requires Yaf_Application(which set the application.directory) to be initialized first', YAF_ERR_STARTUP_FAILED);
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
						if ($root_class == 'Yaf_Controller_Abstract') {
							break;
						}
					}
					if (!$root_class) {
						$this->_trigger_error('Controller must be an instance of Yaf_Controller_Abstract', YAF_ERR_TYPE_ERROR);
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
	 * @param Yaf_Controller_Abstract $controller
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

				if (Yaf_Loader::import($action_path)) {
					$action = ucfirst($action);

					if (YAF_NAME_SUFFIX) {
						$class = $action . YAF_NAME_SEPARATOR . 'Action';
					} else {
						$class = 'Action' . YAF_NAME_SEPARATOR . $action;
					}

					if (class_exists($class, false)) {
						if ($class instanceof Yaf_Action_Abstract) {
							return $class;
						} else {
							$this->_trigger_error('Action ' . $class . ' must extends from Yaf_Action_Abstract', YAF_ERR_TYPE_ERROR);
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
				} elseif(!($class instanceof Yaf_Action_Abstract)) {
					$this->_trigger_error('Action must be an instance of Yaf_Action_Abstract', YAF_ERR_TYPE_ERROR);
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
			$loader = Yaf_Loader::getInstance();
			if (!$loader) {
				/* since only call from userspace can cause loader is NULL, exception throw will works well */
				trigger_error('Yaf_Loader need to be initialize first', E_USER_WARNING);
				return false;
			} else {
				if ($loader->isLocalName($file_name)) {
					$library_path = $loader->getLibraryPath();
				} else {
					$library_path = $loader->getLibraryPath(true);
				}
			}

			if (empty($library_path)) {
				trigger_error('Yaf_Loader requires Yaf_Application(which set the library_directory) to be initialized first', E_USER_WARNING);
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

		return Yaf_Loader::import($file_path);
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
					throw new Yaf_Exception_StartupError($message);
					break;
				case YAF_ERR_ROUTE_FAILED:
					throw new Yaf_Exception_RouterFailed($message);
					break;
				case YAF_ERR_DISPATCH_FAILED:
					throw new Yaf_Exception_DispatchFailed($message);
					break;
				case YAF_ERR_NOTFOUND_MODULE:
					throw new Yaf_Exception_LoadFailed_Module($message);
					break;
				case YAF_ERR_NOTFOUND_CONTROLLER:
					throw new Yaf_Exception_LoadFailed_Controller($message);
					break;
				case YAF_ERR_NOTFOUND_ACTION:
					throw new Yaf_Exception_LoadFailed_Action($message);
					break;
				case YAF_ERR_NOTFOUND_VIEW:
					throw new Yaf_Exception_LoadFailed_View($message);
					break;
				case YAF_ERR_CALL_FAILED:
					throw new Yaf_Exception($message, $code);
					break;
				case YAF_ERR_AUTOLOAD_FAILED:
					throw new Yaf_Exception_LoadFailed($message);
					break;
				case YAF_ERR_TYPE_ERROR:
					throw new Yaf_Exception_TypeError($message);
					break;
				default:
					throw new Yaf_Exception($message, $code);
					break;
			}
		} else {
			$this->getApplication()->setLastError($message, $code);
			trigger_error($message, E_USER_NOTICE);
		}
	}

}


/**
 * Yaf_Bootstrap_Abstract
 * 
 */
abstract class Yaf_Bootstrap_Abstract
{

}


/**
 * Yaf_Controller_Abstract
 * 
 */
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
			try {
				if (is_array($tpl_vars)) {
					$content = call_user_func(array($this->_view, 'render'), $tpl_file, $tpl_vars);
				} else {
					$content = call_user_func(array($this->_view, 'render'), $tpl_file);
				}

				if (!$content) {
					return null;
				}
			} catch (Exception $e) {
				return null;
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
			try {
				if (is_array($tpl_vars)) {
					$content = call_user_func(array($this->_view, 'render'), $tpl_file, $tpl_vars);
				} else {
					$result = call_user_func(array($this->_view, 'render'), $tpl_file);
				}

				if (!$content) {
					return false;
				}
			} catch (Exception $e) {
				return false;
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


/**
 * Yaf_Action_Abstract
 * 
 */
abstract class Yaf_Action_Abstract extends Yaf_Controller_Abstract
{
	protected $_controller;

	/**
	 * __construct
	 * 
	 * @param Yaf_Controller_Abstract $controller
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @param Yaf_View_Interface $view
	 * @param array $invoke_args
	 */
	public function __construct($controller, $request, $response, $view, $invoke_args = null)
	{
		if ($controller instanceof Yaf_Controller_Abstract) {
			parent::__construct($request, $response, $view, $invoke_args);
			$this->_name = get_class($controller);
			$this->_controller = $controller;
		}
		return false;
	}

	/**
	 * getController
	 *
	 * @param void
	 * @return Yaf_Controller_Abstract
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * execute
	 *
	 * @param void
	 */
	abstract public function execute();

}


/**
 * Yaf_Plugin_Abstract
 * 
 */
abstract class Yaf_Plugin_Abstract
{
	/**
	 * routerStartup
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function routerStartup($request, $response)
	{
		return true;
	}

	/**
	 * routerShutdown
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function routerShutdown($request, $response)
	{
		return true;
	}

	/**
	 * dispatchLoopStartup
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function dispatchLoopStartup($request, $response)
	{
		return true;
	}
	
	/**
	 * dispatchLoopShutdown
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function dispatchLoopShutdown($request, $response)
	{
		return true;
	}
	
	/**
	 * preDispatch
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function preDispatch($request, $response)
	{
		return true;
	}
	
	/**
	 * postDispatch
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function postDispatch($request, $response)
	{
		return true;
	}
	
	/**
	 * preResponse
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function preResponse($request, $response)
	{
		return true;
	}
	
}


/**
 * Yaf_Registry
 * 
 */
final class Yaf_Registry
{
	protected static $_instance;

	protected $_entries = array();

	/**
	 * __construct
	 * 
	 * @param void
	 */
	private function __construct()
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

	/**
	 * get
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public static function get($name)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries) && isset($registry->_entries[$name])) {
				return $registry->_entries[$name];
			}
		}
		
		return null;
	}

	/**
	 * has
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function has($name)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries)) {
				return isset($registry->_entries[$name]);
			}
		}

		return false;
	}

	/**
	 * set
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public static function set($name, $value)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries)) {
				$registry->_entries[$name] = $value;
				return true;
			}
		}

		return false;
	}

	/**
	 * del
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function del($name)
	{
		if ($name && is_string($name)) {
			$registry = self::_instance();
			if (is_array($registry->_entries)) {
				unset($registry->_entries[$name]);
				return true;
			}
		}

		return false;
	}

	/**
	 * yaf_registry_instance
	 * 
	 * @param void
	 */
	private static function _instance()
	{
		if (self::$_instance instanceof self) {
			return self::$_instance;
		}

		return self::$_instance = new self();
	}
}


/**
 * Yaf_Session
 * 
 */
final class Yaf_Session implements Iterator, ArrayAccess, Countable
{
	protected static $_instance;

	protected $_session;
	protected $_started = false;

	/**
	 * __construct
	 * 
	 * @param void
	 */
	private function __construct()
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
	 * getInstance
	 * 
	 * @param void
	 * @return Yaf_Session
	 */
	public static function getInstance()
	{
		if (self::$_instance instanceof self) {
			return self::$_instance;
		}

		self::$_instance = new self();
		self::$_instance->start();
		if (!isset($_SESSION) || !is_array($_SESSION)) {
			trigger_error('Attempt to start session failed', E_USER_WARNING);
			unset(self::$_instance);
			return null;
		}
		self::$_instance->_session = &$_SESSION;
		return self::$_instance;
	}

	/**
	 * start
	 * 
	 * @param void
	 * @return Yaf_Session
	 */
	public function start()
	{
		if (!$this->_started) {
			session_start();
			$this->_started = true;
		}
		return $this;
	}

	/**
	 * get
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function get($name = null)
	{
		if (is_null($name)) {
			return $this->_session;
		} elseif(isset($this->_session[$name])) {
			return $this->_session[$name];
		} else {
			return null;
		}
	}

	/**
	 * has
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function has($name)
	{
		return isset($this->_session[$name]);
	}

	/**
	 * set
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return boolean | Yaf_Session
	 */
	public function set($name, $value)
	{
		if ($name && is_string($name)) {
			$this->_session[$name] = $value;
			return $this;
		}
		return false;
	}

	/**
	 * del
	 * 
	 * @param string $name
	 * @return boolean | Yaf_Session
	 */
	public function del($name)
	{
		if ($name && is_string($name)) {
			unset($this->_session[$name]);
			return $this;
		}
		return false;
	}

	/**
	 * Countable::count
	 * 
	 * @param void
	 * @return integer
	 */
	public function count()
	{
		return count($this->_session);
	}

	/**
	 * Iterator::rewind
	 *
	 * @param void
	 * @return void
	 */
	public function rewind()
	{
		reset($this->_session);
	}

	/**
	 * Iterator::next
	 *
	 * @param void
	 * @return void
	 */
	public function next()
	{
		next($this->_session);
	}

	/**
	 * Iterator::current
	 *
	 * @param void
	 * @return mixed
	 */
	public function current()
	{
		return current($this->_session);
	}

	/**
	 * Iterator::key
	 *
	 * @param void
	 * @return string
	 */
	public function key()
	{
 		return key($this->_session);
	}

	/**
	 * Iterator::valid
	 *
	 * @param void
	 * @return boolean
	 */
	public function valid()
	{
		return (current($this->_session) !== false);
	}

	/**
	 * ArrayAccess:: offsetGet
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * ArrayAccess:: offsetSet
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function offsetSet($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * ArrayAccess::offsetExists
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetExists($name)
	{
		return $this->has($name);
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetUnset($name)
	{
		return $this->del($name);
	}

	/**
	 * __get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * __isset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		return $this->has($name);
	}

	/**
	 * __set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * __unset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __unset($name)
	{
		return $this->del($name);
	}

}


/**
 * Yaf_Router
 * 
 */
final class Yaf_Router
{
	protected $_routes;
	protected $_current;

	/**
	 * __construct
	 *
	 * @param void
	 */
	public function __construct()
	{
		$this->_routes = array();

		if (!YAF_G('default_route')) {
		    $this->_routes['_default'] = new Yaf_Route_Static();
		} elseif (!($this->_routes['_default'] = $this->_route_instance())) {
			trigger_error('Unable to initialize default route, use Yaf_Route_Static instead', E_USER_WARNING);
			$this->_routes['_default']= new Yaf_Route_Static();
		}

	}

	/**
	 * addRoute
	 *
	 * @param string $name
	 * @param Yaf_Route_Interface $route
	 * @return boolean | Yaf_Router
	 */
	public function addRoute($name, $route)
	{
		if (empty($name) || !is_string($name)) {
			return false;
		}

		if (!is_object($route) || !($route instanceof Yaf_Route_Interface)) {
			trigger_error('Expects a Yaf_Route_Interface instance', E_USER_WARNING);
			return false;
		}

		$this->_routes[$name] = $route;

		return $this;
	}

	/**
	 * addConfig
	 *
	 * @param Yaf_Config_Abstract $config
	 * @return boolean | Yaf_Router
	 */
	public function addConfig($config)
	{
		if (is_object($config) && ($config instanceof Yaf_Config_Abstract)) {
			$routes = $config->toArray();
		} elseif(is_array($config)) {
			$routes = $config;
		} else {
			trigger_error('Expect a Yaf_Config_Abstract instance or an array, ' . gettype($config) . ' given', E_USER_WARNING);
			return false;
		}

		foreach ($routes as $key => $value) {
			if (empty($value) || !is_array($value)) {
				continue;
			}

			if ($route = $this->_route_instance($value)) {
				$this->_routes[$key] = $route;
			} else {
				if (is_numeric($key)) {
					trigger_error('Unable to initialize route named \'' . $key . '\'', E_USER_WARNING);
				} else {
					trigger_error('Unable to initialize route at index' . $key, E_USER_WARNING);
				}
				continue;
			}
		}

		return $this;
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */	
	public function route($request)
	{
		foreach ($this->_routes as $key => $value) {
			if (call_user_func(array($value, 'route'), $request) === true) {
				$this->_current = $key;
				$request->setRouted();
				return true;
			}
		}

		return false;
	}

	/**
	 * getRoute
	 *
	 * @param string $name
	 * @return boolean | Yaf_Router
	 */
	public function getRoute($name)
	{
		if (empty($name)) {
			return false;
		}

		if (isset($this->_routes[$name])) {
			return $this->_routes[$name];
		}

		return null;
	}
		
	/**
	 * getRoutes
	 *
	 * @param void
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->_routes;
	}

	/**
	 * getCurrentRoute
	 *
	 * @param void
	 * @return integer | string
	 */
	public function getCurrentRoute()
	{
		return $this->_current;
	}

	/**
	 * yaf_route_instance
	 *
	 * @param array $config
	 * @return Yaf_Route_Interface
	 */
	private function _route_instance($config = null)
	{
		if (is_null($config)) {
			$config = YAF_G('default_route');
		}

		if (!$config || !is_array($config)) {
			return null;
		}

		if (empty($config['type']) || !is_string($config['type'])) {
			return null;
		}

		if (strtolower($config['type']) == 'rewrite') {
			if (!isset($config['match']) || !is_string($config['match'])) {
				return null;
			}
			if (!isset($config['route']) || !is_array($config['route'])) {
				return null;
			}

			return new Yaf_Route_Rewrite($config['match'], $config['route']);
		} elseif (strtolower($config['type']) == 'regex') {
			if (!isset($config['match']) || !is_string($config['match'])) {
				return null;
			}
			if (!isset($config['route']) || !is_array($config['route'])) {
				return null;
			}
			if (!isset($config['map']) || !is_array($config['map'])) {
				return null;
			}

			return new Yaf_Route_Regex($config['match'], $config['route'], $config['map']);
		} elseif (strtolower($config['type']) == 'map') {
			$delimiter = null;
			$controller_prefer = false;
			
			if (isset($config['controllerPrefer'])) {
				$controller_prefer = (boolean)$config['controllerPrefer'];
			}

			if (isset($config['delimiter']) && is_string($config['delimiter'])) {
				$delimiter = $config['delimiter'];
			}

			return new Yaf_Route_Map($controller_prefer, $delimiter);
		} elseif (strtolower($config['type']) == 'simple') {
			if (empty($config['module']) || !is_string($config['module'])) {
				return null;
			}
			if (empty($config['controller']) || !is_string($config['controller'])) {
				return null;
			}
			if (empty($config['action']) || !is_string($config['action'])) {
				return null;
			}

			return new Yaf_Route_Simple($config['module'], $config['controller'], $config['action']);
		} elseif (strtolower($config['type']) == 'supervar') {
			if (empty($config['varname']) || !is_string($config['varname'])) {
				return null;
			}

			return new Yaf_Route_Supervar($config['varname']);
		}
	}
	
}


/**
 * Yaf_Route_Interface
 * 
 */
interface Yaf_Route_Interface
{
	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 */
	public function route($request);
}


/**
 * Yaf_Route_Static
 * 
 */
final class Yaf_Route_Static implements Yaf_Route_Interface
{
	/**
	 * match
	 *
	 * @param string $uri
	 * @return boolean
	 */
	public function match($uri)
	{
		return true;
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		if (is_object($request) && ($request instanceof Yaf_Request_Abstract)) {
			$base_uri = $request->getBaseUri();
			$request_uri = $request->getRequestUri();

			if ($base_uri && is_string($base_uri)
					&& !strncasecmp($request_uri, $base_uri, strlen($base_uri))) {
				$request_uri = substr($request_uri, strlen($base_uri));
			}

			return $this->_pathinfo_route($request, $request_uri);
		}

		return false;
	}

	/**
	 * yaf_route_pathinfo_route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @param string $request_uri
	 * @return boolean
	 */
	private function _pathinfo_route($request, $request_uri)
	{
		$module = $controller = $action = $reset = null;

		do {

			if (empty($request_uri) || $request_uri == '/') {
				break;
			}

			$request_uri = trim($request_uri, ' /');

			$token_len = 0;
			if ($token = strtok($request_uri, '/')) {
				if ($this->_is_module_name($token)) {
					$module = $token;
					if ($token = strtok('/')) {
						$controller = trim($token);
						$token_len += strlen($token) + 1;
					}
				} else {
					$controller = $token;
				}
				$token_len += strlen($token) + 1;
			}

			if ($token = strtok('/')) {
				$action = trim($token);
				$token_len += strlen($token) + 1;
			}

			if ($token = strtok('/')) {
				do {
					if (!$module && !$controller && !$action) {
						if ($this->_is_module_name($token)) {
							$module = $token;
							break;
						}
					}

					if (!$controller) {
						$controller = $token;
						break;
					}

					if (!$action) {
						$action = $token;
						break;
					}

					$reset = substr($request_uri, $token_len);
				} while (0);
			}

			if ($module && is_null($controller)) {
				$controller = $module;
				$module = null;
			} elseif ($module && is_null($action)) {
				$action = $controller;
				$controller = $module;
				$module = null;
		    } elseif ($controller && is_null($action)) {
				/* /controller */
				if (YAF_G('action_prefer')) {
					$action = $controller;
					$controller = null;
				}
			}

		} while (0);

		if (!is_null($module)) {
			$request->setModuleName($module);
		}

		if (!is_null($controller)) {
			$request->setControllerName($controller);
		}

		if (!is_null($action)) {
			$request->setActionName($action);
		}

		if ($reset) {
			$params = $this->_parse_parameters($reset);
			$request->setParam($params);
		}

		return true;
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
			$modules = Yaf_Application::app()->getModules();
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
	 * yaf_router_parse_parameters
	 *
	 * @param string $uri
	 * @return array
	 */
	private function _parse_parameters($uri)
	{
		$params = array();

		$key = strtok($uri, '/');
		while ($key) {
			$params[$key] = strtok('/');
			$key = strtok('/');
		}
		return $params;
	}
	
}


/**
 * Yaf_Route_Simple
 * 
 */
final class Yaf_Route_Simple implements Yaf_Route_Interface
{
	protected $module;
	protected $controller;
	protected $action;

	/**
	 * __construct
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 */
	public function __construct($module, $controller, $action)
	{
		if (is_string($module) && is_string($controller) && is_string($action)) {
			$this->module = $module;
			$this->controller = $controller;
			$this->action = $action;
		} else {
			trigger_error('Expect 3 string paramsters', E_USER_ERROR);
		}
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		if (is_object($request) && ($request instanceof Yaf_Request_Abstract)) {
			$module = $request->getQuery($this->module);
			$controller = $request->getQuery($this->controller);
			$action = $request->getQuery($this->action);

			if (is_null($module) && is_null($controller) && is_null($action)) {
				return false;
			}

			if ($module && $this->_is_module_name($module)) {
				$request->setModuleName($module);
			}

			$request->setControllerName((string)$controller);
			$request->setActionName((string)$action);

			return true;
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
			$modules = Yaf_Application::app()->getModules();
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


/**
 * Yaf_Route_Rewrite
 * 
 */
final class Yaf_Route_Rewrite implements Yaf_Route_Interface
{
	protected $_match;
	protected $_route;
	protected $_verify;

	/**
	 * __construct
	 *
	 * @param string $match
	 * @param array $route
	 * @param array $verify
	 */
	public function __construct($match, $route, $verify = null)
	{
		if (empty($match) || !is_string($match)) {
			unset($this);
			trigger_error('Expects a valid string as the first parameter', E_USER_ERROR);
			return false;
		}

		if ($route && !is_array($route)) {
			unset($this);
			trigger_error('Expects an array as the second parameter', E_USER_ERROR);
			return false;
		}

		if ($verify && !is_array($verify)) {
			unset($this);
			trigger_error('Expects an array as the third parmater', E_USER_ERROR);
			return false;
		}

		$this->_match = $match;
		$this->_route = $route;

		if (is_array($verify)) {
			$this->_verify = $verify;
		}
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		if (is_object($request) && ($request instanceof Yaf_Request_Abstract)) {
			$base_uri = $request->getBaseUri();
			$request_uri = $request->getRequestUri();

			if ($base_uri && is_string($base_uri)
					&& !strncasecmp($request_uri, $base_uri, strlen($base_uri))) {
				$request_uri = substr($request_uri, strlen($base_uri));
			}

			if ($args = $this->_rewrite_match($request_uri)) {
				if (isset($this->_route['module'])) {
					$request->setModuleName($this->_route['module']);
				}

				if (isset($this->_route['controller'])) {
					$request->setControllerName($this->_route['controller']);
				}

				if (isset($this->_route['action'])) {
					$request->setActionName($this->_route['action']);
				}

				$request->setParam($args);

				return true;
			}

			return false;
		}

		trigger_error('Expect a Yaf_Request_Abstract instance', E_USER_WARNING);
		return false;
	}

	/**
	 * yaf_route_rewrite_match
	 *
	 * @param string $request_uri
	 * @return boolean
	 */
	private function _rewrite_match($request_uri)
	{
		if (empty($request_uri)) {
			return null;
		}

		$pattern = '#^';
		$seg = strtok($this->_match, '/');
		while ($seg) {
			if ($seg) {
				$pattern .= '/';

				if($seg[0] == '*') {
					$pattern .= '(?P<__yaf_route_rest>.*)';
					break;
				}

				if($seg[0] == ':') {
					$pattern .= '(?P<' . substr($seg, 1) . '>[^/]+)';
				} else {
					$pattern .= $seg;
				}

			}
			$seg = strtok('/');
		}
		$pattern .= '#i';

		if (!preg_match($pattern, $request_uri, $matches)) {
			return null;
		}

		$ret = array();
		foreach ($matches as $key => $value) {
			if (!is_string($key)) {
				continue;
			}

			if ($key == '__yaf_route_rest') {
				$ret = array_merge($ret, $this->_parse_parameters($value));
			} else {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}

	/**
	 * yaf_router_parse_parameters
	 *
	 * @param string $uri
	 * @return array
	 */
	private function _parse_parameters($uri)
	{
		$params = array();

		$key = strtok($uri, '/');
		while ($key) {
			$params[$key] = strtok('/');
			$key = strtok('/');
		}
		return $params;
	}
	
}


/**
 * Yaf_Route_Regex
 * 
 */
final class Yaf_Route_Regex implements Yaf_Route_Interface
{
	protected $_match;
	protected $_route;
	protected $_maps;
	protected $_verify;
	
	/**
	 * __construct
	 *
	 * @param string $match
	 * @param array $route
	 * @param array $maps
	 * @param array $verify
	 */
	public function __construct($match, $route, $maps = null, $verify = null)
	{
		if (empty($match) || !is_string($match)) {
			unset($this);
			trigger_error('Expects a valid string as the first parameter', E_USER_ERROR);
			return false;
		}

		if ($route && !is_array($route)) {
			unset($this);
			trigger_error('Expects an array as the second parameter', E_USER_ERROR);
			return false;
		}

		if ($maps && !is_array($maps)) {
			unset($this);
			trigger_error('Expects an array as the third parmater', E_USER_ERROR);
			return false;
		}

		if ($verify && !is_array($verify)) {
			unset($this);
			trigger_error('Expects an array as verify parmater', E_USER_ERROR);
			return false;
		}

		$this->_match = $match;
		$this->_route = $route;
		$this->_maps = $maps;

		if (is_array($verify)) {
			$this->_verify = $verify;
		}
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		if (is_object($request) && ($request instanceof Yaf_Request_Abstract)) {
			$base_uri = $request->getBaseUri();
			$request_uri = $request->getRequestUri();

			if ($base_uri && is_string($base_uri)
					&& !strncasecmp($request_uri, $base_uri, strlen($base_uri))) {
				$request_uri = substr($request_uri, strlen($base_uri));
			}

			if ($args = $this->_regex_match($request_uri)) {
				if (isset($this->_route['module'])) {
					$request->setModuleName($this->_route['module']);
				}

				if (isset($this->_route['controller'])) {
					$request->setControllerName($this->_route['controller']);
				}

				if (isset($this->_route['action'])) {
					$request->setActionName($this->_route['action']);
				}

				$request->setParam($args);

				return true;
			}

			return false;
		}

		trigger_error('Expect a Yaf_Request_Abstract instance', E_USER_WARNING);
		return false;
	}

	/**
	 * yaf_route_regex_match
	 *
	 * @param string $request_uri
	 * @return boolean
	 */
	private function _regex_match($request_uri)
	{
		if (empty($request_uri)) {
			return null;
		}

		if (!preg_match($this->_match, $request_uri, $matches)) {
			return null;
		}

		$ret = array();
		foreach ($matches as $key => $value) {
			if (is_numeric($key)) {
				if (isset($this->_maps[$key])) {
					$ret[$this->_maps[$key]] = $value;
				}
			}elseif (is_string($key)) {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}
}


/**
 * Yaf_Route_Map
 * 
 */
final class Yaf_Route_Map implements Yaf_Route_Interface
{
	protected $_ctl_router = false;
	protected $_delimeter;

	/**
	 * __construct
	 *
	 * @param string $ctl_router
	 * @param string $delimeter
	 */
	public function __construct($ctl_router = false, $delimeter = '#!')
	{
		if ($ctl_router) {
			$this->_ctl_router = true;
		}

		if ($delimeter && is_string($delimeter)) {
			$this->_delimeter = $delimeter;
		}
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		if (is_object($request) && ($request instanceof Yaf_Request_Abstract)) {
			$query_str = null;

			$base_uri = $request->getBaseUri();
			$request_uri = $request->getRequestUri();

			if ($base_uri && is_string($base_uri)
					&& !strncasecmp($request_uri, $base_uri, strlen($base_uri))) {
				$request_uri = substr($request_uri, strlen($base_uri));
			}

			$request_uri = trim($request_uri, '/');
			if ($this->_delimeter && is_string($this->_delimeter)) {
				if ($query_str = strstr($request_uri, $this->_delimeter)) {
					$request_uri = substr($request_uri, 0, - strlen($query_str));
					$query_str = substr($query_str, strlen($this->_delimeter));
				}
			}

			$route_result = str_replace('/', '_', $request_uri);

			if ($route_result) {
				if ($this->_ctl_router) {
					$request->setControllerName($route_result);
				} else {
					$request->setActionName($route_result);
				}
			}
			
			if ($query_str) {
				$params = $this->_parse_parameters($query_str);
				$request->setParam($params);
			}
		}

		return false;
	}

	/**
	 * yaf_router_parse_parameters
	 *
	 * @param string $uri
	 * @return array
	 */
	private function _parse_parameters($uri)
	{
		$params = array();

		$key = strtok($uri, '/');
		while ($key) {
			$params[$key] = strtok('/');
			$key = strtok('/');
		}
		return $params;
	}

}


/**
 * Yaf_Route_Supervar
 * 
 */
final class Yaf_Route_Supervar implements Yaf_Route_Interface
{
	protected $_var_name;

	/**
	 * __construct
	 *
	 * @param string $varname
	 */
	public function __construct($varname)
	{
		if ($varname && is_string($varname)) {
			$this->_var_name = $varname;
		} else {
			trigger_error('Expects a valid string super var name', E_USER_ERROR);
		}
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		$request_uri = $request->getQuery($this->_var_name);

		if (is_null($request_uri)) {
			return false;
		}

		return $this->_pathinfo_route($request, $request_uri);
	}

	/**
	 * yaf_route_pathinfo_route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @param string $request_uri
	 * @return boolean
	 */
	private function _pathinfo_route($request, $request_uri)
	{
		if (is_object($request) && ($request instanceof Yaf_Request_Abstract)) {

			$module = $controller = $action = $reset = null;

			do {

				if (empty($request_uri) || $request_uri == '/') {
					break;
				}

				$request_uri = trim($request_uri, ' /');

				$token_len = 0;
				if ($token = strtok($request_uri, '/')) {
					if ($this->_is_module_name($token)) {
						$module = $token;
						if ($token = strtok('/')) {
							$controller = trim($token);
							$token_len += strlen($token) + 1;
						}
					} else {
						$controller = $token;
					}
					$token_len += strlen($token) + 1;
				}

				if ($token = strtok('/')) {
					$action = trim($token);
					$token_len += strlen($token) + 1;
				}

				if ($token = strtok('/')) {
					do {
						if (!$module && !$controller && !$action) {
							if ($this->_is_module_name($token)) {
								$module = $token;
								break;
							}
						}

						if (!$controller) {
							$controller = $token;
							break;
						}

						if (!$action) {
							$action = $token;
							break;
						}

						$reset = substr($request_uri, $token_len);
					} while (0);
				}

				if ($module && is_null($controller)) {
					$controller = $module;
					$module = null;
				} elseif ($module && is_null($action)) {
					$action = $controller;
					$controller = $module;
					$module = null;
			    } elseif ($controller && is_null($action)) {
					/* /controller */
					if (YAF_G('action_prefer')) {
						$action = $controller;
						$controller = null;
					}
				}

			} while (0);

			if (!is_null($module)) {
				$request->setModuleName($module);
			}

			if (!is_null($controller)) {
				$request->setControllerName($controller);
			}

			if (!is_null($action)) {
				$request->setActionName($action);
			}

			if ($reset) {
				$params = $this->_parse_parameters($reset);
				$request->setParam($params);
			}

			return true;
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
			$modules = Yaf_Application::app()->getModules();
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
	 * yaf_router_parse_parameters
	 *
	 * @param string $uri
	 * @return array
	 */
	private function _parse_parameters($uri)
	{
		$params = array();

		$key = strtok($uri, '/');
		while ($key) {
			$params[$key] = strtok('/');
			$key = strtok('/');
		}
		return $params;
	}

}


/**
 * Yaf_Config_Abstract
 * 
 */
abstract class Yaf_Config_Abstract implements Iterator, ArrayAccess, Countable
{
	protected $_config = array();
	protected $_readonly = true;

	/**
	 * get
	 *
	 * @param string $name
	 */
	abstract public function get($name = null);

	/**
	 * set
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	abstract public function set($name, $value);

	/**
	 * toArray
	 *
	 * @param void
	 */
	abstract public function toArray();

	/**
	 * readOnly
	 *
	 * @param void
	 */
	abstract public function readOnly();

}


/**
 * Yaf_Config_Ini
 * 
 */
final class Yaf_Config_Ini extends Yaf_Config_Abstract
{
	/**
	 * __construct
	 *
	 * @param mixed $config
	 * @param string $section
	 */
	public function __construct($config, $section = null)
	{
		if (is_array($config)) {
			$this->_config = $config;
		} elseif (is_string($config)) {
			if (file_exists($config)) {
				if (is_file($config)) {
					$this->_config = self::_parser_cb($config, $section);
					if ($this->_config == false || !is_array($this->_config)) {
						trigger_error('Parsing ini file '. $config .' failed', E_USER_ERROR);
						return false;
					}
				} else {
					trigger_error('Argument is not a valid ini file '. $config, E_USER_ERROR);
					return false;
				}
			} else {
				trigger_error('Unable to find config file '. $config, E_USER_ERROR);
				return false;
			}
		} else {
			trigger_error('Invalid parameters provided, must be path of ini file', E_USER_ERROR);
			return false;
		}
	}
	
	/**
	 * __isset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		return isset($this->_config[$name]);
	}

	/**
	 * get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name = null)
	{
		if (is_null($name)) return $this;
		
		if ($seg = strtok($name, '.')) {
			$value = $this->_config;
			while ($seg) {
				if (!isset($value[$seg])) return;
				$value = $value[$seg];
				$seg = strtok('.');
			}
			if (is_array($value)) {
				return new self($value);
			} else {
				return $value;
			}
		}
	}

	/**
	 * set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function set($name, $value)
	{
		return false;
	}

	/**
	 * Countable::count
	 *
	 * @param void
	 * @return integer
	 */
	public function count()
	{
		return count($this->_config);
	}

	/**
	 * Iterator::rewind
	 *
	 * @param void
	 * @return void
	 */
	public function rewind()
	{
		reset($this->_config);
	}

	/**
	 * Iterator::current
	 *
	 * @param void
	 * @return mixed
	 */
	public function current()
	{
		$value = current($this->_config);
		if (is_array($value)) {
			return new self($value);
		} else {
			return $value;
		}
	}

	/**
	 * Iterator::next
	 *
	 * @param void
	 * @return void
	 */
	public function next()
	{
		next($this->_config);
	}

	/**
	 * Iterator::valid
	 *
	 * @param void
	 * @return boolean
	 */
	public function valid()
	{
		return (current($this->_config) !== false);
	}

	/**
	 * Iterator::key
	 *
	 * @param void
	 * @return string
	 */
	public function key()
	{
 		return key($this->_config);
	}

	/**
	 * toArray
	 *
	 * @param void
	 * @return array
	 */
	public function toArray()
	{
		return $this->_config;
	}

	/**
	 * readOnly
	 *
	 * @param void
	 * @return boolean
	 */
	public function readOnly()
	{
		return true;
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetUnset($name)
	{
		return false;
	}

	/**
	 * ArrayAccess:: offsetGet
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * ArrayAccess::offsetExists
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetExists($name)
	{
		return $this->__isset($name);
	}
	
	/**
	 * ArrayAccess:: offsetSet
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function offsetSet($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * __get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * __set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * yaf_config_ini_parser_cb
	 *
	 * @param string $filepath
	 * @param string $section
	 * @return array | boolean
	 */
	private static function _parser_cb($filepath, $section){
		$config = parse_ini_file($filepath, true);
		if ($config && is_array($config)) {
			foreach ($config as $key => $value) {
				if($seg = ltrim(strchr($key, ':'), ': ')){
					while ($token = ltrim(strrchr($seg, ':'), ': ')) {
						if (isset($config[$token])) {
							$value = array_merge($config[$token], $value);
						}
						$seg = substr($seg, 0, -strlen($token));
						$seg = rtrim($seg, ': ');
					}

					$token = rtrim($seg, ': ');
					if (isset($config[$token])) {
						$value = array_merge($config[$token], $value);
					}

					unset($config[$key]);

					if ($key = trim(strtok($key, ':'))) {
						$config[$key] = $value;
					}
				}

				if (is_string($section) && ($key == $section)) {
					return self::_simple_parser_cb($value);
				}
			}

			return self::_simple_parser_cb($config);
		}

		return false;
	}

	/**
	 * yaf_config_ini_simple_parser_cb
	 *
	 * @param array $simple
	 * @return array
	 */
	private static function _simple_parser_cb($simple){
		if(!is_array($simple)) return;
		
		foreach ($simple as $key => $value) {
			if ($seg = strtok($key, '.')) {
				if ($subkey = ltrim(strchr($key, '.'), '.')) {
					$value = array($subkey => $value);
					if (isset($simple[$seg]) && is_array($simple[$seg])) {
						$value = array_merge($simple[$seg], $value);
					}
					$simple[$seg] = self::_simple_parser_cb($value);
					unset($simple[$key]);
				} elseif(is_array($value)) {
					$simple[$key] = self::_simple_parser_cb($value);
				}
			}
		}

		return $simple;
	}

}


/**
 * Yaf_Config_Simple
 * 
 */
final class Yaf_Config_Simple extends Yaf_Config_Abstract
{
	/**
	 * __construct
	 *
	 * @param array $config
	 * @param boolean $readonly
	 */
	public function __construct($config, $readonly = null)
	{
		if (is_array($config)) {
			$this->_config = $config;
			if (!is_null($readonly)) {
				$this->_readonly = (boolean) $readonly;
			}
		} else {
			trigger_error('Invalid parameters provided, must be an array', E_USER_ERROR);
			return false;
		}
	}

	/**
	 * __isset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		return isset($this->_config[$name]);
	}

	/**
	 * get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name = null)
	{
		if (is_null($name)) return $this;

		if (isset($this->_config[$name])) {
			$value = $this->_config[$name];
			if (is_array($value)) {
				return new self($value);
			} else {
				return $value;
			}
		}
	}

	/**
	 * set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function set($name, $value)
	{
		if ($this->_readonly) return false;

		if (is_string($name)) {
			$this->_config[$name] = $value;
			return true;
		}

		trigger_error('Expect a string key name', E_USER_WARNING);
		return false;
	}

	/**
	 * Countable::count
	 *
	 * @param void
	 * @return integer
	 */
	public function count()
	{
		return count($this->_config);
	}

	/**
	 * Iterator::rewind
	 *
	 * @param void
	 * @return void
	 */
	public function rewind()
	{
		reset($this->_config);
	}

	/**
	 * Iterator::current
	 *
	 * @param void
	 * @return mixed
	 */
	public function current()
	{
		$value = current($this->_config);
		if (is_array($value)) {
			return new self($value);
		} else {
			return $value;
		}
	}

	/**
	 * Iterator::next
	 *
	 * @param void
	 * @return void
	 */
	public function next()
	{
		next($this->_config);
	}

	/**
	 * Iterator::valid
	 *
	 * @param void
	 * @return boolean
	 */
	public function valid()
	{
		return (current($this->_config) !== false);
	}

	/**
	 * Iterator::key
	 *
	 * @param void
	 * @return string
	 */
	public function key()
	{
 		return key($this->_config);
	}

	/**
	 * toArray
	 *
	 * @param void
	 * @return array
	 */
	public function toArray()
	{
		return $this->_config;
	}

	/**
	 * readOnly
	 *
	 * @param void
	 * @return boolean
	 */
	public function readOnly()
	{
		return $this->_readonly;
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetUnset($name)
	{
		if ($this->_readonly) return false;
		if (is_string($name)) {
			unset($this->_config[$name]);
			return true;
		}

		trigger_error('Expect a string key name', E_USER_WARNING);
		return false;
	}

	/**
	 * ArrayAccess:: offsetGet
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * ArrayAccess::offsetExists
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function offsetExists($name)
	{
		return $this->__isset($name);
	}
	
	/**
	 * ArrayAccess:: offsetSet
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function offsetSet($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * __get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * __set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

}


/**
 * Yaf_Request_Abstract
 * 
 */
abstract class Yaf_Request_Abstract
{
	public $module;
	public $controller;
	public $action;
	public $method;

	protected $params;
	protected $language;
	protected $_exception;
	protected $_base_uri = '';
	protected $uri = '';
	protected $dispatched = false;
	protected $routed = false;

	/**
	 * isGet
	 *
	 * @param void
	 * @return boolean
	 */
	public function isGet()
	{
		return (strtoupper($this->method) == 'GET');
	}
	
	/**
	 * isPost
	 *
	 * @param void
	 * @return boolean
	 */
	public function isPost()
	{
		return (strtoupper($this->method) == 'POST');
	}
	
	/**
	 * isPut
	 *
	 * @param void
	 * @return boolean
	 */
	public function isPut()
	{
		return (strtoupper($this->method) == 'PUT');
	}
	
	/**
	 * isHead
	 *
	 * @param void
	 * @return boolean
	 */
	public function isHead()
	{
		return (strtoupper($this->method) == 'HEAD');
	}
	
	/**
	 * isOptions
	 *
	 * @param void
	 * @return boolean
	 */
	public function isOptions()
	{
		return (strtoupper($this->method) == 'OPTIONS');
	}
	
	/**
	 * isCli
	 *
	 * @param void
	 * @return boolean
	 */
	public function isCli()
	{
		(strtoupper($this->method) == 'CLI');
	}

	/**
	 * isXmlHttpRequest
	 *
	 * @param void
	 * @return boolean
	 */
	public function isXmlHttpRequest()
	{
		return false;
	}

	/**
	 * getServer
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getServer($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_SERVER;
		} elseif (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}
		return $default;
	}
	
	/**
	 * getEnv
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getEnv($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_ENV;
		} elseif (isset($_ENV[$name])) {
			return $_ENV[$name];
		}
		return $default;
	}

	/**
	 * setParam
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setParam($name, $value = null)
	{
		if (is_null($value)) {
			if (is_array($name)) {
				$this->params = array_merge($this->params, $name);
				return $this;
			}
		} elseif(is_string($name)) {
			$this->params[$name] = $value;
			return $this;
		}
		return false;
	}
	
	/**
	 * getParam
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($name, $dafault = null)
	{
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		return $dafault;
	}
	
	/**
	 * setParams
	 *
	 * @param array
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setParams($params)
	{
		if (is_array($params)) {
			$this->params = $params;
			return $this;
		}
		return false;
	}
	
	/**
	 * getParams
	 *
	 * @param void
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}
	
	/**
	 * setException
	 *
	 * @param Exception $exception
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setException($exception)
	{
		if (is_object($exception)
				&& ($exception instanceof Exception)) {
			$this->_exception = $exception;
			return $this;
		}
		return false;
	}

	/**
	 * getException
	 *
	 * @param void
	 * @return Exception
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
	 * getModuleName
	 *
	 * @param void
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->module;
	}
	
	/**
	 * getControllerName
	 *
	 * @param void
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->controller;
	}
	
	/**
	 * getActionName
	 *
	 * @param void
	 * @return string
	 */
	public function getActionName()
	{
		return $this->action;
	}
	
	/**
	 * setModuleName
	 *
	 * @param string $name
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setModuleName($name)
	{
		if (!is_string($name)) {
			trigger_error('Expect a string module name', E_USER_WARNING);
			return false;
		}
		$this->module = $name;
		return $this;
	}
	
	/**
	 * setControllerName
	 *
	 * @param string $name
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setControllerName($name)
	{
		if (!is_string($name)) {
			trigger_error('Expect a string controller name', E_USER_WARNING);
			return false;
		}
		$this->controller = $name;
		return $this;
	}
	
	/**
	 * setActionName
	 *
	 * @param string $name
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setActionName($name)
	{
		if (!is_string($name)) {
			trigger_error('Expect a string action name', E_USER_WARNING);
			return false;
		}
		$this->action = $name;
		return $this;
	}

	/**
	 * getMethod
	 *
	 * @param void
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * getLanguage
	 *
	 * @param void
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * setBaseUri
	 *
	 * @param string $base_uri
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setBaseUri($base_uri)
	{
		if ($base_uri && is_string($base_uri)) {
			$this->_base_uri = $base_uri;
			return $this;
		}
		return false;
	}

	/**
	 * getBaseUri
	 *
	 * @param void
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->_base_uri;
	}

	/**
	 * setRequestUri
	 *
	 * @param string $uri
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setRequestUri($uri)
	{
		if (is_string($uri)) {
			$this->uri = $uri;
			return $this;
		}
		return false;
	}
	
	/**
	 * getRequestUri
	 *
	 * @param void
	 * @return string
	 */
	public function getRequestUri()
	{
		return $this->uri;
	}

	/**
	 * isDispatched
	 *
	 * @param void
	 * @return boolean
	 */
	public function isDispatched()
	{
		return (boolean) $this->dispatched;
	}
	
	/**
	 * setDispatched
	 *
	 * @param boolean $flag
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setDispatched($flag = true)
	{
		if (is_bool($flag)) {
			$this->dispatched = $flag;
			return $this;
		}
		return false;
	}
	
	/**
	 * isRouted
	 *
	 * @param void
	 * @return boolean
	 */
	public function isRouted()
	{
		return $this->routed;
	}
	
	/**
	 * setRouted
	 *
	 * @param boolean $flag
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setRouted($flag = true)
	{
		if (is_bool($flag)) {
			$this->routed = $flag;
			return $this;
		}
		return false;
	}

	/**
	 * yaf_request_set_base_uri
	 *
	 * @param string $base_uri
	 * @param string $request_uri
	 * @return boolean
	 */
	protected function _set_base_uri($base_uri, $request_uri = null)
	{
		if ($base_uri && is_string($base_uri)) {
			$this->_base_uri = $base_uri;

			return true;
		} elseif($request_uri && is_string($request_uri)) {
			$script_filename = $this->getServer('SCRIPT_FILENAME');

			do {
				if ($script_filename && is_string($script_filename)) {
					$file_name = basename($script_filename, YAF_G('ext'));
					$file_name_len = strlen($file_name);

					$script_name = $this->getServer('SCRIPT_NAME');
					if ($script_name && is_string($script_name)) {
						$script = basename($script_name);

						if (strncmp($file_name, $script, $file_name_len) == 0) {
							$basename = $script_name;
							break;
						}
					}

					$phpself_name = $this->getServer('PHP_SELF');
					if ($phpself_name && is_string($phpself_name)) {
						$phpself = basename($phpself_name);
						if (strncmp($file_name, $phpself, $file_name_len) == 0) {
							$basename = $phpself_name;
							break;
						}
					}

					$orig_name = $this->getServer('ORIG_SCRIPT_NAME');
					if ($orig_name && is_string($orig_name)) {
						$orig = basename($orig_name);
						if (strncmp($file_name, $orig, $file_name_len) == 0) {
							$basename 	 = $orig_name;
							break;
						}
					}
				}
			} while (0);

			if ($basename && strstr($request_uri, $basename) == $request_uri) {
				$this->_base_uri = rtrim($basename, '/');

				return true;
			} elseif ($basename) {
				$dirname = rtrim(dirname($basename), '/');
				if ($dirname) {
					if (strstr($request_uri, $dirname) == $request_uri) {
						$this->_base_uri = $dirname;

						return true;
					}
				}
			}

			$this->_base_uri = '';

			return true;
		}

		return false;
	}

}


/**
 * Yaf_Request_Http
 * 
 */
final class Yaf_Request_Http extends Yaf_Request_Abstract
{
	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	/**
	 * __construct
	 *
	 * @param string $request_uri
	 * @param string $base_uri
	 */
	public function __construct($request_uri = null, $base_uri = null)
	{
		if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->method = $_SERVER['REQUEST_METHOD'];
		} else {
			if (!strncasecmp(PHP_SAPI, 'cli', 3)) {
				$this->method = 'Cli';
			} else {
				$this->method = 'Unknown';
			}
		}

		if (empty($request_uri)) {
			do {
// #ifdef PHP_WIN32
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				/* check this first so IIS will catch */
				if ($request_uri = $this->getServer('HTTP_X_REWRITE_URL')) {
					break;
				}

				/* IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem) */
				if ($rewrited = (boolean)$this->getServer('IIS_WasUrlRewritten')) {
					$unencode = $this->getServer('UNENCODED_URL');
					if ($unencode && is_string($unencode)) {
						$request_uri = $unencode;
					}
					break;
				}
// #endif
}
				if ($request_uri = $this->getServer('PATH_INFO')) {
					break;
				}

				if ($request_uri = $this->getServer('REQUEST_URI')) {
					/* Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path */
					if (strstr($request_uri, 'http') == $request_uri) {
						$url_info = parse_url($request_uri);
						if ($url_info && isset($url_info['path'])) {
							$request_uri = $url_info['path'];
						}
					} else {
						if ($pos = strstr($request_uri, '?')) {
							$request_uri = substr($request_uri, 0, $pos - 1);
						}
					}
					break;
				}

				if ($request_uri = $this->getServer('ORIG_PATH_INFO')) {
					/* intended do nothing */
					/*
					if ($query = $this->getServer('QUERY_STRING')) {
					}
					*/
					break;
				}

			} while (0);
		}

		if ($request_uri && is_string($request_uri)) {
			$request_uri = str_replace('//', '/', $request_uri);
			$this->uri = $request_uri;

			// yaf_request_set_base_uri
			$this->_set_base_uri($base_uri, $request_uri);
		}

		$this->params = array();

	}

	/**
	 * getQuery
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getQuery($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_GET;
		} elseif (isset($_GET[$name])) {
			return $_GET[$name];
		}
		return $default;
	}

	/**
	 * getRequest
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getRequest($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_REQUEST;
		} elseif (isset($_REQUEST[$name])) {
			return $_REQUEST[$name];
		}
		return $default;
	}

	/**
	 * getPost
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_POST;
		} elseif (isset($_POST[$name])) {
			return $_POST[$name];
		}
		return $default;
	}

	/**
	 * getCookie
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getCookie($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_COOKIE;
		} elseif (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return $default;
	}

	/**
	 * getFiles
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getFiles($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_FILES;
		} elseif (isset($_FILES[$name])) {
			return $_FILES[$name];
		}
		return $default;
	}

	/**
	 * get [params -> post -> get -> cookie -> server]
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */	
	public function get($name, $default = null)
	{
		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		} elseif (isset($_POST[$name])) {
			return $_POST[$name];
		} elseif (isset($_GET[$name])) {
			return $_GET[$name];
		} elseif (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		} elseif (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}
		return $default;
	}

	/**
	 * isXmlHttpRequest
	 *
	 * @param void
	 * @return boolean
	 */	
	public function isXmlHttpRequest()
	{
		$header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
		if (is_string($header) && strncasecmp('XMLHttpRequest', $header, 14) == 0) {
			return true;
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


/**
 * Yaf_Request_Simple
 * 
 */
final class Yaf_Request_Simple extends Yaf_Request_Abstract
{
	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	/**
	 * __construct
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @param string $method
	 * @param array $params
	 */
	public function __construct ($module, $controller, $action, $method, $params = null)
	{
		if ($params && !is_array($params)) {
			unset($this);
			trigger_error('Expects the params is an array', E_USER_ERROR);
			return false;
		}

		if (is_string($method)) {
			$this->method = $method;
		} else {
			if (isset($_SERVER['REQUEST_METHOD'])) {
				$this->method = $_SERVER['REQUEST_METHOD'];
			} else {
				if (!strncasecmp(PHP_SAPI, 'cli', 3)) {
					$this->method = 'CLI';
				} else {
					$this->method = 'Unknown';
				}
			}
		}

		if ($module || $controller || $action) {
			if ($module && is_string($module)) {
				$this->module = $module;
			} else {
				$this->module = YAF_G('default_module');
			}

			if ($controller && is_string($controller)) {
				$this->controller = $controller;
			} else {
				$this->controller = YAF_G('default_controller');
			}

			if ($action && is_string($action)) {
				$this->action = $action;
			} else {
				$this->controller = YAF_G('default_action');
			}

			$this->routed = true;
		} else {
			$argv = $this->getServer('argv');
			if (is_array($argv)) {
				foreach($argv as $value) {
					if (is_string($value)) {
						if (strncasecmp($value, 'request_uri=', 12)) {
							continue;
						}
						$query = substr($value, 12);
						break;
					}
				}
			}

			if (empty($query)) {
				$this->uri = '';
			} else {
				$this->uri = $query;
			}
		}

		if ($params && is_array($params)) {
			$this->params = $params;
		} else {
			$this->params = array();
		}
	}
	
	/**
	 * getQuery
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getQuery($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_GET;
		} elseif (isset($_GET[$name])) {
			return $_GET[$name];
		}
		return $default;
	}

	/**
	 * getRequest
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getRequest($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_REQUEST;
		} elseif (isset($_REQUEST[$name])) {
			return $_REQUEST[$name];
		}
		return $default;
	}

	/**
	 * getPost
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_POST;
		} elseif (isset($_POST[$name])) {
			return $_POST[$name];
		}
		return $default;
	}

	/**
	 * getCookie
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getCookie($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_COOKIE;
		} elseif (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return $default;
	}

	/**
	 * getFiles
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getFiles($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_FILES;
		} elseif (isset($_FILES[$name])) {
			return $_FILES[$name];
		}
		return $default;
	}

	/**
	 * get [params -> post -> get -> cookie -> server]
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */	
	public function get($name, $default = null)
	{
		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		} elseif (isset($_POST[$name])) {
			return $_POST[$name];
		} elseif (isset($_GET[$name])) {
			return $_GET[$name];
		} elseif (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		} elseif (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}
		return $default;
	}

	/**
	 * isXmlHttpRequest
	 *
	 * @param void
	 * @return boolean
	 */	
	public function isXmlHttpRequest()
	{
		$header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['X-Requested-With'] : '';
		if (is_string($header) && strncasecmp('XMLHttpRequest', $header, 14) == 0) {
			return true;
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


/**
 * Yaf_Response_Abstract
 * 
 */
abstract class Yaf_Response_Abstract
{
	const DEFAULT_BODY = 'content';

	protected $_header = array();
	protected $_body = array();
	protected $_sendheader = false;
	
	/**
	 * __construct
	 *
	 * @param void
	 */
	public function __construct()
	{

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
	 * __toString
	 *
	 * @param void
	 * @return string
	 */
	public function __toString()
	{
		return implode('', $this->_body);
	}

	/**
	 * setBody
	 *
	 * @param string $body
	 * @param string $name
	 * @return boolean | Yaf_Response_Abstract
	 */
	public function setBody($body, $name = null)
	{
		if ($this->_alter_body($name, $body, 0)) {
			return $this;
		}
		return false;
	}

	/**
	 * appendBody
	 *
	 * @param string $body
	 * @param string $name
	 * @return boolean | Yaf_Response_Abstract
	 */
	public function appendBody($body, $name = null)
	{
		if ($this->_alter_body($name, $body, 2)) {
			return $this;
		}
		return false;
	}

	/**
	 * prependBody
	 *
	 * @param string $body
	 * @param string $name
	 * @return boolean | Yaf_Response_Abstract
	 */
	public function prependBody($body, $name = null)
	{
		if ($this->_alter_body($name, $body, 1)) {
			return $this;
		}
		return false;
	}

	/**
	 * clearBody
	 *
	 * @param string $name
	 * @return Yaf_Response_Abstract
	 */
	public function clearBody($name = null)
	{
		if ($name) {
			unset($this->_body[$name]);
		} else {
			$this->_body = array();
		}
	}

	/**
	 * getBody
	 *
	 * @param string $name
	 * @return string
	 */
	public function getBody($name = null)
	{
		if (func_num_args() == 0) {
			return $this->_body[self::DEFAULT_BODY];
		} elseif (is_null($name)) {
			return $this->_body;
		} elseif (is_string($name)
					&& isset($this->_body[$name])) {
			return $this->_body[$name];
		}
		return '';
	}

	/**
	 * setHeader
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param boolean $replace
	 * @return boolean
	 */
	public function setHeader($name, $value, $replace = false)
	{
		return false;
	}

	/**
	 * setAllHeaders
	 *
	 * @param array $header
	 * @return boolean
	 */
	public function setAllHeaders($header)
	{
		return false;
	}

	/**
	 * getHeader
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getHeader($name = null)
	{
		return null;
	}

	/**
	 * clearHeaders
	 *
	 * @param void
	 * @return boolean
	 */
	public function clearHeaders()
	{
		return false;
	}

	/**
	 * setRedirect
	 *
	 * @param string $url
	 * @return boolean
	 */
	public function setRedirect($url)
	{
		if (empty($url)) {
			return false;
		}

		if (header('Location:' . $url)) {
			return true;
		}

		return false;
	}

	/**
	 * response
	 *
	 * @param void
	 * @return boolean
	 */
	public function response()
	{
		foreach ($this->_body as $value) {
			echo $value;
		}

		return true;
	}

	/**
	 * yaf_response_alter_body
	 *
	 * @param string $name
	 * @param string $body
	 * @param integer $flag
	 * @return boolean
	 */
	private function _alter_body($name, $body, $flag)
	{
		if (empty($body)) {
			return true;
		}

		if (empty($name)) {
			$name = self::DEFAULT_BODY;
		}

		if (!isset($this->_body[$name])) {
			$this->_body[$name] = '';
		}

		$obody = $this->_body[$name];

		switch ($flag) {
			case 1:
				$this->_body[$name] = $body . $obody;
				break;
			case 2:
				$this->_body[$name] = $obody . $body;
				break;
			case 0:
			default:
				$this->_body[$name] = $body;
				break;
		}

		return true;
	}

}


/**
 * Yaf_Response_Http
 * 
 */
final class Yaf_Response_Http extends Yaf_Response_Abstract
{
	protected $_sendheader = true;
	protected $_response_code = 200;
	
}


/**
 * Yaf_Response_Cli
 * 
 */
final class Yaf_Response_Cli extends Yaf_Response_Abstract
{

}


/**
 * Yaf_View_Interface
 * 
 */
interface Yaf_View_Interface
{
	/**
	 * assign
	 * 
	 * @param string | array $name
	 * @param mixed $value
	 */
	public function assign($name, $value = null);

	/**
	 * display
	 * 
	 * @param string $view_path
	 * @param array $tpl_vars
	 */
	public function display($view_path, $tpl_vars = null);

	/**
	 * render
	 * 
	 * @param string $view_path
	 * @param array $tpl_vars
	 */
	public function render($view_path, $tpl_vars = null);

	/**
	 * setScriptPath
	 * 
	 * @param string $view_directory
	 */
	public function setScriptPath($view_directory);

	/**
	 * getScriptPath
	 * 
	 * @param void
	 */
	public function getScriptPath();
	
}


/**
 * Yaf_View_Simple
 * 
 */
class Yaf_View_Simple implements Yaf_View_Interface
{
	protected $_tpl_vars;
	protected $_tpl_dir;
	protected $_options;

	private $_tmp_vars;
	private $_tmp_path;

	/**
	 * __construct
	 *
	 * @param string $tpl_dir
	 * @param array $options
	 */
	public function __construct($tpl_dir, $options = null)
	{
		$this->_tpl_vars = array();

		if ($tpl_dir && is_string($tpl_dir)) {
			if ($tpl_dir = realpath($tpl_dir)) {
				$this->_tpl_dir = $tpl_dir;
			} else {
				$this->_trigger_error('Expects an absolute path for templates directory', YAF_ERR_TYPE_ERROR);
				return false;
			}
		}
	}

	/**
	 * __isset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		if (is_array($this->_tpl_vars)) {
			return isset($this->_tpl_vars[$name]);
		}
		return false;
	}

	/**
	 * get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name = null)
	{
		if ($this->_tpl_vars && is_array($this->_tpl_vars)) {
			if (is_null($name)) {
				if (isset($this->_tpl_vars[$name])) {
					return $this->_tpl_vars[$name];
				}
			} else {
				return $this->_tpl_vars;
			}
		}
		return null;
	}
	
	/**
	 * assign
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function assign($name, $value = null)
	{
		$num_args = func_num_args();

		if ($num_args == 1) {
			if (is_array($name)) {
				$this->_tpl_vars = array_merge($this->_tpl_vars, $name);
				return true;
			}
		} elseif ($num_args == 2) {
			$this->_tpl_vars[$name] = $value;
			return true;
		}

		return false;
	}

	/**
	 * render
	 *
	 * @param string $tpl_file
	 * @param array $tpl_vars
	 * @return boolean | string
	 */
	public function render($tpl_file, $tpl_vars = null)
	{
		// yaf_view_simple_extract
		$this->_tmp_vars = array();
		if (is_array($this->_tpl_vars)) {
			foreach ($this->_tpl_vars as $key => $value) {
				if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
					unset($this->_tpl_vars[$key]);
					continue;
				}
			}
			$this->_tmp_vars = array_merge($this->_tmp_vars, $this->_tpl_vars);
		}
		if (is_array($tpl_vars)) {
			foreach ($tpl_vars as $key => $value) {
				if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
					unset($tpl_vars[$key]);
					continue;
				}
			}
			$this->_tmp_vars = array_merge($this->_tmp_vars, $tpl_vars);
		}

		// short_tags
		$short_open_tag = ini_get('short_open_tag');
		if (!is_array($this->_options)
				|| !isset($this->_options['short_tags'])
				|| $this->_options['short_tags'] == true) {
			ini_set('short_open_tag', 'On');
		}

		// ob_start
		if (!ob_start()) {
			trigger_error('failed to create buffer', E_USER_WARNING);
			return false;
		}

		if ($this->_tmp_path = realpath($tpl_file)) {
			if ($this->_loader_import() == false) {
				ob_end_clean();
				$this->_trigger_error('Failed opening template ' . $tpl_file . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		} else {
			if (!is_string($this->_tpl_dir)) {
				ob_end_clean();
				$this->_trigger_error('Could not determine the view script path, you should call Yaf_View_Simple::setScriptPath to specific it');
				return false;
			} else {
				$this->_tmp_path = $this->_tpl_dir . '/' . $tpl_file;
			}

			if ($this->_loader_import() == false) {
				ob_end_clean();
				$this->_trigger_error('Failed opening template ' . $tpl_file . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		}

		ini_set('short_open_tag', $short_open_tag);
		$this->_tmp_vars = array();
		$this->_tmp_path = null;
		
		if (($content = ob_get_contents()) === false) {
			trigger_error('Unable to fetch ob content', E_USER_WARNING);
			return false;
		}

		if (!ob_end_clean()) {
			return false;
		}

		return $content;
	}

	/**
	 * evals
	 *
	 * @param string $tpl_content
	 * @param array $tpl_vars
	 * @param boolean | string
	 */
	public function evals($tpl_content, $tpl_vars = null)
	{
		return false;
	}
	
	/**
	 * display
	 *
	 * @param string $tpl_file
	 * @param array $tpl_vars
	 * @return boolean | string
	 */
	public function display($tpl_file, $tpl_vars = null)
	{
		// yaf_view_simple_extract
		$this->_tmp_vars = array();
		if (is_array($this->_tpl_vars)) {
			foreach ($this->_tpl_vars as $key => $value) {
				if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
					unset($this->_tpl_vars[$key]);
					continue;
				}
			}
			$this->_tmp_vars = array_merge($this->_tmp_vars, $this->_tpl_vars);
		}
		if (is_array($tpl_vars)) {
			foreach ($tpl_vars as $key => $value) {
				if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
					unset($tpl_vars[$key]);
					continue;
				}
			}
			$this->_tmp_vars = array_merge($this->_tmp_vars, $tpl_vars);
		}

		// short_tags
		$short_open_tag = ini_get('short_open_tag');
		if (!is_array($this->_options)
				|| !isset($this->_options['short_tags'])
				|| $this->_options['short_tags'] == true) {
			ini_set('short_open_tag', 'On');
		}

		if ($this->_tmp_path = realpath($tpl_file)) {
			if ($this->_loader_import() == false) {
				$this->_trigger_error('Failed opening template ' . $tpl_path . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		} else {
			if (!is_string($this->_tpl_dir)) {
				$this->_trigger_error('Could not determine the view script path, you should call Yaf_View_Simple::setScriptPath to specific it');
				return false;
			} else {
				$this->_tmp_path = $this->_tpl_dir . '/' . $tpl_file;
			}

			if ($this->_loader_import() == false) {
				$this->_trigger_error('Failed opening template ' . $tpl_path . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		}

		ini_set('short_open_tag', $short_open_tag);
		$this->_tmp_vars = array();
		$this->_tmp_path = null;
		return true;
	}

	/**
	 * assignRef
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function assignRef($name, &$value)
	{
		$this->_tpl_vars[$name] = $value;
		return true;
	}

	/**
	 * assignRef
	 *
	 * @param string $name
	 * @return boolean | Yaf_View_Simple
	 */
	public function clear($name = null)
	{
		if ($this->_tpl_vars && is_array($this->_tpl_vars)) {
			if (is_null($name)) {
				$this->_tpl_vars = array();
			} else {
				unset($this->_tpl_vars[$name]);
			}
		}
		return $this;
	}
	
	/**
	 * setScriptPath
	 *
	 * @param string $tpl_dir
	 * @return boolean | Yaf_View_Simple
	 */
	public function setScriptPath($tpl_dir)
	{
		if (is_string($tpl_dir) && ($tpl_dir = realpath($tpl_dir))) {
			$this->_tpl_dir = $tpl_dir;
			return $this;
		}
		return false;
	}

	/**
	 * getScriptPath
	 *
	 * @param void
	 * @return string
	 */
	public function getScriptPath()
	{
		if (!is_string($this->_tpl_dir) && YAF_G('view_directory')) {
			return YAF_G('view_directory');
		}

		return $this->_tpl_dir;
	}

	/**
	 * __get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * __set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function __set($name, $value = null)
	{
		return $this->assign($name, $value);
	}

	/**
	 * yaf_loader_import
	 * 
	 * @param void
	 * @return boolean
	 */
	private function _loader_import()
	{
		if (is_file($this->_tmp_path) && is_readable($this->_tmp_path)) {
			extract($this->_tpl_vars);
			include($this->_tmp_path);
			return true;
		}
		return false;
	}

	/**
	 * yaf_trigger_error
	 * 
	 * @param string $message
	 * @param integer $code
	 */
	private function _trigger_error($message, $code = YAF_ERR_NOTFOUND_VIEW)
	{
		if (YAF_G('throw_exception')) {
			switch ($code) {
				case YAF_ERR_NOTFOUND_VIEW:
					throw new Yaf_Exception_LoadFailed_View($message);
					break;
				case YAF_ERR_TYPE_ERROR:
					throw new Yaf_Exception_TypeError($message);
					break;
				default:
					throw new Yaf_Exception($message, $code);
					break;
			}
		} else {
			Yaf_Application::app()->setLastError($message, $code);
			trigger_error($message, E_USER_NOTICE);
		}
	}

}


/**
 * Yaf_Exception
 * 
 */
class Yaf_Exception extends Exception
{

}


/**
 * Yaf_Exception_StartupError
 * 
 */
class Yaf_Exception_StartupError extends Yaf_Exception
{
	protected $code = YAF_ERR_STARTUP_FAILED;
}


/**
 * Yaf_Exception_DispatchFailed
 * 
 */
class Yaf_Exception_DispatchFailed extends Yaf_Exception
{
	protected $code = YAF_ERR_DISPATCH_FAILED;
}


/**
 * Yaf_Exception_RouterFailed
 * 
 */
class Yaf_Exception_RouterFailed extends Yaf_Exception
{
	protected $code = YAF_ERR_ROUTE_FAILED;
}


/**
 * Yaf_Exception_LoadFailed
 * 
 */
class Yaf_Exception_LoadFailed extends Yaf_Exception
{
	protected $code = YAF_ERR_AUTOLOAD_FAILED;
}


/**
 * Yaf_Exception_LoadFailed_Module
 * 
 */
class Yaf_Exception_LoadFailed_Module extends Yaf_Exception_LoadFailed
{
	protected $code = YAF_ERR_NOTFOUND_MODULE;
}


/**
 * Yaf_Exception_LoadFailed_Controller
 * 
 */
class Yaf_Exception_LoadFailed_Controller extends Yaf_Exception_LoadFailed
{
	protected $code = YAF_ERR_NOTFOUND_CONTROLLER;
}


/**
 * Yaf_Exception_LoadFailed_Action
 * 
 */
class Yaf_Exception_LoadFailed_Action extends Yaf_Exception_LoadFailed
{
	protected $code = YAF_ERR_NOTFOUND_ACTION;
}


/**
 * Yaf_Exception_LoadFailed_View
 * 
 */
class Yaf_Exception_LoadFailed_View extends Yaf_Exception_LoadFailed
{
	protected $code = YAF_ERR_NOTFOUND_VIEW;
}


/**
 * Yaf_Exception_CallFailed
 * 
 */
class Yaf_Exception_CallFailed extends Yaf_Exception
{
	protected $code = YAF_ERR_CALL_FAILED;
}


/**
 * Yaf_Exception_TypeError
 * 
 */
class Yaf_Exception_TypeError extends Yaf_Exception
{
	protected $code = YAF_ERR_TYPE_ERROR;
}
