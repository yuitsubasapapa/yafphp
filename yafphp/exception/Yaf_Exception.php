<?php
class Yaf_Exception extends Exception
{
	protected $message = '';
	protected $code = 0;

	private $_previous = NULL;

	public function __construct($message, $code = 0, $previous = NULL)
	{
		$this->message = $message;
		$this->code = $code;
		
		$this->_previous = $previous;
	}
	
	final public function getMessage()
	{
		return parent::getMessage();
	}
	
	final public function getCode()
	{
		return parent::getCode();
	}
	
	final public function getFile()
	{
		return parent::getFile();
	}
	
	final public function getLine()
	{
		return parent::getLine();
	}

	public final function getPrevious()
	{
		return $this->_previous;
	}
}
