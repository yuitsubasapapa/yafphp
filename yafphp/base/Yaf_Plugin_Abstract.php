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

abstract class Yaf_Plugin_Abstract
{
	/**
	 * routerStartup
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function routerStartup($request, $response)
	{
		return true;
	}

	/**
	 * routerShutdown
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function routerShutdown($request, $response)
	{
		return true;
	}

	/**
	 * dispatchLoopStartup
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function dispatchLoopStartup($request, $response)
	{
		return true;
	}
	
	/**
	 * dispatchLoopShutdown
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function dispatchLoopShutdown($request, $response)
	{
		return true;
	}
	
	/**
	 * preDispatch
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function preDispatch($request, $response)
	{
		return true;
	}
	
	/**
	 * postDispatch
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function postDispatch($request, $response)
	{
		return true;
	}
	
	/**
	 * preResponse
	 * 
	 * @param Yaf_Request_Abstract $request
	 * @param Yaf_Response_Abstract $response
	 * @return boolean
	 */
	public function preResponse($request, $response)
	{
		return true;
	}
	
}
