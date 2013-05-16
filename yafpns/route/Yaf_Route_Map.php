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

final class Route_Map implements Route_Interface
{
	protected $_ctl_router = false;
	protected $_delimeter;

	/**
	 * __construct
	 *
	 * @param string $ctl_router
	 * @param string $delimeter
	 */
	public function __construct($ctl_router = false, $delimeter = '#!')
	{
		if ($ctl_router) {
			$this->_ctl_router = true;
		}

		if ($delimeter && is_string($delimeter)) {
			$this->_delimeter = $delimeter;
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
			$query_str = null;

			$base_uri = $request->getBaseUri();
			$request_uri = $request->getRequestUri();

			if ($base_uri && is_string($base_uri)
					&& !strncasecmp($request_uri, $base_uri, strlen($base_uri))) {
				$request_uri = substr($request_uri, strlen($base_uri));
			}

			$request_uri = trim($request_uri, '/');
			if ($this->_delimeter && is_string($this->_delimeter)) {
				if ($query_str = strstr($request_uri, $this->_delimeter)) {
					$request_uri = substr($request_uri, 0, - strlen($query_str));
					$query_str = substr($query_str, strlen($this->_delimeter));
				}
			}

			$route_result = str_replace('/', '_', $request_uri);

			if ($route_result) {
				if ($this->_ctl_router) {
					$request->setControllerName($route_result);
				} else {
					$request->setActionName($route_result);
				}
			}
			
			if ($query_str) {
				$params = $this->_parse_parameters($query_str);
				$request->setParam($params);
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
