<?php
/**
 * @version   $Id: Loader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Form_Loader implements RokCommon_Loader
{
	/**
	 * @var array
	 */
	private $_orderedPaths = array();

	/**
	 * @var array
	 */
	private $_allPaths = array();

	/**
	 * @var array
	 */
	private $_prefixes = array();


	/**
	 * @param        $path
	 * @param string $prefix
	 * @param int    $priority
	 *
	 * @throws RokCommon_Ajax_Exception
	 * @return
	 */
	public function addModelPath($path, $prefix = RokCommon_Ajax::DEFAULT_MODEL_PREFIX, $priority = 10)
	{
		$realpath = realpath($path);
		if (in_array($realpath, $this->_allPaths)) return;
		if (!file_exists($path) || !is_dir($path)) {
			throw new RokCommon_Ajax_Exception($path . ' is not a valid directory.');
		}
		$this->_orderedPaths[$priority][$realpath] = $realpath;
		$this->_allPaths[]                         = $realpath;
		$this->_prefixes[$realpath]                = $prefix;
	}


	/**
	 * @param $model
	 *
	 * @throws RokCommon_Ajax_Exception
	 * @return bool|RokCommon_Ajax_Model
	 */
	public function &getModel($model)
	{
		foreach ($this->_orderedPaths as $priority => $priorityPaths) {
			foreach ($priorityPaths as $path) {
				if (array_key_exists($path, $this->_prefixes)) {
					$classname = $this->_prefixes[$path] . ucfirst($model);
					if (class_exists($classname)) {
						$refclass = new ReflectionClass($classname);
						if (!$refclass->implementsInterface('RokCommon_Ajax_Model')) {
							throw new RokCommon_Ajax_Exception('Found class for model ' . $model . ' does not implement RokCommon_Ajax_Model');
						}
						$class = new $classname();
						return $class;
					}
				}
			}
		}
		throw new RokCommon_Ajax_Exception('Unable to find class for model ' . $model);
	}

	/**
	 * @param  string $className the class name to look for and load
	 *
	 * @return bool True if the class was found and loaded.
	 */
	public function loadClass($className)
	{

		foreach ($this->_orderedPaths as $priority => $priorityPaths) {
			foreach ($priorityPaths as $path) {
				if (array_key_exists($path, $this->_prefixes)) {
					$fileName       = str_replace($this->_prefixes[$path], '', $className) . self::FILE_EXTENSION;
					$full_file_path = $path . DIRECTORY_SEPARATOR . $fileName;
					if (file_exists($full_file_path) && is_readable($full_file_path)) {
						require($full_file_path);
						return true;
					}
				}

			}
		}
		return false;
	}
}
