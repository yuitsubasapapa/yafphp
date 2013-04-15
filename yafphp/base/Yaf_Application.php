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
	protected static $_app = NULL;

	protected $_config = NULL;
	protected $_dispatcher;
	protected $_environ = YAF_ENVIRON;
	protected $_modules = array();

	protected $_run = FALSE;

	private $_g = array(
		'directory' => '',
		'ext' => 'php',
		'bootstrap' => '/Bootstrap.php',
		'local_library' => '/library',
		'local_namespaces' => '',
		'view_ext' => 'phtml',
		'base_uri' => null,
		'default_module' => 'index',
		'default_controller' => 'index',
		'default_action' => 'index',
		'default_route' => array(),
		'throw_exception' => true,
		'catch_exception' => false,
		'modules' => array(),
	);

	/**
	 * __construct
	 *
	 */
	public function __construct($config, $section = YAF_ENVIRON)
	{
		if (empty($config)) return;

		if (!is_null(self::$_app)) {
			unset($this);
			throw new Yaf_Exception_StartupError('Only one application can be initialized');
			return;
		}

		if (is_string($section) && strlen($section)) {
			$this->_environ = $section;
		}

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
			return;
		}

		$request = new Yaf_Request_Http(null, $this->_g['base_uri']);
		if ($this->_g['base_uri']) {
			$this->_g['base_uri'] = null;
		}

		if(!$request){
			throw new Yaf_Exception_StartupError('Initialization of request failed');
			return;
		}

		$this->_dispatcher = new Yaf_Dispatcher();
		if (is_null($this->_dispatcher)
				|| !is_object($this->_dispatcher)
				|| !($this->_dispatcher instanceof Yaf_Dispatcher)) {
			unset($this);
			throw new Yaf_Exception_StartupError('Instantiation of application dispatcher failed');
			return;
		}
		$this->_dispatcher->setRequest($request);

/*
	zdispatcher = yaf_dispatcher_instance(NULL TSRMLS_CC);
	if (NULL == zdispatcher
			|| Z_TYPE_P(zdispatcher) != IS_OBJECT
			|| !instanceof_function(Z_OBJCE_P(zdispatcher), yaf_dispatcher_ce TSRMLS_CC)) {
		YAF_UNINITIALIZED_OBJECT(getThis());
		yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "Instantiation of application dispatcher failed");
		RETURN_FALSE;
	}
	yaf_dispatcher_set_request(zdispatcher, request TSRMLS_CC);

	zend_update_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_CONFIG), zconfig TSRMLS_CC);
	zend_update_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_DISPATCHER), zdispatcher TSRMLS_CC);

	zval_ptr_dtor(&request);
	zval_ptr_dtor(&zdispatcher);
	zval_ptr_dtor(&zconfig);

	if (YAF_G(local_library)) {
		loader = yaf_loader_instance(NULL, YAF_G(local_library),
				strlen(YAF_G(global_library))? YAF_G(global_library) : NULL TSRMLS_CC);
		efree(YAF_G(local_library));
		YAF_G(local_library) = NULL;
	} else {
		char *local_library;
		spprintf(&local_library, 0, "%s%c%s", YAF_G(directory), DEFAULT_SLASH, YAF_LIBRARY_DIRECTORY_NAME);
		loader = yaf_loader_instance(NULL, local_library,
				strlen(YAF_G(global_library))? YAF_G(global_library) : NULL TSRMLS_CC);
		efree(local_library);
	}

	if (!loader) {
		YAF_UNINITIALIZED_OBJECT(getThis());
		yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "Initialization of application auto loader failed");
		RETURN_FALSE;
	}

	zend_update_property_bool(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_RUN), 0 TSRMLS_CC);
	zend_update_property_string(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_ENV), YAF_G(environ) TSRMLS_CC);

	if (YAF_G(modules)) {
		zend_update_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_MODULES), YAF_G(modules) TSRMLS_CC);
		Z_DELREF_P(YAF_G(modules));
		YAF_G(modules) = NULL;
	} else {
		zend_update_property_null(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_MODULES) TSRMLS_CC);
	}

	zend_update_static_property(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_APP), self TSRMLS_CC);
*/

		self::$_app = &$this;
	}

	/**
	 * bootstrap
	 *
	 */
	public function bootstrap()
	{
		return $this;
	}

	/**
	 * run
	 *
	 */
	public function run()
	{
		return $this;
	}

	/**
	 * getConfig
	 *
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * getDispatcher
	 *
	 */
	public function getDispatcher()
	{
		return $this->_dispatcher;
	}

	/**
	 * geModules
	 *
	 */
	public function geModules()
	{
		return $this->_modules;
	}

	/**
	 * app
	 *
	 */
	public static function app()
	{
		return self::$_app;
	}

	/**
	 * environ
	 *
	 */
	public function environ()
	{

	}

	/**
	 * execute
	 *
	 */
	public function execute($funcion, $parameter = NULL)
	{

	}

	/**
	 * yaf_application_parse_option
	 *
	 */
	private function _parse_option($config = NULL)
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
		} else {
			$this->_g['bootstrap'] = $this->_g['directory'] . $this->_g['bootstrap'];
		}

		if (!isset($app->library)) {
			$this->_g['local_library'] = $this->_g['directory'] . $this->_g['local_library'];
		} else {
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

/*
	public function __construct($config)
	{
		Yaf::setApplication($this);

		if(is_string($config))
			$config = require($config);

		$this->configure($config);
		$this->_dispatcher = Yaf_Dispatcher::getInstance();
		Yaf::setInclude('app.library', $this->_library);

	}

	public function configure($config)
	{
		if(isset($config['application']))
		{
			if(is_array($config['application']))
			{
				foreach($config['application'] as $key => $value)
					$this->{'_' . $key} = $value;
			}
			unset($config['application']);
		}

		if(is_array($config))
		{
			foreach($config as $key => $value)
				$this->$key = $value;
		}
	}

	public function bootstrap($bootstrap = null)
	{
		if($bootstrap instanceof Yaf_Bootstrap_Abstract){
			$bootmethods = get_class_methods($bootstrap);
		}elseif(is_file($this->_bootstrap)){
			require($this->_bootstrap);
			$bootstrap = new Bootstrap();
			$bootmethods = get_class_methods($bootstrap);
		}
		if(is_array($bootmethods)){
			foreach($bootmethods as $method){
				if(substr($method, 0, 5) == '_init' && method_exists($bootstrap, $method))
					$bootstrap->$method($this->_dispatcher);
			}
			unset($bootstrap);
		}

		return $this;
	}

	public function run()
	{
		return $this;
	}
	*/
}