<?php
// +----------------------------------------------------------------------
// | yafphp [ Yaf PHP Framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://yafphp.duapp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zmrnet <zmrnet@qq.com>
// +----------------------------------------------------------------------

abstract class Yaf_Request_Abstract
{
	protected $_method ;
	protected $_module ;
	protected $_controller ;
	protected $_action ;
	protected $_params = array();
	protected $_language ;
	protected $_base_uri ;
	protected $_request_uri ;
	protected $_dispatched ;
	protected $_routed ;

	public function getModuleName ()
	{

	}

	public function getControllerName ()
	{

	}

	public function getActionName ()
	{

	}

	public function setModuleName ($name )
	{

	}

	public function setControllerName ($name )
	{

	}

	public function setActionName ($name )
	{

	}

	public function getException ()
	{

	}

	public function getParams ()
	{

	}

	public function getParam ($name,$dafault = NULL )
	{

	}

	public function setParam ($name, $value )
	{

	}

	public function getMethod ()
	{

	}

	public function isDispatched ()
	{

	}

	public function setDispatched ()
	{

	}

	public function isRouted ()
	{

	}

	public function setRouted ()
	{

	}

	
	abstract public function getLanguage ();

	abstract public function getQuery ( $name = NULL );

	abstract public function getPost ( $name = NULL );

	abstract public function getEnv ($name = NULL );

	abstract public function getServer ($name = NULL );

	abstract public function getCookie ($name = NULL );

	abstract public function getFiles ($name = NULL );

	abstract public function isGet ();

	abstract public function isPost ();

	abstract public function isHead ();

	abstract public function isXmlHttpRequest ();

	abstract public function isPut ();

	abstract public function isDelete ();

	abstract public function isOption ();

	abstract public function isCli ();

}
