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
	public static function setLibraryPath($path, $global = false)
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
	public static function getLibraryPath($global = false)
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
