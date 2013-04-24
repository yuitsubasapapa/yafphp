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

final class Yaf_Debug
{
	protected static $_runtime = array();
	protected static $_runlogs = array();

	/**
	 * log
	 *
	 * @param boolean | string $key;
	 * @param string $message;
	 * @return boolean
	 */
	public static function log($key = false, $message = null)
	{
		if (!YAF_DEBUG) return false;

		if (is_null($message)) {
			if (is_bool($key)) {
				return self::getLog($key);
			} else {
				self::setTime($key);
			}
		} else {
			self::setLog($key, $message);
		}

		return true;
	}

	/**
	 * setLog
	 *
	 * @param string $message;
	 * @param string $key;
	 * @return boolean
	 */
	public static function setLog($key, $message = null)
	{
		if (!YAF_DEBUG) return false;

		if (is_null($message)) {
			self::setTime($key);
		} else {
			$microtime = implode(':', str_split(substr(microtime(), 2, 6), 3));
			self::$_runlogs[] = array(
				'nowtime' => date('Y-m-d H:i:s ' . $microtime),
				'runtime' => round((microtime(true) - self::getTime($key)) * 1000, 2),
				'message' => (is_array($message) ? implode(' ', $message) : (string) $message),
			);
		}

		return true;
	}

	/**
	 * getLog
	 *
	 * @param boolean $echo;
	 * @return string
	 */
	public static function getLog($echo = false)
	{
		if (!YAF_DEBUG) return false;

		$output = '<hr><pre>';
		foreach (self::$_runlogs as $log) {
			$output .= vsprintf("[%s] %sms\t%s" . PHP_EOL, $log);
		}
		if ($echo) echo $output;

		return $output;
	}

	/**
	 * setTime
	 *
	 * @param string $key;
	 * @return boolean
	 */
	public static function setTime($key)
	{
		if (is_string($key)) {
			return self::$_runtime[$key] = microtime(true);
		}
		return false;
	}

	/**
	 * getTime
	 *
	 * @param string $key;
	 * @return integer
	 */
	public static function getTime($key)
	{
		if (is_string($key) && isset(self::$_runtime[$key])) {
			return self::$_runtime[$key];
		}
		return YAF_RUNTIME;
	}
}
