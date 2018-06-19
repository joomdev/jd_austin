<?php
/**
 * @version   $Id: AbstractLoader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokCommon_ClassLoader_AbstractLoader implements RokCommon_ClassLoader_ILoader
{


	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;


	protected $active = false;
	/**
	 * @var array
	 */
	protected $checked = array();

	/**
	 * @var RokCommon_ClassLoader_IFinder[]
	 */
	protected $finders = array();

	public function __construct()
	{
		$this->container = RokCommon_Service::getContainer();
	}


	/**
	 * @param RokCommon_ClassLoader_IFinder|RokCommon_ClassLoader_IFinder[] $finders
	 *
	 * @return mixed|void
	 */
	public function setFinders($finders = array())
	{
		if (!is_array($finders)) {
			$finders = array($finders);
		}
		$this->finders = $finders;
		$this->checked = array();
	}

	/**
	 * @param $class
	 *
	 * @return bool|string
	 */
	protected function findFileForClass($class)
	{
		$prefixes  = $this->container->getParameter('classloader.allowed_prefixes');
		$findit    = false;
		foreach ($prefixes as $allowed_prefix) {
			if (stripos($class, $allowed_prefix) === 0) {
				$findit = true;
				break;
			}
		}
		if ($findit) {
			foreach ($this->finders as $finder) {
				/** @var $finder RokCommon_ClassLoader_IFinder */
				if ($file = $finder->find($class)) {
					return $file;
				}
			}
		}
		return false;
	}

	protected function hasBeenChecked($class)
	{
		return in_array($class, $this->checked);
	}

	protected function addChecked($class)
	{
		$this->checked[] = $class;
	}

	protected function clearChecked()
	{
		$this->checked = array();
	}

	public function activate($priority = RokCommon_ClassLoader::DEFAULT_LOADER_PRIORITY)
	{
		if (!$this->active) {
			/** @var $classloader RokCommon_ClassLoader */
			$classloader =  $this->container->classloader;
			$classloader->addLoader($this, $priority);
			$this->active = true;
		}
	}

	public function deactivate()
	{
		if ($this->active) {
			/** @var $classloader RokCommon_ClassLoader */
			$classloader =  $this->container->classloader;
			$classloader->removeLoader($this);
			$this->active = false;
		}
	}
}
