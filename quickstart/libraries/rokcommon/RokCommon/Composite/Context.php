<?php
/**
 * @version   $Id: Context.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Composite_Context
{
	/**
	 * @var string
	 */
	protected $_context = '';

	/**
	 * @var string
	 */
	protected $_paths;

	/**
	 * @param       $context
	 * @param array $paths
	 *
	 * @return RokCommon_Composite_Context
	 *
	 */
	public function __construct($context, array &$paths = array())
	{
		$this->_context = $context;
		$this->_paths   = $paths;
	}

	/**
	 * Perform an include with passed variables set for the passed in found file on the paths of the
	 * context in the context path
	 *
	 * @param      $file
	 * @param      $vars
	 *
	 * @param bool $hierarchical
	 *
	 * @return string|bool
	 */
	public function load($file, $vars, $hierarchical = true)
	{
		$_internal_loading_final_file = false;
		if (!$hierarchical) {
			$_internal_loading_final_file = self::_findFile($file, $this->_context, $this->_paths);
		} else {
			$found_paths = self::_findSet($file, $this->_context, $this->_paths);
			if (!empty($found_paths)) {
				$_internal_loading_final_file = $found_paths[0];
			}
		}

		if ($_internal_loading_final_file === false) return false;
		if (!file_exists($_internal_loading_final_file)) return false;
		extract($vars, EXTR_REFS | EXTR_SKIP);
		ob_start();
		include($_internal_loading_final_file);
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Perform an include with passed variables set for the passed in found file on the paths of the
	 * context in the context path
	 *
	 * @param $file
	 * @param $vars
	 * @param bool $hierarchical
	 * @param bool $startbuild
	 *
	 * @return string|bool
	 */
	public function build($file, $vars, $hierarchical = true, $startbuild = false)
	{
		$_internal_loading_final_file = false;
		if (!$hierarchical) {
			$_internal_loading_final_file = self::_findFile($file, $this->_context, $this->_paths);
		} else {
			$found_paths = self::_findSet($file, $this->_context, $this->_paths);
			if (!empty($found_paths)) {
				$_internal_loading_final_file = $found_paths[0];
			}
		}

		if ($_internal_loading_final_file === false) return false;
		if (!file_exists($_internal_loading_final_file)) return false;
		extract($vars, EXTR_REFS | EXTR_SKIP);

		if (!$startbuild){
			// look at backtrace to see where called from
			$backtrace = debug_backtrace();
			$filecontents = file($backtrace[0]['file']);
			$line = $backtrace[0]['line']-1;
			preg_replace('/build/','ignore',$filecontents[$line]);
		}
		ob_start();

		include($_internal_loading_final_file);

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Perform an include with passed variables set for the passed in found file on the paths of the
	 * context in the context path
	 *
	 * @param $file
	 * @param $vars
	 *
	 * @return string|bool
	 */
	public function loadAll($file, $vars)
	{

		$found_paths = self::_findSet($file, $this->_context, $this->_paths);
		if (!empty($found_paths)) {
			$_internal_loading_final_file = $found_paths[0];
		}

		if ($found_paths === false && !empty($found_paths)) return false;

		extract($vars, EXTR_REFS | EXTR_SKIP);
		ob_start();
		foreach ($found_paths as $found_path) {
			if (!file_exists($_internal_loading_final_file)) continue;
			include($found_path);
		}
		$output = ob_get_clean();
		return $output;
	}


	/**
	 * Get the path of the highest priority package file with the context in the context paths;
	 *
	 * @param $file
	 *
	 * @return bool|string
	 */
	public function get($file)
	{
		return self::_findFile($file, $this->_context, $this->_paths);
	}

	/**
	 * @param string $file
	 * @param bool   $hierarchical
	 *
	 * @return string
	 */
	public function getUrl($file, $hierarchical = true)
	{
		$file_path = false;
		if (!$hierarchical) {
			$file_path = self::_findFile($file, $this->_context, $this->_paths);
		} else {
			$found_paths = self::_findSet($file, $this->_context, $this->_paths);
			if (!empty($found_paths)) {
				$file_path = $found_paths[0];
			}
		}
		if ($file_path == false) return '';
		$container = RokCommon_Service::getContainer();
		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $container->platforminfo;
		return $platforminfo->getUrlForPath($file_path);
	}

	/**
	 * Get the hierarchical set of the highest priority files with the filename along the context path
	 *
	 * @param $file
	 *
	 * @return array
	 */
	public function getSet($file)
	{
		return self::_findSet($file, $this->_context, $this->_paths);
	}


	/**
	 * @param $file
	 * @param $context
	 * @param $basepaths
	 *
	 * @return bool|string
	 */
	protected static function _findFile($file, $context, $basepaths)
	{
		$hunt_path = str_replace('.', DS, $context);
		foreach ($basepaths as $priority => $paths) {
			foreach ($paths as $path) {

				$find_path = $path;
				$find_path .= (!empty($hunt_path)) ? DS . $hunt_path : '';
				$find_path .= DS . $file;

				if (file_exists($find_path) && is_file($find_path)) {
					return $find_path;
				}
			}
		}
		return false;
	}


	/**
	 * @param $file
	 *
	 * @return array
	 */
	public function getAll($file)
	{
		return self::_findAllFiles($file, $this->_context, $this->_paths);
	}

	/**
	 * @param $file
	 *
	 * @return array
	 */
	public function getAllSubFiles($file)
	{
		return self::_findSubFiles($file, $this->_context, $this->_paths);
	}


	/**
	 * @param $file
	 * @param $context
	 * @param $basepaths
	 *
	 * @return array
	 */
	protected static function _findAllFiles($file, $context, $basepaths)
	{
		$ret       = array();
		$hunt_path = str_replace('.', DS, $context);
		foreach ($basepaths as $priority => $paths) {
			foreach ($paths as $path) {

				$find_path = $path;
				$find_path .= (!empty($hunt_path)) ? DS . $hunt_path : '';
				$find_path .= DS . $file;

				if (file_exists($find_path) && is_file($find_path)) {
					$ret[$priority][] = $find_path;
				}
			}
		}
		return $ret;
	}

	/**
	 * @static
	 *
	 * @param $file
	 * @param $context
	 * @param $basepaths
	 *
	 * @return array
	 */
	protected static function _findSubFiles($file, $context, $basepaths)
	{

		$ret       = array();
		$hunt_path = str_replace('.', DS, $context);
		foreach ($basepaths as $priority => $paths) {
			foreach ($paths as $path) {
				$find_path = $path;
				$find_path .= (!empty($hunt_path)) ? DS . $hunt_path : '';
				if (defined("RecursiveDirectoryIterator::FOLLOW_SYMLINKS")) {
					$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($find_path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS), RecursiveIteratorIterator::LEAVES_ONLY);
				} else {
					$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($find_path), RecursiveIteratorIterator::LEAVES_ONLY);
				}
				foreach ($iterator as $path) {
					/** @var $path FilesystemIterator  */
					if ($path->getFilename() == $file) {
						$ret[$priority][] = $path->__toString();
					}
				}
			}
		}
		return $ret;
	}


	/**
	 * @param $file
	 * @param $context
	 * @param $basepaths
	 *
	 * @return array
	 */
	protected static function _findSet($file, $context, $basepaths)
	{
		$ret           = array();
		$context_parts = explode('.', $context);
		if (!empty($context)) {
			while (count($context_parts)) {
				$context_path = implode('.', $context_parts);
				$filepath     = self::_findFile($file, $context_path, $basepaths);
				if ($filepath !== false) {
					$ret[] = $filepath;
				}
				array_pop($context_parts);
			}
		}
		$filepath = self::_findFile($file, '', $basepaths);
		if ($filepath !== false) {
			$ret[] = $filepath;
		}

		return $ret;
	}
}
