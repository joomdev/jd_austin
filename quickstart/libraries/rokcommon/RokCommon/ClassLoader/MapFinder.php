<?php
/**
 * @version   $Id: MapFinder.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_ClassLoader_MapFinder implements RokCommon_ClassLoader_IFinder
{
	const MAP_FILE_DEFAULT_NAME = 'classes.map.php';

	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;

	/**
	 * @var
	 */
	protected $classpath_key;

	protected $maps = array();

	protected $checked_paths = array();

	protected $last_md5 = '';

	/**
	 * @param $classpath_key
	 */
	public function __construct($classpath_key)
	{
		$this->container     = RokCommon_Service::getContainer();
		$this->classpath_key = $classpath_key;
	}

	/**
	 * Finds the path to the file where the class is defined.
	 *
	 * @param string $class The name of the class
	 *
	 * @return bool|null|string The path, if found
	 */
	public function find($class)
	{
		$classpath = get_object_vars($this->container->getParameter($this->classpath_key));
		krsort($classpath);

		$current_md5 = md5(RokCommon_Utils_ArrayHelper::toString($classpath));
		if ($this->last_md5 != $current_md5) {
			$this->last_md5 = $current_md5;
			$found_maps     = array();
			foreach ($classpath as $priority => $priority_paths) {
				foreach ($priority_paths as $path) {
					if (!in_array($path, $this->checked_paths)) {
						$this->checked_paths[] = $path;
						if (is_dir($path)) {
							$path = $path . DIRECTORY_SEPARATOR . self::MAP_FILE_DEFAULT_NAME;
						}
						if (!in_array($path, $this->checked_paths) && is_file($path)) {
							if (($path_map = @include($path)) && is_array($path_map)) {
								$found_maps[] = $path_map;
							} else {
								if (defined('ROKCOMMON_CORE_DEBUG') && ROKCOMMON_CORE_DEBUG) {
									error_log(sprintf('%s: Unable to load map file %s as a valid class map', get_class($this), $path));
								}
							}
						}
					}
				}
				if (isset($this->maps[$priority]) && !empty($found_maps)) {
					$this->maps[$priority] = array_merge($this->maps[$priority], (array)$found_maps);
				} elseif (!empty($found_maps)) {
					$this->maps[$priority] = (array)$found_maps;
				}
			}
			krsort($this->maps);
		}

		if ('\\' === $class[0]) {
			$class = substr($class, 1);
		}

		foreach ($this->maps as $priority_maps) {
			foreach ($priority_maps as $map) {
				if (isset($map[$class])) {
					return $map[$class];
				}
			}
		}
		return false;
	}
}
