<?php
abstract class Yaf_Config_Abstract implements Iterator, ArrayAccess, Countable
{
	protected $_config = array();
	protected $_readonly = true;
	public function get($name);
	public function __get ( string $name );
	public function __isset ( string $name );
	public function __set ( string|int $name,mixed $value );
	public function set ( string|int $name,mixed $value );
	public function count ( void );
	public function offsetGet ( string|int $name );
	public function offsetSet ( string|int $name ,mixed $value );
	public function offsetExists ( string|int $name );
	public function offsetUnset ( string|int $name );
	public function rewind ( void );
	public function key ( void );
	public function next ( void );
	public function current ( void );
	public function valid ( void );
	public function toArray ( void );
	public function readOnly ( void );
}
