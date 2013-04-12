<?php
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

	public function getModuleName ( void );
	public function getControllerName ( void );
	public function getActionName ( void );
	public function setModuleName ( string $name );
	public function setControllerName ( string $name );
	public function setActionName ( string $name );
	public function getException ( void );
	public function getParams ( void );
	public function getParam ($name,$dafault = NULL );
	public function setParam ($name, $value );
	public function getMethod ( void );
	public function isDispatched ( void );
	public function setDispatched ( void );
	public function isRouted ( void );
	public function setRouted ( void );
	
	abstract public function getLanguage ( void );
	abstract public function getQuery ( string $name = NULL );
	abstract public function getPost ( string $name = NULL );
	abstract public function getEnv ( string $name = NULL );
	abstract public function getServer ( string $name = NULL );
	abstract public function getCookie ( string $name = NULL );
	abstract public function getFiles ( string $name = NULL );
	abstract public function isGet ( void );
	abstract public function isPost ( void );
	abstract public function isHead ( void );
	abstract public function isXmlHttpRequest ( void );
	abstract public function isPut ( void );
	abstract public function isDelete ( void );
	abstract public function isOption ( void );
	abstract public function isCli ( void );
}
