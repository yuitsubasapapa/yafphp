<?php
class IndexController extends Yaf_Controller_Abstract
{
	public function indexAction()
	{
		$this->_view->assign('yafphp', 'yafphp <sup>[ Yaf PHP Framework ]</sup>');
	}
}