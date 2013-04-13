<?php
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
					$this->_config = parse_ini_file($config, true);
					if($this->_config == FALSE || !is_array($this->_config)){
						throw new Exception('Parsing ini file '. $config .' failed');
						return;
					}
				}else{
					throw new Exception('Argument is not a valid ini file '. $config);
					return;
				}
			}else{
				throw new Exception('Unable to find config file '. $config);
				return;
			}
		}else{
			throw new Exception('Invalid parameters provided, must be path of ini file');
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
				if(!isset($value[$name])) return;
				$value = $value[$name];
				$seg = strtok('.');
			}
		}else{
			if(!isset($this->_config[$name])) return;
			$value = $this->_config[$name];
		}

		if(is_array($value)){
			return new self($value);
		}else{
			return $value;
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
	 * _parserKey
	 *
	 */
	private static function _parserColon($config){

	}

	/**
	 * _parserKey
	 *
	 */
	private static function _parserPoint($config){

	}
}
