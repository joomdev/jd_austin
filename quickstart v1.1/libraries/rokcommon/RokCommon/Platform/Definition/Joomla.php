<?php

/**
 * @version   $Id: Joomla.php 19230 2014-02-27 01:33:55Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokCommon_Platform_Definition_Joomla extends RokCommon_Platform_BaseDefinition
{
	/**
	 * Check to see if this is the current platform running
	 * @static
	 * @return bool true if this is the current platform, false if not.
	 */
	public static function isCurrentlyRunning()
	{
		if (defined('_JEXEC') && defined('JVERSION')) {
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function __construct()
	{
		$this->_name = 'joomla';
		if (self::isCurrentlyRunning()) {
			if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')) {
				$this->populateJoomla15Info();
			} elseif (version_compare(JVERSION, '1.6', '>=') && version_compare(JVERSION, '1.7', '<')) {
				$this->populateJoomla16Info();
			} elseif (version_compare(JVERSION, '1.7', '>=')) {
				$this->populateJoomla17Info();
			} else {
				$this->_version        = JVERSION;
				$this->_javascriptInfo = new RokCommon_Platform_Javascript();
				$this->_javascriptInfo->setName(RokCommon_Platform_Definition::UNKNOWN);
				$this->_javascriptInfo->setVerison(RokCommon_Platform_Definition::UNKNOWN_VERSION);
			}
			$this->populateLoaderChecks();
		} else {
			$this->_version        = RokCommon_Platform_Definition::UNKNOWN_VERSION;
			$this->_javascriptInfo = new RokCommon_Platform_Javascript();
			$this->_javascriptInfo->setName(RokCommon_Platform_Definition::UNKNOWN);
			$this->_javascriptInfo->setVerison(RokCommon_Platform_Definition::UNKNOWN_VERSION);
		}
	}

	/**
	 * Populate base information for a Joomla 1.5 instance
	 */
	protected function populateJoomla15Info()
	{
		$this->_version        = JVERSION;
		$this->_shortversion   = '1.5';
		$this->_javascriptInfo = new RokCommon_Platform_Javascript();
		$this->_javascriptInfo->setName('mootools');
		$app = JFactory::getApplication();

		if (version_compare(JVERSION, '1.5.15', '<=')) {
			$mootools_version = JFactory::getApplication()->get('MooToolsVersion', '1.11');
		} else {
			$mootools_version = JFactory::getApplication()->get('MooToolsVersion', '1.12');
		}
		$matches = array();
		if (preg_match('/(\d+\.\d+\.?\d*) \+Compat/', $mootools_version, $matches)) {
			$mootools_version = $matches[1];
		}
		$this->_javascriptInfo->setVerison($mootools_version);
	}

	/**
	 * Populate base information for a Joomla 1.6 instance
	 */
	protected function populateJoomla16Info()
	{
		$this->_version        = JVERSION;
		$this->_shortversion   = '1.6';
		$this->_javascriptInfo = new RokCommon_Platform_Javascript();
		$this->_javascriptInfo->setName('mootools');
		$this->_javascriptInfo->setVerison('1.3');
	}

	/**
	 * Populate base information for a Joomla 1.7 instance
	 */
	protected function populateJoomla17Info()
	{
		$this->_version        = JVERSION;
		$this->_shortversion   = '1.7';
		$this->_javascriptInfo = new RokCommon_Platform_Javascript();
		$this->_javascriptInfo->setName('mootools');

	}

	protected function populateLoaderChecks()
	{
		$compat_17_versions = array($this->_version);

		if (version_compare($this->_version, '3.2.0', '>=')) {
			$compat_17_versions[] = '3.1.0';
		}

		$compat_17_versions[] = '1.6.6';


		if (version_compare(JVERSION, '1.7', '>=')) {
			if ($this->_version != RokCommon_Platform_Definition::UNKNOWN_VERSION) {
				foreach ($compat_17_versions as $compat_version) {
					if ($this->_version != RokCommon_Platform_Definition::UNKNOWN_VERSION) {
						$this->_loaderchecks = array_merge($this->_loaderchecks, self::getChecksForVersion($this->_name, $compat_version));
					}
				}
			}
			$this->_loaderchecks[] = $this->_name;
		} else {
			parent::populateLoaderChecks();
		}
	}

	public function getOldVersionPlatformId()
	{
		if (version_compare(JVERSION, '1.6', '>=')) {
			return '16';
		} else {
			return '15';
		}
	}
}


