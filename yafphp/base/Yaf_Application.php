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

final class Yaf_Application
{
	protected static $_app = NULL;

	protected $_config = NULL;
	protected $_dispatcher;
	protected $_environ = YAF_ENVIRON;
	protected $_modules = array();

	protected $_run = FALSE;

	/**
	 * __construct
	 *
	 */
	public function __construct($config, $section = YAF_ENVIRON)
	{
		if (empty($config)) return;

		if (!is_null(self::$_app)) {
			unset($this);
			throw new Yaf_Exception_StartupError('Only one application can be initialized');
			return;
		}

		if (is_string($section) && strlen($section)) {
			$this->_environ = $section;
		}

		if (is_string($config)) {
			$this->_config = new Yaf_Config_Ini($config, $section);
		}
		if (is_array($config)) {
			$this->_config = new Yaf_Config_Simple($config, true);
		}

		if (is_null($this->_config)
				|| !is_object($this->_config)
				|| !($this->_config instanceof Yaf_Config_Abstract)
				|| self::_parse_option($this->_config) == FALSE) {
			unset($this);
			throw new Yaf_Exception_StartupError('Initialization of application config failed');
			return;
		}

		$request = new Yaf_Request_Http(null, $base_uri);

		self::$_app = &$this;
	}

	/**
	 * bootstrap
	 *
	 */
	public function bootstrap()
	{
		return $this;
	}

	/**
	 * run
	 *
	 */
	public function run()
	{
		return $this;
	}

	/**
	 * getConfig
	 *
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * getDispatcher
	 *
	 */
	public function getDispatcher()
	{
		return $this->_dispatcher;
	}

	/**
	 * geModules
	 *
	 */
	public function geModules()
	{
		return $this->_modules;
	}

	/**
	 * app
	 *
	 */
	public static function app()
	{
		return self::$_app;
	}

	/**
	 * environ
	 *
	 */
	public function environ()
	{

	}

	/**
	 * execute
	 *
	 */
	public function execute($funcion, $parameter = NULL)
	{

	}

	/**
	 * yaf_application_parse_option
	 *
	 */
	private static function _parse_option($config)
	{
		if (!isset($config['application'])) {
			/* For back compatibilty */
			if (!isset($config['yaf'])) {
				throw new Yaf_Exception_TypeError('Expected an array of application configure');
				return FALSE;
			}
		}

		$app = isset($config['application']) ? $config['application'] : $config['yaf'];
		if (!($app instanceof Yaf_Config_Abstract)) {
			throw new Yaf_Exception_TypeError('Expected an array of application configure');
			return FALSE;
		}

/*
app = *ppzval;
	if (Z_TYPE_P(app) != IS_ARRAY) {
		yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "%s", "Expected an array of application configure");
		return FAILURE;
	}

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("directory"), (void **)&ppzval) == FAILURE
			|| Z_TYPE_PP(ppzval) != IS_STRING) {
		yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "%s", "Expected a directory entry in application configures");
		return FAILURE;
	}

	if (*(Z_STRVAL_PP(ppzval) + Z_STRLEN_PP(ppzval) - 1) == DEFAULT_SLASH) {
		YAF_G(directory) = estrndup(Z_STRVAL_PP(ppzval), Z_STRLEN_PP(ppzval) - 1);
	} else {
		YAF_G(directory) = estrndup(Z_STRVAL_PP(ppzval), Z_STRLEN_PP(ppzval));
	}

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("ext"), (void **)&ppzval) == SUCCESS
			&& Z_TYPE_PP(ppzval) == IS_STRING) {
		YAF_G(ext) = estrndup(Z_STRVAL_PP(ppzval), Z_STRLEN_PP(ppzval));
	} else {
		YAF_G(ext) = YAF_DEFAULT_EXT;
	}

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("bootstrap"), (void **)&ppzval) == SUCCESS
			&& Z_TYPE_PP(ppzval) == IS_STRING) {
		YAF_G(bootstrap) = estrndup(Z_STRVAL_PP(ppzval), Z_STRLEN_PP(ppzval));
	}

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("library"), (void **)&ppzval) == SUCCESS) {
		if (IS_STRING == Z_TYPE_PP(ppzval)) {
			YAF_G(local_library) = estrndup(Z_STRVAL_PP(ppzval), Z_STRLEN_PP(ppzval));
		} else if (IS_ARRAY == Z_TYPE_PP(ppzval)) {
			if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("directory"), (void **)&ppsval) == SUCCESS
					&& Z_TYPE_PP(ppsval) == IS_STRING) {
				YAF_G(local_library) = estrndup(Z_STRVAL_PP(ppsval), Z_STRLEN_PP(ppsval));
			}
			if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("namespace"), (void **)&ppsval) == SUCCESS
					&& Z_TYPE_PP(ppsval) == IS_STRING) {
				uint i, len;
				char *src = Z_STRVAL_PP(ppsval);
				if (Z_STRLEN_PP(ppsval)) {
				    char *target = emalloc(Z_STRLEN_PP(ppsval) + 1);
					len = 0;
					for(i=0; i<Z_STRLEN_PP(ppsval); i++) {
						if (src[i] == ',') {
							target[len++] = DEFAULT_DIR_SEPARATOR;
						} else if (src[i] != ' ') {
                            target[len++] = src[i];
						}
					}
					target[len] = '\0';
					yaf_loader_register_namespace_single(target, len TSRMLS_CC);
					efree(target);
				}
			}
		}
	}

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("view"), (void **)&ppzval) == FAILURE 
			|| Z_TYPE_PP(ppzval) != IS_ARRAY) {
		YAF_G(view_ext) = YAF_DEFAULT_VIEW_EXT;
	} else {
		if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("ext"), (void **)&ppsval) == FAILURE
				|| Z_TYPE_PP(ppsval) != IS_STRING) {
			YAF_G(view_ext) = YAF_DEFAULT_VIEW_EXT;
		} else {
			YAF_G(view_ext) = estrndup(Z_STRVAL_PP(ppsval), Z_STRLEN_PP(ppsval));
		}
	}

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("baseUri"), (void **)&ppzval) == SUCCESS
			&& Z_TYPE_PP(ppzval) == IS_STRING) {
		YAF_G(base_uri) = estrndup(Z_STRVAL_PP(ppzval), Z_STRLEN_PP(ppzval));
	}

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("dispatcher"), (void **)&ppzval) == FAILURE
			|| Z_TYPE_PP(ppzval) != IS_ARRAY) {
		YAF_G(default_module) = YAF_ROUTER_DEFAULT_MODULE;
		YAF_G(default_controller) = YAF_ROUTER_DEFAULT_CONTROLLER;
		YAF_G(default_action)  = YAF_ROUTER_DEFAULT_ACTION;
	} else {
		if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("defaultModule"), (void **)&ppsval) == FAILURE
				|| Z_TYPE_PP(ppsval) != IS_STRING) {
			YAF_G(default_module) = YAF_ROUTER_DEFAULT_MODULE;
		} else {
			YAF_G(default_module) = zend_str_tolower_dup(Z_STRVAL_PP(ppsval), Z_STRLEN_PP(ppsval));
			*(YAF_G(default_module)) = toupper(*YAF_G(default_module));
		}

		if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("defaultController"), (void **)&ppsval) == FAILURE
				|| Z_TYPE_PP(ppsval) != IS_STRING) {
			YAF_G(default_controller) = YAF_ROUTER_DEFAULT_CONTROLLER;
		} else {
			YAF_G(default_controller) = zend_str_tolower_dup(Z_STRVAL_PP(ppsval), Z_STRLEN_PP(ppsval));
			*(YAF_G(default_controller)) = toupper(*YAF_G(default_controller));
		}

		if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("defaultAction"), (void **)&ppsval) == FAILURE
				|| Z_TYPE_PP(ppsval) != IS_STRING) {
			YAF_G(default_action)	  = YAF_ROUTER_DEFAULT_ACTION;
		} else {
			YAF_G(default_action) = zend_str_tolower_dup(Z_STRVAL_PP(ppsval), Z_STRLEN_PP(ppsval));
		}

		if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("throwException"), (void **)&ppsval) == SUCCESS) {
			zval *tmp = *ppsval;
			Z_ADDREF_P(tmp);
			convert_to_boolean_ex(&tmp);
			YAF_G(throw_exception) = Z_BVAL_P(tmp);
			zval_ptr_dtor(&tmp);
		}

		if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("catchException"), (void **)&ppsval) == SUCCESS) {
			zval *tmp = *ppsval;
			Z_ADDREF_P(tmp);
			convert_to_boolean_ex(&tmp);
			YAF_G(catch_exception) = Z_BVAL_P(tmp);
			zval_ptr_dtor(&tmp);
		}

		if (zend_hash_find(Z_ARRVAL_PP(ppzval), ZEND_STRS("defaultRoute"), (void **)&ppsval) == SUCCESS
				&& Z_TYPE_PP(ppsval) == IS_ARRAY) {
			YAF_G(default_route) = *ppsval;
		}
	}

	do {
		char *ptrptr;
		zval *module, *zmodules;

		MAKE_STD_ZVAL(zmodules);
		array_init(zmodules);
		if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("modules"), (void **)&ppzval) == SUCCESS
				&& Z_TYPE_PP(ppzval) == IS_STRING && Z_STRLEN_PP(ppzval)) {
			char *seg, *modules;
			modules = estrndup(Z_STRVAL_PP(ppzval), Z_STRLEN_PP(ppzval));
			seg = php_strtok_r(modules, ",", &ptrptr);
			while(seg) {
				if (seg && strlen(seg)) {
					MAKE_STD_ZVAL(module);
					ZVAL_STRINGL(module, seg, strlen(seg), 1);
					zend_hash_next_index_insert(Z_ARRVAL_P(zmodules),
							(void **)&module, sizeof(zval *), NULL);
				}
				seg = php_strtok_r(NULL, ",", &ptrptr);
			}
			efree(modules);
		} else {
			MAKE_STD_ZVAL(module);
			ZVAL_STRING(module, YAF_G(default_module), 1);
			zend_hash_next_index_insert(Z_ARRVAL_P(zmodules), (void **)&module, sizeof(zval *), NULL);
		}
		YAF_G(modules) = zmodules;
	} while (0);

	if (zend_hash_find(Z_ARRVAL_P(app), ZEND_STRS("system"), (void **)&ppzval) == SUCCESS && Z_TYPE_PP(ppzval) == IS_ARRAY) {
		zval **value;
		char *key, name[128];
		HashTable *ht = Z_ARRVAL_PP(ppzval);

		for(zend_hash_internal_pointer_reset(ht);
				zend_hash_has_more_elements(ht) == SUCCESS;
				zend_hash_move_forward(ht)) {
			uint len;
			ulong idx;
			if (zend_hash_get_current_key_ex(ht, &key, &len, &idx, 0, NULL) != HASH_KEY_IS_STRING) {
				continue;
			}

			if (zend_hash_get_current_data(ht, (void **)&value) == FAILURE) {
				continue;
			}
			len = snprintf(name, sizeof(name), "%s.%s", "yaf", key);
			if (len > sizeof(name) -1) {
				len = sizeof(name) - 1;
			}
			convert_to_string(*value);
			zend_alter_ini_entry(name, len + 1, Z_STRVAL_PP(value), Z_STRLEN_PP(value), PHP_INI_USER, PHP_INI_STAGE_RUNTIME);
		}
	}
*/
		return TRUE;
	}

/*
	public function __construct($config)
	{
		Yaf::setApplication($this);

		if(is_string($config))
			$config = require($config);

		$this->configure($config);
		$this->_dispatcher = Yaf_Dispatcher::getInstance();
		Yaf::setInclude('app.library', $this->_library);

	}

	public function configure($config)
	{
		if(isset($config['application']))
		{
			if(is_array($config['application']))
			{
				foreach($config['application'] as $key => $value)
					$this->{'_' . $key} = $value;
			}
			unset($config['application']);
		}

		if(is_array($config))
		{
			foreach($config as $key => $value)
				$this->$key = $value;
		}
	}

	public function bootstrap($bootstrap = null)
	{
		if($bootstrap instanceof Yaf_Bootstrap_Abstract){
			$bootmethods = get_class_methods($bootstrap);
		}elseif(is_file($this->_bootstrap)){
			require($this->_bootstrap);
			$bootstrap = new Bootstrap();
			$bootmethods = get_class_methods($bootstrap);
		}
		if(is_array($bootmethods)){
			foreach($bootmethods as $method){
				if(substr($method, 0, 5) == '_init' && method_exists($bootstrap, $method))
					$bootstrap->$method($this->_dispatcher);
			}
			unset($bootstrap);
		}

		return $this;
	}

	public function run()
	{
		return $this;
	}
	*/
}