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

final class Yaf_Request_Http extends Yaf_Request_Abstract
{
	/**
	 * __construct
	 *
	 */
	public function __construct($request_uri = null, $base_uri = null)
	{

	}

	/**
	 * getLanguage
	 *
	 */
	public function getLanguage()
	{

	}

	/**
	 * getQuery
	 *
	 */
	public function getQuery($name = null)
	{

	}

	/**
	 * getPost
	 *
	 */
	public function getPost($name = null)
	{

	}

	/**
	 * getEnv
	 *
	 */
	public function getEnv($name = null)
	{

	}

	/**
	 * getServer
	 *
	 */
	public function getServer($name = null)
	{
		
	}

	/**
	 * getCookie
	 *
	 */
	public function getCookie($name = null)
	{

	}

	/**
	 * getFiles
	 *
	 */
	public function getFiles($name = null)
	{

	}

	/**
	 * isGet
	 *
	 */
	public function isGet()
	{
		return (strtoupper($this->_method) == 'GET');
	}

	/**
	 * isPost
	 *
	 */	
	public function isPost()
	{
		return (strtoupper($this->_method) == 'POST');
	}

	/**
	 * isHead
	 *
	 */	
	public function isHead()
	{
		return (strtoupper($this->_method) == 'HEAD');
	}

	/**
	 * isXmlHttpRequest
	 *
	 */	
	public function isXmlHttpRequest()
	{
		return false;
	}

	/**
	 * isPut
	 *
	 */	
	public function isPut()
	{
		return (strtoupper($this->_method) == 'PUT');
	}

	/**
	 * isDelete
	 *
	 */
	public function isDelete()
	{
		return (strtoupper($this->_method) == 'DELETE');
	}

	/**
	 * isOptions
	 *
	 */	
	public function isOptions()
	{
		return (strtoupper($this->_method) == 'OPTIONS');
	}

	/**
	 * isCli
	 *
	 */	
	public function isCli()
	{
		return (strtoupper($this->_method) == 'CLI');
	}

	/**
	 * getBaseUri
	 *
	 */	
	public function getBaseUri()
	{
		return $this->_base_uri;
	}

	/**
	 * setBaseUri
	 *
	 */	
	public function setBaseUri($base_uri)
	{
		if (is_string($base_uri) && strlen($base_uri)) {
/*
				char *basename = NULL;
	uint basename_len = 0;
	zval *container = NULL;

	if (!base_uri) {
		zval 	*script_filename;
		char 	*file_name, *ext = YAF_G(ext);
		size_t 	file_name_len;
		uint  	ext_len;

		ext_len	= strlen(ext);

		script_filename = yaf_request_query(YAF_GLOBAL_VARS_SERVER, ZEND_STRL("SCRIPT_FILENAME") TSRMLS_CC);

		do {
			if (script_filename && IS_STRING == Z_TYPE_P(script_filename)) {
				zval *script_name, *phpself_name, *orig_name;

				script_name = yaf_request_query(YAF_GLOBAL_VARS_SERVER, ZEND_STRL("SCRIPT_NAME") TSRMLS_CC);
				php_basename(Z_STRVAL_P(script_filename), Z_STRLEN_P(script_filename), ext, ext_len, &file_name, &file_name_len TSRMLS_CC);
				if (script_name && IS_STRING == Z_TYPE_P(script_name)) {
					char 	*script;
					size_t 	script_len;

					php_basename(Z_STRVAL_P(script_name), Z_STRLEN_P(script_name),
							NULL, 0, &script, &script_len TSRMLS_CC);

					if (strncmp(file_name, script, file_name_len) == 0) {
						basename 	 = Z_STRVAL_P(script_name);
						basename_len = Z_STRLEN_P(script_name);
						container = script_name;
						efree(file_name);
						efree(script);
						break;
					}
					efree(script);
				}
				zval_ptr_dtor(&script_name);

				phpself_name = yaf_request_query(YAF_GLOBAL_VARS_SERVER, ZEND_STRL("PHP_SELF") TSRMLS_CC);
				if (phpself_name && IS_STRING == Z_TYPE_P(phpself_name)) {
					char 	*phpself;
					size_t	phpself_len;

					php_basename(Z_STRVAL_P(phpself_name), Z_STRLEN_P(phpself_name), NULL, 0, &phpself, &phpself_len TSRMLS_CC);
					if (strncmp(file_name, phpself, file_name_len) == 0) {
						basename	 = Z_STRVAL_P(phpself_name);
						basename_len = Z_STRLEN_P(phpself_name);
						container = phpself_name;
						efree(file_name);
						efree(phpself);
						break;
					}
					efree(phpself);
				}
				zval_ptr_dtor(&phpself_name);

				orig_name = yaf_request_query(YAF_GLOBAL_VARS_SERVER, ZEND_STRL("ORIG_SCRIPT_NAME") TSRMLS_CC);
				if (orig_name && IS_STRING == Z_TYPE_P(orig_name)) {
					char 	*orig;
					size_t	orig_len;
					php_basename(Z_STRVAL_P(orig_name), Z_STRLEN_P(orig_name), NULL, 0, &orig, &orig_len TSRMLS_CC);
					if (strncmp(file_name, orig, file_name_len) == 0) {
						basename 	 = Z_STRVAL_P(orig_name);
						basename_len = Z_STRLEN_P(orig_name);
						container = orig_name;
						efree(file_name);
						efree(orig);
						break;
					}
					efree(orig);
				}
				zval_ptr_dtor(&orig_name);
				efree(file_name);
			}
		} while (0);
		zval_ptr_dtor(&script_filename);

		if (basename && strstr(request_uri, basename) == request_uri) {
			if (*(basename + basename_len - 1) == '/') {
				--basename_len;
			}
			zend_update_property_stringl(yaf_request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_BASE), basename, basename_len TSRMLS_CC);
			if (container) {
				zval_ptr_dtor(&container);
			}

			return 1;
		} else if (basename) {
			size_t  dir_len;
			char 	*dir = estrndup(basename, basename_len); /* php_dirname might alter the string * /

			dir_len = php_dirname(dir, basename_len);
			if (*(basename + dir_len - 1) == '/') {
				--dir_len;
			}

			if (dir_len) {
				if (strstr(request_uri, dir) == request_uri) {
					zend_update_property_string(yaf_request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_BASE), dir TSRMLS_CC);
					efree(dir);

					if (container) {
						zval_ptr_dtor(&container);
					}
					return 1;
				}
			}
			efree(dir);
		}

		if (container) {
			zval_ptr_dtor(&container);
		}

		zend_update_property_string(yaf_request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_BASE), "" TSRMLS_CC);
		return 1;
	} else {
		zend_update_property_string(yaf_request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_BASE), base_uri TSRMLS_CC);
		return 1;
	}
*/
			return $this;
		}

		return false;
	}

	/**
	 * getRequestUri
	 *
	 */	
	public function getRequestUri()
	{
		return $this->_request_uri;
	}
	
}
