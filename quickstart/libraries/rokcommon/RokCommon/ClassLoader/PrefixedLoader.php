<?php
/**
 * @version   $Id: PrefixedLoader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_ClassLoader_PrefixedLoader extends RokCommon_ClassLoader_AbstractLoader
{
	/**
	 * @var
	 */
	protected $classpath_key;

	/**
	 * @param $classpath_key
	 */
	public function __construct($classpath_key)
	{
		parent::__construct();
		$this->classpath_key = $classpath_key;
	}


	/**
	 * @param     $path
	 * @param     $prefix
	 * @param int $priority
	 */
	public function addPath($path, $prefix, $priority = self::DEFAULT_PATH_PRIORITY)
	{
		$container = RokCommon_Service::getContainer();
		if (!$container->hasParameter($this->classpath_key)) {
			$container->setParameter($this->classpath_key, new stdClass());
		}
		$container->setParameter($this->classpath_key . '.' . $prefix . '.' . $priority, array($path));
		$this->clearChecked();
	}

	/**
	 * @param      $name
	 * @param null $prefix
	 * @param null $interface
	 *
	 * @return mixed
	 * @throws RokCommon_ClassLoader_Exception
	 */
	public function getItem($name, $prefix = null, $interface = null)
	{
		$prefixes = array($prefix);
		if (empty($prefix)) {
			$prefixes = RokCommon_Utils_ArrayHelper::fromObject($this->container->getParameter($this->classpath_key));
		}
		foreach ($prefixes as $current_prefix => $paths) {
			$classname = $current_prefix . ucfirst($name);
			if (class_exists($classname)) {
				if (!empty($interface)) {
					$refclass = new ReflectionClass($classname);
					if (!$refclass->implementsInterface($interface)) {
						throw new RokCommon_ClassLoader_Exception('Found class for item' . $name . ' does not implement ' . $interface);
					}
				}
				$class = new $classname();
				return $class;
			}
		}
		throw new RokCommon_ClassLoader_Exception('Unable to find class for item ' . $name);
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $class The name of the class
	 *
	 * @throws RokCommon_ClassLoader_Exception
	 * @return bool|null True, if loaded
	 */
	public function loadClass($class)
	{
		if ($this->hasBeenChecked($class)) return false;
		if (!$this->container->hasParameter($this->classpath_key)) {
			error_log(sprintf('Classpath parameter %s does not exist.', $this->classpath_key));
			return false;
		}

		$classpath = RokCommon_Utils_ArrayHelper::fromObject($this->container->getParameter($this->classpath_key));
		foreach ($classpath as $prefix => $priorityPaths) {
			if (preg_match('/^' . $prefix . '/i', $class)) {
				$striped_name    = str_ireplace($prefix, '', $class);
				$filename_checks = array(
					$striped_name . self::FILE_EXTENSION, // oRiGiNal
					strtolower($striped_name . self::FILE_EXTENSION), //original
					ucfirst(strtolower($striped_name . self::FILE_EXTENSION)), //Original
					strtoupper($striped_name . self::FILE_EXTENSION) // ORIGINAL
				);
				krsort($priorityPaths); // highest priority is loaded first
				foreach ($priorityPaths as $priority => $paths) {
					foreach ($paths as $path) {
						foreach ($filename_checks as $filename) {
							$full_file_path = $path . DIRECTORY_SEPARATOR . strtolower($filename);
							if (($full_file_path = $this->fileExists($full_file_path, false)) !== false && is_readable($full_file_path)) {
								require_once($full_file_path);
								if (class_exists($class, false)) {
									return true;
								}
							}
						}
					}

				}
			}
		}
		$this->addChecked($class);
		return false;
	}

	/**
	 * @param      $fileName
	 * @param bool $caseSensitive
	 *
	 * @return bool
	 */
	protected function fileExists($fileName, $caseSensitive = true)
	{

		if (file_exists($fileName)) {
			return $fileName;
		}
		if ($caseSensitive) return false;

		// Handle case insensitive requests
		$directoryName     = dirname($fileName);
		$fileArray         = glob($directoryName . '/*', GLOB_NOSORT);
		$fileNameLowerCase = strtolower($fileName);
		if ($fileArray !== false) {
			foreach ($fileArray as $file) {
				if (strtolower($file) == $fileNameLowerCase) {
					return $file;
				}
			}
		}
		return false;
	}
}
