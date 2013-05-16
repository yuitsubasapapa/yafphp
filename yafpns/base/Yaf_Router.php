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

final class Router
{
	protected $_routes;
	protected $_current;

	/**
	 * __construct
	 *
	 * @param void
	 */
	public function __construct()
	{
		$this->_routes = array();

		if (!YAF_G('default_route')) {
		    $this->_routes['_default'] = new Route_Static();
		} elseif (!($this->_routes['_default'] = $this->_route_instance())) {
			trigger_error('Unable to initialize default route, use Yaf\Route_Static instead', E_USER_WARNING);
			$this->_routes['_default']= new Route_Static();
		}

	}

	/**
	 * addRoute
	 *
	 * @param string $name
	 * @param Yaf\Route_Interface $route
	 * @return boolean | Yaf\Router
	 */
	public function addRoute($name, $route)
	{
		if (empty($name) || !is_string($name)) {
			return false;
		}

		if (!is_object($route) || !($route instanceof Route_Interface)) {
			trigger_error('Expects a Yaf\Route_Interface instance', E_USER_WARNING);
			return false;
		}

		$this->_routes[$name] = $route;

		return $this;
	}

	/**
	 * addConfig
	 *
	 * @param Yaf\Config_Abstract $config
	 * @return boolean | Yaf\Router
	 */
	public function addConfig($config)
	{
		if (is_object($config) && ($config instanceof Config_Abstract)) {
			$routes = $config->toArray();
		} elseif(is_array($config)) {
			$routes = $config;
		} else {
			trigger_error('Expect a Yaf\Config_Abstract instance or an array, ' . gettype($config) . ' given', E_USER_WARNING);
			return false;
		}

		foreach ($routes as $key => $value) {
			if (empty($value) || !is_array($value)) {
				continue;
			}

			if ($route = $this->_route_instance($value)) {
				$this->_routes[$key] = $route;
			} else {
				if (is_numeric($key)) {
					trigger_error('Unable to initialize route named \'' . $key . '\'', E_USER_WARNING);
				} else {
					trigger_error('Unable to initialize route at index' . $key, E_USER_WARNING);
				}
				continue;
			}
		}

		return $this;
	}

	/**
	 * route
	 *
	 * @param Yaf\Request_Abstract $request
	 * @return boolean
	 */	
	public function route($request)
	{
		foreach ($this->_routes as $key => $value) {
			if (call_user_func(array($value, 'route'), $request) === true) {
				$this->_current = $key;
				$request->setRouted();
				return true;
			}
		}

		return false;
	}

	/**
	 * getRoute
	 *
	 * @param string $name
	 * @return boolean | Yaf\Router
	 */
	public function getRoute($name)
	{
		if (empty($name)) {
			return false;
		}

		if (isset($this->_routes[$name])) {
			return $this->_routes[$name];
		}

		return null;
	}
		
	/**
	 * getRoutes
	 *
	 * @param void
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->_routes;
	}

	/**
	 * getCurrentRoute
	 *
	 * @param void
	 * @return integer | string
	 */
	public function getCurrentRoute()
	{
		return $this->_current;
	}

	/**
	 * yaf_route_instance
	 *
	 * @param array $config
	 * @return Yaf\Route_Interface
	 */
	private function _route_instance($config = null)
	{
		if (is_null($config)) {
			$config = YAF_G('default_route');
		}

		if (!$config || !is_array($config)) {
			return null;
		}

		if (empty($config['type']) || !is_string($config['type'])) {
			return null;
		}

		if (strtolower($config['type']) == 'rewrite') {
			if (!isset($config['match']) || !is_string($config['match'])) {
				return null;
			}
			if (!isset($config['route']) || !is_array($config['route'])) {
				return null;
			}

			return new Route_Rewrite($config['match'], $config['route']);
		} elseif (strtolower($config['type']) == 'regex') {
			if (!isset($config['match']) || !is_string($config['match'])) {
				return null;
			}
			if (!isset($config['route']) || !is_array($config['route'])) {
				return null;
			}
			if (!isset($config['map']) || !is_array($config['map'])) {
				return null;
			}

			return new Route_Regex($config['match'], $config['route'], $config['map']);
		} elseif (strtolower($config['type']) == 'map') {
			$delimiter = null;
			$controller_prefer = false;
			
			if (isset($config['controllerPrefer'])) {
				$controller_prefer = (boolean)$config['controllerPrefer'];
			}

			if (isset($config['delimiter']) && is_string($config['delimiter'])) {
				$delimiter = $config['delimiter'];
			}

			return new Route_Map($controller_prefer, $delimiter);
		} elseif (strtolower($config['type']) == 'simple') {
			if (empty($config['module']) || !is_string($config['module'])) {
				return null;
			}
			if (empty($config['controller']) || !is_string($config['controller'])) {
				return null;
			}
			if (empty($config['action']) || !is_string($config['action'])) {
				return null;
			}

			return new Route_Simple($config['module'], $config['controller'], $config['action']);
		} elseif (strtolower($config['type']) == 'supervar') {
			if (empty($config['varname']) || !is_string($config['varname'])) {
				return null;
			}

			return new Route_Supervar($config['varname']);
		}
	}
	
}
