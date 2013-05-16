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

final class Route_Simple implements Route_Interface
{
	protected $module;
	protected $controller;
	protected $action;

	/**
	 * __construct
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 */
	public function __construct($module, $controller, $action)
	{
		if (is_string($module) && is_string($controller) && is_string($action)) {
			$this->module = $module;
			$this->controller = $controller;
			$this->action = $action;
		} else {
			trigger_error('Expect 3 string paramsters', E_USER_ERROR);
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
			$module = $request->getQuery($this->module);
			$controller = $request->getQuery($this->controller);
			$action = $request->getQuery($this->action);

			if (is_null($module) && is_null($controller) && is_null($action)) {
				return false;
			}

			if ($module && $this->_is_module_name($module)) {
				$request->setModuleName($module);
			}

			$request->setControllerName((string)$controller);
			$request->setActionName((string)$action);

			return true;
		}

		return false;
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
			$modules = Application::app()->getModules();
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

}
