<?php
// +----------------------------------------------------------------------
// | yafphp [ Yaf PHP Framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://yafphp.duapp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: baoqiang <zmrnet@qq.com>
// +----------------------------------------------------------------------

namespace Yaf;

use \Iterator as Iterator;
use \ArrayAccess as ArrayAccess;
use \Countable as Countable;

abstract class Config_Abstract implements Iterator, ArrayAccess, Countable
{
	protected $_config = array();
	protected $_readonly = true;

	/**
	 * get
	 *
	 * @param string $name
	 */
	abstract public function get($name = null);

	/**
	 * set
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	abstract public function set($name, $value);

	/**
	 * toArray
	 *
	 * @param void
	 */
	abstract public function toArray();

	/**
	 * readOnly
	 *
	 * @param void
	 */
	abstract public function readOnly();

}
