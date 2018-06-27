<?php
/**
 * @version   $Id: Service.php 27318 2015-03-04 12:51:02Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Service extends RokCommon_Service_Container_Builder
{
	/**
	 * @var bool
	 */
	protected static $developmentMode = false;
	/**
	 * @var RokCommon_Service_Container[]
	 */
	protected static $instance = array();

	/**
	 * @var
	 */
	protected static $containerInstance;

	/**
	 * @var string
	 */
	protected static $last_checksum = null;

	/**
	 * @var string[]
	 */
	protected static $config_files = array();

	/**
	 * @var array
	 */
	protected static $addedFiles = array();

	/**
	 * @var array
	 */
	protected static $addingFiles = array();

	/**
	 * @var
	 */
	protected static $tmp_file_dir;

	/**
	 * @static
	 * @return RokCommon_Service_Container
	 */
	public static function getContainer()
	{
		if (!isset(self::$containerInstance)) {
			self::$containerInstance = new self;
			$loader                  = new RokCommon_Service_Container_Loader_File_Xml(self::$containerInstance);
			// get and load the platform specific base container file
			$platform = RokCommon_PlatformFactory::getCurrent();
			foreach ($platform->getLoaderChecks() as $platform_check) {
				$platform_file = ROKCOMMON_LIB_PATH . '/config/' . $platform_check . '.xml';
				if (file_exists($platform_file)) {
					$loader->load($platform_file);
					/** @var $platforminfo RokCommon_IPlatformInfo */
					$platforminfo = self::$containerInstance->platforminfo;
					$platforminfo->setPlatformParameters(self::$containerInstance);
					break;
				}

			}
		}
		return self::$containerInstance;
	}

	/**
	 * @static
	 *
	 * @param string    $path

	 */
	public static function addConfigFile($path)
	{
		self::getContainer();
		if (!in_array($path, self::$config_files)) {
			// set up the loader
			$loader = new RokCommon_Service_Container_Loader_File_Xml(self::$containerInstance);
			$loader->load($path);
			self::$config_files[] = $path;
		}
	}

	/**
	 * @static
	 * @return string[]
	 */
	protected static function getConfigFiles()
	{
		$ret = array();
		ksort(self::$config_files, SORT_NUMERIC);
		$iterator = new RecursiveArrayIterator(self::$config_files);
		foreach ($iterator as $path) {
			if (is_file($path)) {
				$ret[] = $path;
			}
		}
		return $ret;
	}

	/**
	 * @param boolean $developmentMode
	 */
	public static function setDevelopmentMode($developmentMode)
	{
		self::$developmentMode = $developmentMode;
	}

	/**
	 * @return boolean
	 */
	public static function getDevelopmentMode()
	{
		return self::$developmentMode;
	}

	/**
	 * @static
	 *
	 * @param $path
	 */
	public static function setTempFileDir($path)
	{
		if (@is_dir($path) && @is_writable($path)) {
			self::$tmp_file_dir = $path;
		}
	}
}

RokCommon_Service::setTempFileDir(sys_get_temp_dir());
