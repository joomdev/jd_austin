<?php
/**
 * @version   $Id: Platform.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// No direct access
defined('ROKCOMMON') or die;

/**
 * @deprecated Use RokCommon_PlatformFactory to get a RokCommon_Platform_Definition
 */
class RokCommon_Platform
{
	/** @var RokCommon_Platform_Definition */
	protected $platform_definition;

	/**
	 * @var RokCommon_Platform
	 */
	protected static $instance;

	/**
	 * @static
	 * @return RokCommon_Platform
	 */
	public static function &getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new RokCommon_Platform();
		}
		return self::$instance;
	}

	/**
	 *
	 */
	protected function __construct()
	{
		$this->platform_definition = RokCommon_PlatformFactory::getCurrent();
	}

	/**
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platform_definition->getName();
	}

	/**
	 * @return string
	 */
	public function getPlatformVersion()
	{
		return $this->platform_definition->getVersion();
	}

	/**
	 * @return string
	 */
	public function getPlatformId()
	{
		return strtolower($this->platform_definition->getName()) . preg_replace('/[\.]/i', '', $this->platform_definition->getOldVersionPlatformId());
	}

	/**
	 * @return string
	 */
	public function getPlatformShortVersion()
	{
		return $this->platform_definition->getShortVersion();
	}
}
