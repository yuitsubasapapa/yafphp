<?php
interface Yaf_View_Interface
{
	public string render( string $view_path ,
	array $tpl_vars = NULL );
	public boolean display( string $view_path ,
	array $tpl_vars = NULL );
	public boolean assign( mixed $name ,
	mixed $value = NULL );
	public boolean setScriptPath( string $view_directory );
	public string getScriptPath( void );
}