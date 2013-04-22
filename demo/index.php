<?php
define('YAF_RUNTIME', microtime(true));

error_reporting(E_ALL);
ini_set('display_errors', 'On');
defined('YAF_DEBUG') or define('YAF_DEBUG', true);

date_default_timezone_set('Asia/Shanghai');

// config
defined('YAF_ENVIRON') or define('YAF_ENVIRON', 'develop');
defined('YAF_USE_SPL_AUTOLOAD') or define('YAF_USE_SPL_AUTOLOAD', true);

// yafphp
class_exists('Yaf_Application') or require(dirname(__FILE__) . '/../yafphp/yafphp.php');
// yafphp use namespace
//class_exists('Yaf\Application') or require(dirname(__FILE__) . '/../yafpns/yafpns.php');

define('APP_PATH',  realpath(dirname(__FILE__)));

$config = APP_PATH . '/conf/app.ini';
$yafapp  = new Yaf_Application($config);
$yafapp->bootstrap()->run();

// runtime
if (YAF_DEBUG) {
	$runtime = round((microtime(true) - YAF_RUNTIME) * 1000, 2);
	echo '<hr>[' . $runtime . 'ms]';
}
