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

interface Yaf_View_Interface
{
	public function render( $view_path , $tpl_vars = NULL );
	public function display( $view_path , $tpl_vars = NULL );
	public function assign( $name , $value = NULL );
	public function setScriptPath( $view_directory );
	public function getScriptPath( );
}