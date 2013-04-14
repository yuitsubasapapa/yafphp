<?php
return array(
	// application
	'application' => array(
		'directory' => dirname(__FILE__),
		'dispatcher' => array(
			'catchException' => 1,
		),
		'modules' => 'Admin,Index',
	),
	// routes
	'routes' => array(
		'error' => array(
			'type' => 'rewrite',
			'match' => '.+',
			'route' => array(
				'module' => 'Index',
				'controller' => 'Error',
				'action' => 'error',
			),
		),
		'detail' => array(
			'type' => 'regex',
			'match' => '#^/detail-(\d+).html$#',
			'route' => array(
				'module' => 'Index',
				'controller' => 'Detail',
				'action' => 'index',
			),
			'map' => array(
				1 => 'aid',
			),
		),
		'catego' => array(
			'type' => 'regex',
			'match' => '#^/catego-(\d+)-(\d+).html$#',
			'route' => array(
				'module' => 'Index',
				'controller' => 'Catego',
				'action' => 'index',
			),
			'map' => array(
				1 => 'cid',
				2 => 'pn',
			),
		),
		'search' => array(
			'type' => 'regex',
			'match' => '#^/search#',
			'route' => array(
				'module' => 'Index',
				'controller' => 'Search',
				'action' => 'index',
			),
			'map' => array(
			),
		),
		'index' => array(
			'type' => 'regex',
			'match' => '#^/index.html$#',
			'route' => array(
				'module' => 'Index',
				'controller' => 'Index',
				'action' => 'index',
			),
			'map' => array(
				
			),
		),
	),
	// pdodb
	'pdodb' => array(
		'down' => array(
			'dsn' => 'mysql:host=localhost;dbname=down',
			'username' => 'down',
			'password' => 'down@srccn.com',
		),
		'news' => array(
			'dsn' => 'mysql:host=localhost;dbname=news',
			'username' => 'news',
			'password' => 'news@srccn.com',
		),
		'site' => array(
			'dsn' => 'mysql:host=localhost;dbname=site',
			'username' => 'site',
			'password' => 'site@srccn.com',
		),
	),
	// params
	'params' => array(
		'skindir' => 'http://css.srccn.com/news/',
	),
);
