<?php
/**
 * @version   $Id: PlatformFactory.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_PlatformFactory
{
	/** @var string[] */
	protected static $_platforms = array('joomla', 'wordpress', 'phpunit','native');

	/** @var \RokCommon_Platform_Definition */
	protected static $_current_platform;

	/**
	 * @static
	 * @return \RokCommon_Platform_Definition
	 */
	public static function &getCurrent()
	{
		$ret = null;
		if (isset(self::$_current_platform)) {
			return self::$_current_platform;
		} else {
			foreach (self::$_platforms as $platform) {
				$classname = 'RokCommon_Platform_Definition_' . ucfirst($platform);
				if (class_exists($classname)) {
					if (call_user_func(array($classname, 'isCurrentlyRunning'))) {
						self::$_current_platform = new $classname();
						return self::$_current_platform;
					}
				}
			}
			return $ret;
		}
	}
}
