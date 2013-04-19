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

interface Yaf_View_Interface
{
	/**
	 * assign
	 * 
	 * @param string | array $name
	 * @param mixed $value
	 */
	public function assign($name, $value = null);

	/**
	 * display
	 * 
	 * @param string $view_path
	 * @param array $tpl_vars
	 */
	public function display($view_path, $tpl_vars = null);

	/**
	 * render
	 * 
	 * @param string $view_path
	 * @param array $tpl_vars
	 */
	public function render($view_path, $tpl_vars = null);

	/**
	 * setScriptPath
	 * 
	 * @param string $view_directory
	 */
	public function setScriptPath($view_directory);

	/**
	 * getScriptPath
	 * 
	 * @param void
	 */
	public function getScriptPath();
	
}
