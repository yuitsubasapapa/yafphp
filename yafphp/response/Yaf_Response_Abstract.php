<?php
// +----------------------------------------------------------------------
// | yafphp [ Yaf PHP Framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://yafphp.duapp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zmrnet <zmrnet@qq.com>
// +----------------------------------------------------------------------

abstract class Yaf_Response_Abstract
{
	protected $_body = array();
	protected $_header = array();
	
	public function setBody ( $body, $name = NULL )
	{

	}

	public function prependBody ( $body, $name = NULL )
	{

	}

	public function appendBody ( $body, $name = NULL )
	{

	}

	public function clearBody ()
	{

	}

	public function getBody ()
	{

	}

	public function response ()
	{

	}

	public function setRedirect ($url )
	{

	}

	public function __toString ()
	{

	}

}
