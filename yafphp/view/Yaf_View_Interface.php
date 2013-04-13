<?php
interface Yaf_View_Interface
{
	public function render( $view_path , $tpl_vars = NULL );
	public function display( $view_path , $tpl_vars = NULL );
	public function assign( $name , $value = NULL );
	public function setScriptPath( $view_directory );
	public function getScriptPath( );
}