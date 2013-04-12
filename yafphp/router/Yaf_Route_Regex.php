<?php
final class Yaf_Route_Regex implements Yaf_Route_Interface
{
	protected $_body = array();
	protected $_header = array();
	
	public function setBody ( $body, $name = NULL );
	public function prependBody ( $body, $name = NULL );
	public function appendBody ( $body, $name = NULL );
	public function clearBody ( void );
	public function getBody ( void );
	public function response ( void );
	public function setRedirect ( string $url );
	public function __toString ( void );
}
