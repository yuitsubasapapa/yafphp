<?php
class Yaf_View_Simple extends Yaf_View_Interface {
	protected array _tpl_vars ;
	protected string _script_path ;
	
	public string render ( string $view_path ,
	array $tpl_vars = NULL );
	public boolean display ( string $view_path ,
	array $tpl_vars = NULL );
	public boolean setScriptPath ( string $view_directory );
	public string getScriptPath ( void );
	public boolean assign ( string $name ,
	mixed $value );
	public boolean __set ( string $name ,
	mixed $value = NULL );
	public mixed __get ( string $name );
}
