<?php
final class Yaf_Config_Simple extends Yaf_Config_Abstract
{

	/**
	 * __construct
	 *
	 */
	public function __construct($config, $readonly = NULL)
	{
		if(is_array($config)){
			$this->_config = $config;
			if(!is_null($readonly)){
				$this->_readonly = (bool)$readonly;
			}
		}else{
			throw new Yaf_Exception_TypeError('Invalid parameters provided, must be an array');
			return NULL;
		}
	}

	/**
	 * __get
	 *
	 */
	public function __get($name)
	{
		if(isset($this->_config[$name])){
			$value = $this->_config[$name];
			if(is_array($value)){
				return new Yaf_Config_Simple($value);
			}else{
				return $value;
			}
		}
	}

}
