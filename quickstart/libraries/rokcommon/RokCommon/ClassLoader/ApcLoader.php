<?php
/**
 * @version   $Id: ApcLoader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_ClassLoader_ApcLoader extends RokCommon_ClassLoader_AbstractLoader
{
	private $prefix;


	public function __construct($prefix)
	{
		parent::__construct();
		if (!extension_loaded('apc')) {
			throw new RokCommon_ClassLoader_Exception('Unable to use RokCommon_Classloader_ApcLoader as APC is not enabled.');
		}
		$this->prefix = $prefix;
	}

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
		if (false === $file = apc_fetch($this->prefix . $class)) {
			apc_store($this->prefix . $class, $file = $this->findFileForClass($class));
		}
		if ($file !== false) {
			require $file;
			return true;
		}
		$this->addChecked($class);
		return false;
	}
}
