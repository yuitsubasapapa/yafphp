<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

date_default_timezone_set('Asia/Shanghai');

// yafphp
class_exists('Yaf_Application') or require(dirname(__FILE__) . '/yafphp/yaf.php');

define('APP_PATH',  realpath(dirname(__FILE__)));

$config = APP_PATH . '/conf/app.ini';
$yafapp  = new Yaf_Application($config);
$yafapp->bootstrap()->run();