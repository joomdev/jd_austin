<?php
/**
 * @version   $Id: Native.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Platform_Definition_Native extends RokCommon_Platform_BaseDefinition
{
	/**
	 * Check to see if this is the current platform running
	 * @static
	 * @return bool true if this is the current platform, false if not.
	 */
	public static function isCurrentlyRunning()
	{
		return true;
	}

	/**
	 *
	 */
	public function __construct()
	{
		$this->_version        = phpversion();;
		$version_parts         = explode('.', $this->_version);
		$this->_shortversion   = (count($version_parts) >= 2) ? $version_parts[0] . '.' . $version_parts[1] : $version_parts[0];
		$this->_javascriptInfo = new RokCommon_Platform_Javascript();
		$this->_javascriptInfo->setName(RokCommon_Platform_Definition::UNKNOWN);
		$this->_javascriptInfo->setVerison(RokCommon_Platform_Definition::UNKNOWN_VERSION);
	}

	public function getOldVersionPlatformId()
	{
		return $this->_shortversion;
	}
}
