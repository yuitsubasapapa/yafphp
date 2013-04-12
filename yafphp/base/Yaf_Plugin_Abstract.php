<?php
abstract class Yaf_Plugin_Abstract
{
	public void routerStartup( Yaf_Request_Abstract $request ,
	Yaf_Response_Abstarct $response );
	public void routerShutdown( Yaf_Request_Abstract $request ,
	Yaf_Response_Abstarct $response );
	public void dispatchLoopStartup( Yaf_Request_Abstract $request ,
	Yaf_Response_Abstarct $response );
	public void preDispatch( Yaf_Request_Abstract $request ,
	Yaf_Response_Abstarct $response );
	public void postDispatch( Yaf_Request_Abstract $request ,
	Yaf_Response_Abstarct $response );
	public void dispatchLoopShutdown( Yaf_Request_Abstract $request ,
	Yaf_Response_Abstarct $response );
}
