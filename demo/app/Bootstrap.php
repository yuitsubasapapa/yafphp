<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{
/*
	public function _initRequest(Yod_Dispatcher $dispatcher) {
		$request = $dispatcher->getRequest();
		$request_uri = $request->getRequestUri();
		if(empty($request_uri)){
			$request->setBaseUri(dirname($_SERVER['PHP_SELF']));
			$request->setRequestUri($_SERVER['REQUEST_URI']);
		}
	}

	public function _initRoute(Yod_Dispatcher $dispatcher) {
		$router = Yod_Dispatcher::getInstance()->getRouter();
		$router->addConfig(Yaf_Registry::get('config')->routes);
	}

	public function _initDefaultName(Yod_Dispatcher $dispatcher) {
		$dispatcher->setDefaultModule('Index')
			->setDefaultController('Index')
			->setDefaultAction('index');
	}

	public function _initPlugin(Yod_Dispatcher $dispatcher) {
		$user = new UserPlugin();
		$dispatcher->registerPlugin($user);
	}

	public function _initRegistry(Yod_Dispatcher $dispatcher) {
		$config = Yaf_Registry::get('config');
		// params
		Yaf_Registry::set('params', $config->params);
	}

	public function _initMemcache(Yod_Dispatcher $dispatcher) {
		$memcache = new Memcache();
		$memcache->connect('localhost', 11211);
		// memcache
		Yaf_Registry::set('memcache', $memcache);
	}
*/
}