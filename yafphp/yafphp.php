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
