<?php
abstract class Yaf_Abstract implements IteratorAggregate, ArrayAccess
{

	public function getIterator()
	{
		return new ArrayIterator($this);
	}

	public function offsetExists($offset)
	{
		return property_exists($this, $offset);
	}

	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}
}
