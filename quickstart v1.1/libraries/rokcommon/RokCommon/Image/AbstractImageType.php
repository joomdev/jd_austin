<?php
/**
 * @version   $Id: AbstractImageType.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Derived from
 *
 * PhpThumb Base Class Definition File
 *
 * This file contains the definition for the ThumbBase object
 *
 * PHP Version 5 with GD 2.0+
 * PhpThumb : PHP Thumb Library <http://phpthumb.gxdlabs.com>
 * Copyright (c) 2009, Ian Selby/Gen X Design
 *
 * Author(s): Ian Selby <ian@gen-x-design.com>
 *
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author                               Ian Selby <ian@gen-x-design.com>
 * @copyright                            Copyright (c) 2009 Gen X Design
 * @link                                 http://phpthumb.gxdlabs.com
 * @license                              http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version                              3.0
 * @package                              PhpThumb
 * @filesource
 */

/**
 * ThumbBase Class Definition
 *
 * This is the base class that all implementations must extend.  It contains the
 * core variables and functionality common to all implementations, as well as the functions that
 * allow plugins to augment those classes.
 *
 * @package    PhpThumb
 * @subpackage Core
 */
abstract class RokCommon_Image_AbstractImageType implements RokCommon_Image_ImageType
{
	/**
	 * All imported objects
	 *
	 * An array of imported plugin objects
	 *
	 * @var array
	 */
	protected $imported;
	/**
	 * All imported object functions
	 *
	 * An array of all methods added to this class by imported plugin objects
	 *
	 * @var array
	 */
	protected $importedFunctions;
	/**
	 * The last error message raised
	 *
	 * @var string
	 */
	protected $errorMessage;
	/**
	 * Whether or not the current instance has any errors
	 *
	 * @var bool
	 */
	protected $hasError;
	/**
	 * The name of the file we're manipulating
	 *
	 * This must include the path to the file (absolute paths recommended)
	 *
	 * @var string
	 */
	protected $fileName;
	/**
	 * What the file format is (mime-type)
	 *
	 * @var string
	 */
	protected $format;
	/**
	 * Whether or not the image is hosted remotely
	 *
	 * @var bool
	 */
	protected $remoteImage;
	/**
	 * Whether or not the current image is an actual file, or the raw file data
	 *
	 * By "raw file data" it's meant that we're actually passing the result of something
	 * like file_get_contents() or perhaps from a database blob
	 *
	 * @var bool
	 */
	protected $isDataStream;

	/**
	 * The options for this class
	 *
	 * This array contains various options that determine the behavior in
	 * various functions throughout the class.  Functions note which specific
	 * option key / values are used in their documentation
	 *
	 * @var array
	 */
	protected $options;


	/**
	 * Class constructor
	 *

	 *
	 * @param       $fileName
	 * @param array $options
	 * @param bool  $isDataStream
	 *
	 * @throws RokCommon_Image_Exception
	 */
	public function __construct($fileName, array $options = array(), $isDataStream = false)
	{
		$this->imported          = array();
		$this->importedFunctions = array();
		$this->errorMessage      = null;
		$this->hasError          = false;
		$this->fileName          = $fileName;
		$this->remoteImage       = false;
		$this->isDataStream      = $isDataStream;

		$this->fileExistsAndReadable();
	}

	/**
	 * Imports plugins in $registry to the class
	 *
	 * @param array $registry
	 */
	public function importPlugins($registry)
	{
		foreach ($registry as $plugin => $meta) {
			$this->imports($plugin);
		}
	}

	/**
	 * Imports a plugin
	 *
	 * This is where all the plugins magic happens!  This function "loads" the plugin functions, making them available as
	 * methods on the class.
	 *
	 * @param string $object The name of the object to import / "load"
	 */
	protected function imports($object)
	{
		// the new object to import
		$newImport = new $object();
		// the name of the new object (class name)
		$importName = get_class($newImport);
		// the new functions to import
		$importFunctions = get_class_methods($newImport);

		// add the object to the registry
		array_push($this->imported, array($importName, $newImport));

		// add the methods to the registry
		foreach ($importFunctions as $key => $functionName) {
			$this->importedFunctions[$functionName] = &$newImport;
		}
	}

	/**
	 * Checks to see if $this->fileName exists and is readable
	 *
	 */
	protected function fileExistsAndReadable()
	{
		if ($this->isDataStream === true) {
			return;
		}

		if (stristr($this->fileName, 'http://') !== false) {
			$this->remoteImage = true;
			return;
		}

		if (!file_exists($this->fileName)) {
			throw new RokCommon_Image_Exception('Image file not found: ' . $this->fileName);
		} elseif (!is_readable($this->fileName)) {
			throw new RokCommon_Image_Exception('Image file not readable: ' . $this->fileName);
		}
	}

	/**
	 * Calls plugin / imported functions
	 *
	 * This is also where a fair amount of plugins magaic happens.  This magic method is called whenever an "undefined" class
	 * method is called in code, and we use that to call an imported function.
	 *
	 * You should NEVER EVER EVER invoke this function manually.  The universe will implode if you do... seriously ;)
	 *
	 * @param string $method
	 * @param array  $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if (array_key_exists($method, $this->importedFunctions)) {
			$args[] = $this;
			return call_user_func_array(array($this->importedFunctions[$method], $method), $args);
		}

		throw new BadMethodCallException ('Call to undefined method/class function: ' . $method);
	}

	/**
	 * Returns $imported.
	 * @see ThumbBase::$imported
	 * @return array
	 */
	public function getImported()
	{
		return $this->imported;
	}

	/**
	 * Returns $importedFunctions.
	 * @see ThumbBase::$importedFunctions
	 * @return array
	 */
	public function getImportedFunctions()
	{
		return $this->importedFunctions;
	}


	/**
	 * Returns $fileName.
	 *
	 * @see ThumbBase::$fileName
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * Returns $format.
	 *
	 * @see ThumbBase::$format
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}


	/**
	 * @abstract
	 * @return array the default set of options for the RokCommon_Image_ImageType
	 */
	abstract protected function getOptionDefaults();

	/**
	 * Returns $options.
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	#################################
	# ----- GETTERS / SETTERS ----- #
	#################################

	/**
	 * Sets $this->options to $options
	 *
	 * @param array $options
	 */
	public function setOptions(array $options = array())
	{
		// make sure we've got an array for $this->options (could be null)
		if (!is_array($this->options)) {
			$this->options = array();
		}

		// we've yet to init the default options, so create them here
		if (sizeof($this->options) == 0) {
			$defaultOptions = $this->getOptionDefaults();
		} // otherwise, let's use what we've got already
		else {
			$defaultOptions = $this->options;
		}

		$this->options = array_merge($defaultOptions, $options);
	}

	public function setOption($name, $value)
	{
		if ($name != null && $value != null) {
			$this->options[$name] = $value;
		}
	}

	/**
	 * Get the value of a named option
	 *
	 * @param string $name the name of the option to get the value for
	 *
	 * @return mixed|null the value of the passed in option or null if it doesn't exist
	 */
	public function getOption($name)
	{
		$ret = null;
		if (array_key_exists($name, $this->options))
		{
			$ret = $this->options[$name];
		}
		return $ret;
	}


}
