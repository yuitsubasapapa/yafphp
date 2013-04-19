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

class Yaf_View_Simple implements Yaf_View_Interface
{
	protected $_tpl_vars;
	protected $_script_path;

	public function __construct()
	{

	}

	public function __isset($name)
	{

	}

	public function get()
	{

	}
	
	public function assign($name, $value = null)
	{

	}

	public function render($view_path, $tpl_vars = null)
	{

	}

	public function xeval()
	{

	}
	
	public function display($view_path, $tpl_vars = null)
	{

	}

	public function assignRef($name, $value)
	{

	}

	public function clear()
	{

	}
	
	public function setScriptPath($view_directory)
	{

	}

	public function getScriptPath()
	{

	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function __set($name, $value = null)
	{
		return $this->assign($name, $value);
	}

}
