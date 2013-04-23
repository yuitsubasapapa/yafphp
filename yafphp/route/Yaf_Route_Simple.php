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

final class Yaf_Route_Simple implements Yaf_Route_Interface
{
	protected $module;
	protected $controller;
	protected $action;

	/**
	 * route
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
			yaf_trigger_error('Expect 3 string paramsters', YAF_ERR_TYPE_ERROR);
		}
	}

	/**
	 * route
	 *
	 * @param Yaf_Request_Abstract $request
	 * @return boolean
	 */
	public function route($request)
	{
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

}
