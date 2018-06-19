<?php
/**
 * @version   $Id: Memory.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Memory_Exception extends Exception
{
}

/**
 *
 */
class RokCommon_Memory
{
	/**
	 *
	 */
	const ERROR_UNKNOWN = 100;
	/**
	 *
	 */
	const ERROR_SUSHIN = 101;

	/**
	 * Gets the current memory limit set for the php.ini in bytes
	 * @static
	 * @return int the memory limit in bytes
	 */
	public static function getLimit()
	{
		$val = ini_get('memory_limit');
		$val = self::convertSize($val);
		return $val;
	}

	/**
	 * @static
	 *
	 * @param $size
	 *
	 * @return int|string
	 */
	protected static function convertSize($size)
	{
		$size = trim($size);
		$last = strtolower($size[strlen($size) - 1]);
		switch ($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$size *= 1024;
			case 'm':
				$size *= 1024;
			case 'k':
				$size *= 1024;
		}
		return $size;
	}

	/**
	 * Gets the available free space in bytes
	 *
	 * @static
	 * @return int
	 */
	public static function getFreeSpace()
	{
		return self::getLimit() - self::getUsage();
	}

	/**
	 * @static
	 *
	 * @param $size
	 *
	 * @throws RokCommon_Memory_Exception
	 */
	public static function setLimit($size)
	{
		if (in_array('suhosin', get_loaded_extensions())) {
			$suhosin_limit = self::convertSize(ini_get('suhosin.memory_limit'));
			if ($size > $suhosin_limit) {
				throw new RokCommon_Memory_Exception('Memory size limited by Suhosin.  The suhosin.memory_limit setting needs to be adjusted', self::ERROR_SUSHIN);
			}
		}
		ini_set('memory_limit', $size);
		$new_limit = ini_get('memory_limit');
		if ($size != self::convertSize($new_limit)) {
			throw new RokCommon_Memory_Exception('Unable to automatically adjust memory limit.  The memory_limit ini setting needs to be adjusted.', self::ERROR_UNKNOWN);
		}
	}

	/**
	 * @static
	 * @return int
	 */
	public static function getUsage()
	{
		return memory_get_usage();
	}
}
