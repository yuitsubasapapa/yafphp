<?php
abstract class Yaf_Controller_Abstract
{
	protected $actions ;
	protected $_request ;
	protected $_response ;
	protected $_view ;
	protected $_script_path ;
	
	private function __construct ( )
	{

	}

	public function init ( )
	{

	}

	public function getModuleName ( )
	{

	}

	public function getRequest ( )
	{

	}

	public function getResponse ( )
	{

	}

	public function getView ( )
	{

	}

	public function initView ( )
	{

	}

	public function setViewPath ( $view_directory )
	{

	}

	public function getViewPath ( )
	{

	}

	public function render ( $action_name , $tpl_vars = NULL )
	{

	}

	public function display ( $action_name , $tpl_vars = NULL )
	{

	}

	public function forward ( $action , $invoke_args = NULL )
	{

	}
/*
	public function forward ( $controller , $action , $invoke_args = NULL )
	{

	}

	public function forward ( $module , $controller , $action , $invoke_args = NULL )
	{

	}
*/
	public function redirect ( $url )
	{

	}

}