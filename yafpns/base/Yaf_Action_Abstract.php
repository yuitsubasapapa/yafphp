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

abstract class Action_Abstract extends Controller_Abstract
{
	protected $_controller;

	/**
	 * __construct
	 * 
	 * @param Yaf\Controller_Abstract $controller
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @param Yaf\View_Interface $view
	 * @param array $invoke_args
	 */
	public function __construct($controller, $request, $response, $view, $invoke_args = null)
	{
		if ($controller instanceof Controller_Abstract) {
			parent::__construct($request, $response, $view, $invoke_args);
			$this->_name = get_class($controller);
			$this->_controller = $controller;
		}
		return false;
	}

	/**
	 * getController
	 *
	 * @param void
	 * @return Yaf\Controller_Abstract
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * execute
	 *
	 * @param void
	 */
	abstract public function execute();

}
