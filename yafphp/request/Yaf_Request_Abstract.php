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
				$this->_params = array_merge($this->_params, $name);
				return $this;
			}
		} elseif(is_string($name)) {
			$this->_params[$name] = $value;
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
		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		}
		return $dafault;
	}
	
	/**
	 * getParams
	 *
	 * @param void
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
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
	 * @param string $request_uri
	 * @return boolean | Yaf_Request_Abstract
	 */
	public function setBaseUri($base_uri, $request_uri = '')
	{
		if ($base_uri && is_string($base_uri)) {
			$this->_base_uri = $base_uri;
			return $this;
		} else {
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

				return $this;
			} elseif ($basename) {
				$dirname = rtrim(dirname($basename), '/');
				if ($dirname) {
					if (strstr($request_uri, $dirname) == $request_uri) {
						$this->_base_uri = $dirname;

						return $this;
					}
				}
			}

			$this->_base_uri = '';

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

}
