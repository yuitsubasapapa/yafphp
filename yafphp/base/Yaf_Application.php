<?php
final class Yaf_Application
{
	protected static $_app = NULL;

	protected $_config = NULL;
	protected $_dispatcher;
	protected $_environ = YAF_ENVIRON;
	protected $_modules = array();

	protected $_run = FALSE;

	/**
	 * __construct
	 *
	 */
	public function __construct($config, $section = YAF_ENVIRON)
	{
		if(!is_null(self::$_app)){
			throw new Yaf_Exception_StartupError('Only one application can be initialized');
		}

		if(is_string($section) && strlen($section)){
			$this->_environ = $section;
		}

		if(is_string($config)){
			$this->_config = new Yaf_Config_Ini($config, $section);
		}
		if(is_array($config)){
			$this->_config = new Yaf_Config_Simple($config, $section);
		}

		if(is_null($this->_config)
			|| !is_object($this->_config)
			|| !($this->_config instanceof Yaf_Config_Abstract)
			|| yaf_application_parse_option(zend_read_property(yaf_config_ce,
				   	zconfig, ZEND_STRL(YAF_CONFIG_PROPERT_NAME), 1 TSRMLS_CC) TSRMLS_CC) == FAILURE)
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