<?php
/**
 * @version   $Id: Package.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Composite_Package
{
	/** @var string */
	protected $_name;

	/** @var string[] */
	protected $_paths = array();

	/**
	 * @var array
	 */
	protected $_contexts = array();


	/**
	 * @param $name
	 */
	public function __construct($name)
	{
		$this->_name = strtolower($name);
	}

	/**
	 * @param     $path
	 * @param int $priority
	 */
	public function addPath($path, $priority = RokCommon_Composite::DEFAULT_PRIORITY)
	{
		if (file_exists($path)) {
			$this->_paths[$priority][$path] = $path;
			krsort($this->_paths, SORT_NUMERIC);
		}
	}


	/**
	 * @param $context_path
	 *
	 * @return \RokCommon_Composite_Context
	 */
	public function &getContext($context_path)
	{
		$this->_contexts[$context_path] = new RokCommon_Composite_Context($context_path, $this->_paths);

		return $this->_contexts[$context_path];
	}


	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
}
