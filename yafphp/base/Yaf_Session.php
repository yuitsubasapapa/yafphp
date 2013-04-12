<?php
final Yaf_Session implements Iterator , ArrayAccess , Countable
{
	public static Yaf_Session getInstance ( void );
	public Yaf_Session start ( void );
	public mixed get ( string $name = NULL );
	public boolean set ( string $name ,
	mixed $value );
	public mixed __get ( string $name );
	public boolean __set ( string $name ,
	mixed $value );
	public boolean has ( string $name );
	public boolean del ( string $name );
	public boolean __isset ( string $name );
	public boolean __unset ( string $name );
}