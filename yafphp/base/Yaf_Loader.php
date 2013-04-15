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

final class Yaf_Loader
{
	protected static $_instance;

	protected $_library_directory;
	protected $_global_library_directory;
	protected $_local_ns;

	/**
	 * getInstance
	 *
	 */
	public static function getInstance($local_library_directory = null, $global_library_directory = null)
	{
		if (!(self::$_instance instanceof self)) {
			if ((is_null($local_library_directory) && is_null($global_library_directory))
					|| (empty($local_library_directory) && empty($global_library_directory))
				){
				return;
			}

			self::$_instance = new self();
		}
		
		if ($local_library_directory && is_string($local_library_directory)) {
			self::$_instance->_library_directory = $local_library_directory;
		}

		if ($global_library_directory && is_string($global_library_directory)) {
			self::$_instance->_global_library_directory = $global_library_directory;
		}
		return self::$_instance;
	}
	
	/**
	 * registerLocalNamespace
	 *
	 */
	public function registerLocalNamespace($namespace)
	{

	}
	
	/**
	 * getLocalNamespace
	 *
	 */
	public function getLocalNamespace()
	{

	}
	
	/**
	 * clearLocalNamespace
	 *
	 */
	public function clearLocalNamespace()
	{

	}
	
	/**
	 * isLocalName
	 *
	 */
	public function isLocalName($class_name)
	{

	}
	
	/**
	 * autoload
	 *
	 */
	public function autoload($class_name)
	{

	}
	
	/**
	 * import
	 *
	 */
	public static function import($file_name)
	{
		if (is_file($file_name) && is_readable($file_name)) {
			require_once($file_name);
			return true;
		}
		return false;
	}
	
}

//spl_autoload_register(array('Yaf_Loader', 'autoload'));