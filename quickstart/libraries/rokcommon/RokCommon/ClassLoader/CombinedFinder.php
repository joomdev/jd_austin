<?php
/**
 * @version   $Id: CombinedFinder.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_ClassLoader_CombinedFinder implements RokCommon_ClassLoader_IFinder
{
	protected $container;
	/**
	 * @var
	 */
	protected $classpath_key;

	/**
	 * @param $classpath_key
	 */
	public function __construct($classpath_key)
	{
		$this->container = RokCommon_Service::getContainer();
		$this->classpath_key = $classpath_key;
	}


	/**
	 * @param string $class
	 *
	 * @return bool|string
	 */
	public function find($class)
	{
		$paths = RokCommon_Utils_ArrayHelper::fromObject($this->container->getParameter($this->classpath_key));
		ksort($paths);

		if ('\\' == $class[0]) {
			$class = substr($class, 1);
		}

		if (false !== $pos = strrpos($class, '\\')) {
			// namespaced class name
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		} else {
			// PEAR-like class name
			$classPath = null;
			$className = $class;
		}

		$classPathParts = array();
		if (!empty($classPath)) {
			$classPathParts = explode(DIRECTORY_SEPARATOR, $classPath);
		}
		$nameStartPosition = count($classPathParts);
		$classNameParts    = explode('_', $className);
		$classPathParts    = array_merge($classPathParts, $classNameParts);
		$partsCount        = count($classPathParts);

		if (count($classPathParts) > 1) {
			// check for compiled first
			$iter = new RecursiveArrayIterator($paths);
			foreach (new RecursiveIteratorIterator($iter) as $dir) {
				$pos = $partsCount - 1;
				do {
					$path = $dir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_slice($classPathParts, 0, $pos)) . '.compiled.php';
					if (file_exists($path)) {
						return $path;
					}
				} while (--$pos > $nameStartPosition);
			}
		}
		return false;
	}
}
