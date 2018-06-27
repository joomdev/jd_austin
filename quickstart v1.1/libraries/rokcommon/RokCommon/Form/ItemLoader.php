<?php
/**
 * @version   $Id: ItemLoader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Form_ItemLoader extends RokCommon_ClassLoader_PrefixedLoader
{
	/** @var string the package name for the assets */
	protected $package;

	public function __construct($classpath_key, $package)
	{
		$this->package = $package;
		parent::__construct($classpath_key);
		$classpath = RokCommon_Utils_ArrayHelper::fromObject($this->container->getParameter($this->classpath_key));
		$append_path = $this->container->getParameter('form.assets.appendpath','assets');
		foreach ($classpath as $prefix => $priorityPaths) {
			krsort($priorityPaths); // highest priority is loaded first
			foreach ($priorityPaths as $priority => $paths) {
				foreach ($paths as $path) {
					RokCommon_Composite::addPackagePath($this->package, $path . DS . $append_path, $priority);
				}
			}
		}

	}

	public function addPath($path, $prefix, $priority = self::DEFAULT_PATH_PRIORITY)
	{
		parent::addPath($path, $prefix, $priority);
		RokCommon_Composite::addPackagePath($this->package, $path . DS . $this->container->getParameter('form.assets.appendpath','assets'), $priority);
	}
}
