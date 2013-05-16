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

abstract class Plugin_Abstract
{
	/**
	 * routerStartup
	 * 
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @return boolean
	 */
	public function routerStartup($request, $response)
	{
		return true;
	}

	/**
	 * routerShutdown
	 * 
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @return boolean
	 */
	public function routerShutdown($request, $response)
	{
		return true;
	}

	/**
	 * dispatchLoopStartup
	 * 
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @return boolean
	 */
	public function dispatchLoopStartup($request, $response)
	{
		return true;
	}
	
	/**
	 * dispatchLoopShutdown
	 * 
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @return boolean
	 */
	public function dispatchLoopShutdown($request, $response)
	{
		return true;
	}
	
	/**
	 * preDispatch
	 * 
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @return boolean
	 */
	public function preDispatch($request, $response)
	{
		return true;
	}
	
	/**
	 * postDispatch
	 * 
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @return boolean
	 */
	public function postDispatch($request, $response)
	{
		return true;
	}
	
	/**
	 * preResponse
	 * 
	 * @param Yaf\Request_Abstract $request
	 * @param Yaf\Response_Abstract $response
	 * @return boolean
	 */
	public function preResponse($request, $response)
	{
		return true;
	}
	
}
