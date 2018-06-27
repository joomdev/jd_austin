<?php
/**
 * @version   $Id: BasicLoader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_ClassLoader_BasicLoader extends RokCommon_ClassLoader_AbstractLoader
{
	/**
	 * Loads the given class or interface.
	 *
	 * @param string $class The name of the class
	 *
	 * @return Boolean|null True, if loaded
	 */
	public function loadClass($class)
	{
		if ($this->hasBeenChecked($class)) return false;
		if ($file = $this->findFileForClass($class)) {
			require $file;
			return true;
		}
		$this->addChecked($class);
		return false;
	}
}
