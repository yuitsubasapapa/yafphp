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

class Yaf_View_Simple implements Yaf_View_Interface
{
	protected $_tpl_vars;
	protected $_tpl_dir;
	protected $_options;

	/**
	 * __construct
	 *
	 * @param string $tpl_dir
	 * @param array $options
	 */
	public function __construct($tpl_dir, $options = null)
	{
		$this->_tpl_vars = array();

		if ($tpl_dir && is_string($tpl_dir)) {
			if ($tpl_dir = realpath($tpl_dir)) {
				$this->_tpl_dir = $tpl_dir;
			} else {
				throw new Yaf_Exception_TypeError('Expects an absolute path for templates directory');
				return false;
			}
		}
	}

	/**
	 * __isset
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		if (is_array($this->_tpl_vars)) {
			return isset($this->_tpl_vars[$name]);
		}
		return false;
	}

	/**
	 * get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name = null)
	{
		if ($this->_tpl_vars && is_array($this->_tpl_vars)) {
			if (is_null($name)) {
				if (isset($this->_tpl_vars[$name])) {
					return $this->_tpl_vars[$name];
				}
			} else {
				return $this->_tpl_vars;
			}
		}
		return null;
	}
	
	/**
	 * assign
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function assign($name, $value = null)
	{
		$num_args = func_num_args();

		if ($num_args == 1) {
			if (is_array($name)) {
				$this->_tpl_vars = array_merge($this->_tpl_vars, $name);
				return true;
			}
		} elseif ($num_args == 2) {
			$this->_tpl_vars[$name] = $value;
			return true;
		}

		return false;
	}

	/**
	 * render
	 *
	 * @param string $tpl_file
	 * @param array $tpl_vars
	 * @return boolean | string
	 */
	public function render($tpl_file, $tpl_vars = null)
	{
		// yaf_view_simple_extract
		if (is_array($this->_tpl_vars)) {
			foreach ($this->_tpl_vars as $key => $value) {
				if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
					unset($this->_tpl_vars[$key]);
					continue;
				}
			}
			extract($this->_tpl_vars);
		}
		
		if (is_array($tpl_vars)) {
			foreach ($tpl_vars as $key => $value) {
				if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
					unset($tpl_vars[$key]);
					continue;
				}
			}
			extract($tpl_vars);
		}

		// short_tags
		$short_open_tag = ini_get('short_open_tag');
		if (!is_array($this->_options)
				|| !isset($this->_options['short_tags'])
				|| $this->_options['short_tags'] == true) {
			ini_set('short_open_tag', 1);
		}

		// ob_start
		if (!ob_start()) {
			trigger_error('failed to create buffer', E_USER_WARNING);
			return false;
		}

		if ($tpl_path = realpath($tpl_file)) {
			if (Yaf_loader::import($tpl_path) == false) {
				ob_end_clean();
				throw new Yaf_Exception_LoadFailed_View('Failed opening template ' . $tpl_path . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		} else {
			if (!is_string($this->_tpl_dir)) {
				ob_end_clean();
				throw new Yaf_Exception_LoadFailed_View('Could not determine the view script path, you should call Yaf_View_Simple::setScriptPath to specific it');
				return false;
			} else {
				$tpl_path = $this->_tpl_dir . '/' . $tpl_file;
			}

			if (Yaf_loader::import($tpl_path) == false) {
				ob_end_clean();
				throw new Yaf_Exception_LoadFailed_View('Failed opening template ' . $tpl_path . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		}

		ini_set('short_open_tag', $short_open_tag);

		if (($content = ob_get_contents()) === false) {
			trigger_error('Unable to fetch ob content', E_USER_WARNING);
			return false;
		}

		if (!ob_end_clean()) {
			return false;
		}

		return $content;
	}

	/**
	 * evals
	 *
	 * @param string $tpl_content
	 * @param array $tpl_vars
	 * @param boolean | string
	 */
	public function evals($tpl_content, $tpl_vars = null)
	{
		return false;
	}
	
	/**
	 * display
	 *
	 * @param string $tpl_file
	 * @param array $tpl_vars
	 * @return boolean | string
	 */
	public function display($tpl_file, $tpl_vars = null)
	{
		// yaf_view_simple_extract
		foreach ($this->_tpl_vars as $key => $value) {
			if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
				unset($this->_tpl_vars[$key]);
				continue;
			}
		}
		extract($this->_tpl_vars);
		foreach ($tpl_vars as $key => $value) {
			if (strtoupper($key) == 'GLOBALS' || strtolower($key) == 'this') {
				unset($tpl_vars[$key]);
				continue;
			}
		}
		extract($tpl_vars);

		// short_tags
		$short_open_tag = ini_get('short_open_tag');
		if (!is_array($this->_options)
				|| !isset($this->_options['short_tags'])
				|| $this->_options['short_tags'] == true) {
			ini_set('short_open_tag', 1);
		}

		if ($tpl_path = realpath($tpl_file)) {
			if (Yaf_loader::import($tpl_path) == false) {
				throw new Yaf_Exception_LoadFailed_View('Failed opening template ' . $tpl_path . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		} else {
			if (!is_string($this->_tpl_dir)) {
				throw new Yaf_Exception_LoadFailed_View('Could not determine the view script path, you should call Yaf_View_Simple::setScriptPath to specific it');
				return false;
			} else {
				$tpl_path = $this->_tpl_dir . '/' . $tpl_file;
			}

			if (Yaf_loader::import($tpl_path) == false) {
				throw new Yaf_Exception_LoadFailed_View('Failed opening template ' . $tpl_path . ':' . YAF_ERR_NOTFOUND_VIEW);
				return false;
			}
		}

		ini_set('short_open_tag', $short_open_tag);

		return true;
	}

	/**
	 * assignRef
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function assignRef($name, &$value)
	{
		$this->_tpl_vars[$name] = $value;
		return true;
	}

	/**
	 * assignRef
	 *
	 * @param string $name
	 * @return boolean | Yaf_View_Simple
	 */
	public function clear($name = null)
	{
		if ($this->_tpl_vars && is_array($this->_tpl_vars)) {
			if (is_null($name)) {
				$this->_tpl_vars = array();
			} else {
				unset($this->_tpl_vars[$name]);
			}
		}
		return $this;
	}
	
	/**
	 * setScriptPath
	 *
	 * @param string $tpl_dir
	 * @return boolean | Yaf_View_Simple
	 */
	public function setScriptPath($tpl_dir)
	{
		if (is_string($tpl_dir) && ($tpl_dir = realpath($tpl_dir))) {
			$this->_tpl_dir = $tpl_dir;
			return $this;
		}
		return false;
	}

	/**
	 * getScriptPath
	 *
	 * @param void
	 * @return string
	 */
	public function getScriptPath()
	{
		if (!is_string($this->_tpl_dir) && YAF_G('view_directory')) {
			return YAF_G('view_directory');
		}

		return $this->_tpl_dir;
	}

	/**
	 * __get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * __set
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function __set($name, $value = null)
	{
		return $this->assign($name, $value);
	}

}
