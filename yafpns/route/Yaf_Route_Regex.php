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

namespace Yaf;

final class Route_Regex implements Route_Interface
{
	protected $_match;
	protected $_route;
	protected $_maps;
	protected $_verify;
	
	/**
	 * __construct
	 *
	 * @param string $match
	 * @param array $route
	 * @param array $maps
	 * @param array $verify
	 */
	public function __construct($match, $route, $maps = null, $verify = null)
	{
		if (empty($match) || !is_string($match)) {
			unset($this);
			trigger_error('Expects a valid string as the first parameter', E_USER_ERROR);
			return false;
		}

		if ($route && !is_array($route)) {
			unset($this);
			trigger_error('Expects an array as the second parameter', E_USER_ERROR);
			return false;
		}

		if ($maps && !is_array($maps)) {
			unset($this);
			trigger_error('Expects an array as the third parmater', E_USER_ERROR);
			return false;
		}

		if ($verify && !is_array($verify)) {
			unset($this);
			trigger_error('Expects an array as verify parmater', E_USER_ERROR);
			return false;
		}

		$this->_match = $match;
		$this->_route = $route;
		$this->_maps = $maps;

		if (is_array($verify)) {
			$this->_verify = $verify;
		}
	}

	/**
	 * route
	 *
	 * @param Yaf\Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		if (is_object($request) && ($request instanceof Request_Abstract)) {
			$base_uri = $request->getBaseUri();
			$request_uri = $request->getRequestUri();

			if ($base_uri && is_string($base_uri)
					&& !strncasecmp($request_uri, $base_uri, strlen($base_uri))) {
				$request_uri = substr($request_uri, strlen($base_uri));
			}

			if ($args = $this->_regex_match($request_uri)) {
				if (isset($this->_route['module'])) {
					$request->setModuleName($this->_route['module']);
				}

				if (isset($this->_route['controller'])) {
					$request->setControllerName($this->_route['controller']);
				}

				if (isset($this->_route['action'])) {
					$request->setActionName($this->_route['action']);
				}

				$request->setParam($args);

				return true;
			}

			return false;
		}

		trigger_error('Expect a Yaf\Request_Abstract instance', E_USER_WARNING);
		return false;
	}

	/**
	 * yaf_route_regex_match
	 *
	 * @param string $request_uri
	 * @return boolean
	 */
	private function _regex_match($request_uri)
	{
		if (empty($request_uri)) {
			return null;
		}

		if (!preg_match($this->_match, $request_uri, $matches)) {
			return null;
		}

		$ret = array();
		foreach ($matches as $key => $value) {
			if (is_numeric($key)) {
				if (isset($this->_maps[$key])) {
					$ret[$this->_maps[$key]] = $value;
				}
			}elseif (is_string($key)) {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}
}
