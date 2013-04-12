<?php
// +----------------------------------------------------------------------
// | yafphp [ Yaf PHP Framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2007-2012 http://yodphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yodphp <yodphp@qq.com>
// +----------------------------------------------------------------------

// runtime
defined('YOD_RUNTIME') or define('YOD_RUNTIME', microtime(true));
// memory used
defined('YOD_MEMUSED') or define('YOD_MEMUSED', memory_get_usage());
// core dir
defined('YOD_COREDIR') or define('YOD_COREDIR', dirname(__FILE__));
// debug
defined('YOD_DEBUG') or define('YOD_DEBUG', false);

class Yafphp
{
	private static $_app;

	private static $_includes = array();

	public static function app()
	{
		return self::$_app;
	}

	public static function setInclude($alias, $incpath)
	{
		if(empty($incpath))
			unset(self::$_includes[$alias]);
		else
			self::$_includes[$alias] = rtrim($incpath, '\\/');
	}

	public static function setApplication($app)
	{
		if(self::$_app===null || $app===null)
			self::$_app = $app;
		else
			throw new Exception('Yod application can only be created once.');
	}

	public static function runApplication($config = null)
	{
		self::$_app = new Yod_Application($config);
		self::$_app->bootstrap()->run();
	}

	public static function autoload($classname)
	{
		$classpath = $classname;
		// class name with namespace in PHP 5.3
		if(strpos($classname, '\\') !== false)
			$classpath = str_replace('\\', '_', $classname);

		if(substr($classpath, 0, 4) == 'Yaf_')  // yafphp core class
		{
			$yodpath = new DirectoryIterator(dirname(__FILE__));
			foreach($yodpath as $incpath)
			{
				if($incpath->isDir() && $incpath->isDot()===false)
				{
					$classfile	= $incpath->getPathname() . DIRECTORY_SEPARATOR . $classpath . '.php';
					if(is_file($classfile))
					{
						include($classfile);
						break;
					}
				}
			}
		}
		else  // yodphp app library class
		{
			$classpath = str_replace('_', DIRECTORY_SEPARATOR, $classpath);
			foreach(self::$_includes as $incpath)
			{
				$classfile	= $incpath . DIRECTORY_SEPARATOR . $classpath . '.php';
				if(is_file($classfile))
				{
					include($classfile);
					break;
				}
			}
		}

		return class_exists($classname, false) || interface_exists($classname, false);
	}

}

/*
//print_r(get_defined_vars());
//print_r(get_defined_constants());
//print_r(get_defined_functions());
//print_r(get_declared_classes());
*/
echo ini_get('yaf.environ');
echo ini_get('yaf.library');
echo ini_get('yaf.cache_config');
echo ini_get('yaf.name_suffix');
echo ini_get('yaf.name_separator');
echo ini_get('yaf.forward_limit');
echo ini_get('yaf.use_namespace');
echo ini_get('yaf.use_spl_autoload');

define('YAF_VERSION', '1.0.0');
define('YAF_ENVIRON', ini_get('yaf.environ') ? ini_get('yaf.environ') : 'product');
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

echo YAF_ENVIRON;

/*
Yaf_Application
Yaf_Bootstrap_Abstract
Yaf_Dispatcher
Yaf_Loader
Yaf_Request_Abstract
Yaf_Request_Http
Yaf_Request_Simple
Yaf_Response_Abstract
Yaf_Response_Http
Yaf_Response_Cli
Yaf_Controller_Abstract
Yaf_Action_Abstract
Yaf_Config_Abstract
Yaf_Config_Ini
Yaf_Config_Simple
Yaf_View_Simple
Yaf_Router
Yaf_Route_Static
Yaf_Route_Simple
Yaf_Route_Supervar
Yaf_Route_Rewrite
Yaf_Route_Regex
Yaf_Route_Map
Yaf_Plugin_Abstract
Yaf_Registry
Yaf_Session
Yaf_Exception
Yaf_Exception_StartupError
Yaf_Exception_RouterFailed
Yaf_Exception_DispatchFailed
Yaf_Exception_LoadFailed
Yaf_Exception_LoadFailed_Module
Yaf_Exception_LoadFailed_Controller
Yaf_Exception_LoadFailed_Action
Yaf_Exception_LoadFailed_View
Yaf_Exception_TypeError
*/


function __autoload($classname)
{
	$classpath = $classname;
	// class name with namespace in PHP 5.3
	if(strpos($classname, '\\') !== false)
		$classpath = str_replace('\\', '_', $classname);

	if(substr($classpath, 0, 4) == 'Yaf_')  // yafphp core class
	{
		$yafpath = new DirectoryIterator(YOD_COREDIR);
		foreach($yafpath as $incpath)
		{
			if($incpath->isDir() && $incpath->isDot()===false)
			{
				$classfile	= $incpath->getPathname() . DIRECTORY_SEPARATOR . $classpath . '.php';
				if(is_file($classfile))
				{
					include($classfile);
					break;
				}
			}
		}
	}
	else  // yodphp app library class
	{
		$classpath = str_replace('_', DIRECTORY_SEPARATOR, $classpath);
		foreach(self::$_includes as $incpath)
		{
			$classfile	= $incpath . DIRECTORY_SEPARATOR . $classpath . '.php';
			if(is_file($classfile))
			{
				include($classfile);
				break;
			}
		}
	}

	return class_exists($classname, false) || interface_exists($classname, false);
}

(strtolower(ini_get('yaf.use_spl_autoload')) == 'on') or spl_autoload_register('__autoload');
