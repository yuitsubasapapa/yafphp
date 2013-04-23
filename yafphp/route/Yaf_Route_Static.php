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

final class Yaf_Route_Static implements Yaf_Route_Interface
{
	/**
	 * match
	 *
	 * @param string $uri
	 * @return boolean
	 */
	public function match($uri)
	{
		return true;
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
		if (is_object($request) && ($request instanceof Yaf_Request_Abstract)) {
			$base_uri = $request->getBaseUri();
			$request_uri = $request->getRequestUri();

			if ($base_uri && is_string($base_uri)
					&& !strncasecmp($request_uri, $base_uri, strlen($base_uri))) {
				$request_uri = substr($request_uri, strlen($base_uri));
			}

			return $this->_pathinfo_route($request, $request_uri);
		}

		return false;
	}

	/**
	 * yaf_route_pathinfo_route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @param string $request_uri
	 * @return boolean
	 */
	private function _pathinfo_route($request, $request_uri)
	{
		$module = $controller = $action = $reset = null;

		do {

			if (empty($request_uri) || $request_uri == '/') {
				break;
			}

			$request_uri = trim($request_uri, ' /');

			$token_len = 0;
			if ($token = strtok($request_uri, '/')) {
				if ($this->_is_module_name($token)) {
					$module = $token;
					if ($token = strtok('/')) {
						$controller = trim($token);
						$token_len += strlen($token) + 1;
					}
				} else {
					$controller = $token;
				}
				$token_len += strlen($token) + 1;
			}

			if ($token = strtok('/')) {
				$action = trim($token);
				$token_len += strlen($token) + 1;
			}

			if ($token = strtok('/')) {
				do {
					if (!$module && !$controller && !$action) {
						if ($this->_is_module_name($token)) {
							$module = $token;
							break;
						}
					}

					if (!$controller) {
						$controller = $token;
						break;
					}

					if (!$action) {
						$action = $token;
						break;
					}

					$reset = substr($request_uri, $token_len);
				} while (0);
			}

			if ($module && is_null($controller)) {
				$controller = $module;
				$module = null;
			} elseif ($module && is_null($action)) {
				$action = $controller;
				$controller = $module;
				$module = null;
		    } elseif ($controller && is_null($action)) {
				/* /controller */
				if (YAF_G('action_prefer')) {
					$action = $controller;
					$controller = null;
				}
			}

		} while (0);

		if (!is_null($module)) {
			$request->setModuleName($module);
		}

		if (!is_null($controller)) {
			$request->setControllerName($controller);
		}

		if (!is_null($action)) {
			$request->setActionName($action);
		}

		if ($reset) {
			$params = $this->_parse_parameters($reset);
			$request->setParam($params);
		}

		return true;
	}

	/**
	 * yaf_application_is_module_name
	 *
	 * @param string $name
	 * @return boolean
	 */
	private function _is_module_name($name)
	{
		if ($name && is_string($name)) {
			$modules = Yaf_Application::app()->getModules();
			if ($modules && is_array($modules)) {
				foreach ($modules as $value) {
					if (strcasecmp($name, $value) == 0) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * yaf_router_parse_parameters
	 *
	 * @param string $uri
	 * @return array
	 */
	private function _parse_parameters($uri)
	{
		$params = array();

		$key = strtok($uri, '/');
		while ($key) {
			$params[$key] = strtok('/');
			$key = strtok('/');
		}
		return $params;
	}
	
}
