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

final class Yaf_Config_Ini extends Yaf_Config_Abstract
{
	/**
	 * __construct
	 *
	 */
	public function __construct($config, $section = YAF_ENVIRON)
	{
		if(is_array($config)){
			$this->_config = $config;
		}elseif(is_string($config)){
			if(file_exists($config)){
				if(is_file($config)){
					$this->_config = self::_parser_cb($config, $section);
					if($this->_config == FALSE || !is_array($this->_config)){
						throw new Exception('Parsing ini file '. $config .' failed', E_ERROR);
						return;
					}
				}else{
					throw new Exception('Argument is not a valid ini file '. $config, E_ERROR);
					return;
				}
			}else{
				throw new Exception('Unable to find config file '. $config, E_ERROR);
				return;
			}
		}else{
			throw new Exception('Invalid parameters provided, must be path of ini file', E_ERROR);
			return;
		}
	}
	
	/**
	 * get
	 *
	 */
	public function get($name = NULL)
	{
		if(is_null($name)) return $this;
		
		if($seg = strtok($name, '.')){
			$value = $this->_config;
			while($seg){
				if(!isset($value[$seg])) return;
				$value = $value[$seg];
				$seg = strtok('.');
			}
			if(is_array($value)){
				return new self($value);
			}else{
				return $value;
			}
		}
	}

	/**
	 * __set
	 *
	 */
	public function set($name, $value)
	{
		return;
	}

	/**
	 * ArrayAccess:: offsetUnset
	 *
	 */
	public function offsetUnset($name)
	{
		return;
	}


	/**
	 * Iterator::current
	 *
	 */
	public function current()
	{
		$value = current($this->_config);
		if(is_array($value)){
			return new self($value);
		}else{
			return $value;
		}
	}

	/**
	 * readOnly
	 *
	 */
	public function readOnly()
	{
		return true;
	}

	/**
	 * yaf_config_ini_parser_cb
	 *
	 */
	private static function _parser_cb($filepath, $section){
		$config = parse_ini_file($filepath, true);
		if($config && is_array($config)){
			foreach($config as $key => $value){
				if($seg = ltrim(strchr($key, ':'), ': ')){
					while($token = ltrim(strrchr($seg, ':'), ': ')){
						if(isset($config[$token])){
							$value = array_merge($config[$token], $value);
						}
						$seg = substr($seg, 0, -strlen($token));
						$seg = rtrim($seg, ': ');
					}

					$token = rtrim($seg, ': ');
					if(isset($config[$token])){
						$value = array_merge($config[$token], $value);
					}

					if($key = trim(strtok($key, ':'))){
						$config[$key] = $value;
					}
				}

				if(trim($key) == $section){
					return self::_simple_parser_cb($value);
				}
			}
		}

		return false;
	}

	/**
	 * yaf_config_ini_simple_parser_cb
	 *
	 */
	private static function _simple_parser_cb($simple){
		if(!is_array($simple)) return;
		
		foreach($simple as $key => $value){
			if($seg = strtok($key, '.')){
				if($subkey = ltrim(strchr($key, '.'), '.')){
					$value = array($subkey => $value);
					if(isset($simple[$seg]) && is_array($simple[$seg])){
						$value = array_merge($simple[$seg], $value);
					}
					$simple[$seg] = self::_simple_parser_cb($value);
					unset($simple[$key]);
				}
			}
		}

		return $simple;
	}
}
