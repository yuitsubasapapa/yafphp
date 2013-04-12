<?php
final class Yaf_Application
{
	protected static $_app;

	protected $_config;
	protected $_dispatcher;
	protected $_environ;
	protected $_modules;

	protected $_run = FALSE;

	/**
	 * __construct
	 *
	 */
	public function __construct($config, $section = NULL)
	{
		
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
	 * getDispatcher
	 *
	 */
	public function getDispatcher()
	{

	}

	/**
	 * getConfig
	 *
	 */
	public function getConfig()
	{

	}

	/**
	 * environ
	 *
	 */
	public function environ()
	{

	}

	/**
	 * environ
	 *
	 */
	public function geModules()
	{

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