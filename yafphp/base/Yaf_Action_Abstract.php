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

abstract class Yaf_Action_Abstract extends Yaf_Controller_Abstract
{
	protected $_controller;

	/**
	 * __construct
	 * 
	 * @param Yaf_Controller_Abstract $controller
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @param Yaf_View_Interface $view
	 * @param array $invoke_args
	 */
	public function __construct($controller, $request, $response, $view, $invoke_args = null)
	{
		if ($controller instanceof Yaf_Controller_Abstract) {
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
	 * @return Yaf_Controller_Abstract
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
