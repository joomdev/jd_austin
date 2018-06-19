<?php
/**
 * @version   3.2.5 August 4, 2016
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * derived from JoomlaRTCacheDriver with original copyright and license
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('ROKCOMMON') or die;

/**
 * Abstract Format for RokCommon_Registry
 *
 * @abstract
 * @package		JoomlaRTCacheDriver.Framework
 * @subpackage	Registry
 * @since		1.5
 */
abstract class RokCommon_Registry_Format
{
    protected static $instances;

	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string	The format to load
	 * @return	object	Registry format handler
	 * @throws	RokCommon_Loader_Exception
	 * @since	1.5
	 */
	public static function getInstance($type)
	{
		// Initialize static variable.

		if (!isset (self::$instances)) {
			self::$instances = array ();
		}

		// Sanitize format type.
		$type = strtoupper(preg_replace('/[^A-Z0-9_]/i', '', $type));
		// Only instantiate the object if it doesn't already exist.
		if (!isset(self::$instances[$type])) {
			// Only load the file the class does not exist.
			$class = 'RokCommon_Registry_Format_'.$type;
			if (!class_exists($class)) {
		        throw new RokCommon_Loader_Exception('Unable to find Registry format ' . $type);
			}
			self::$instances[$type] = new $class();
		}
		return self::$instances[$type];
	}

	/**
	 * Converts an object into a formatted string.
	 *
	 * @param	object	Data Source Object.
	 * @param	array	An array of options for the formatter.
	 * @return	string	Formatted string.
	 * @since	1.5
	 */
	abstract public function objectToString($object, $options = null);

	/**
	 * Converts a formatted string into an object.
	 *
	 * @param	string	Formatted string
	 * @param	array	An array of options for the formatter.
	 * @return	object	Data Object
	 * @since	1.5
	 */
	abstract public function stringToObject($data, $options = null);
}