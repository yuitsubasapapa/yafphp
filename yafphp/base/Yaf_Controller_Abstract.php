<?php
abstract class Yaf_Controller_Abstract
{
	protected array actions ;
	protected Yaf_Request_Abstract _request ;
	protected Yaf_Response_Abstract _response ;
	protected Yaf_View_Interface _view ;
	protected string _script_path ;
	
	private void __construct ( void );
	public void init ( void );
	public string getModuleName ( void );
	public Yaf_Request_Abstract getRequest ( void );
	public Yaf_Response_Abstract getResponse ( void );
	public Yaf_View_Interface getView ( void );
	public Yaf_View_Interface initView ( void );
	public boolean setViewPath ( string $view_directory );
	public string getViewPath ( void );
	public Yaf_Response_Abstract render ( string $action_name ,
	array $tpl_vars = NULL );
	public boolean display ( string $action_name ,
	array $tpl_vars = NULL );
	public boolean forward ( string $action ,
	array $invoke_args = NULL );
	public boolean forward ( string $controller ,
	string $action ,
	array $invoke_args = NULL );
	public boolean forward ( string $module ,
	string $controller ,
	string $action ,
	array $invoke_args = NULL );
	public boolean redirect ( string $url );
}
