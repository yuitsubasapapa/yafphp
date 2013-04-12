<?php
final class Yaf_Dispatcher
{
	private static $_instance;

	private $_request;

	public $t;

	public function __construct()
	{
		
	}

	public static function getInstance()
	{
		if(self::$_instance instanceof self)
			return self::$_instance;
		else
			return self::$_instance = new self();
	}
	
	public function getRequest()
	{
		return $this->_request;
	}
}
