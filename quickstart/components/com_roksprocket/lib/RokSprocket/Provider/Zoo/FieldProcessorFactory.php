<?php
/**
 * @version   $Id: FieldProcessorFactory.php 14554 2013-10-16 21:17:05Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider_Zoo_FieldProcessorFactory
{
	const TYPE_UNKNOWN            = 'unknown';
	const TYPE_TEXT               = 'text';
	const TYPE_IMAGE              = 'image';
	const TYPE_LINK               = 'link';
	const DEFAULT_VALUE_CONTAINER = 'value';
	const TYPE_FIELD              = 'type';
	const VALUE_FIELD             = 'value';

	protected static $types;

	/**
	 * @param $type
	 *
	 * @return RokSprocket_Provider_Zoo_FieldProcessorInterface
	 */
	public static function getFieldProcessor($type)
	{
		$processor  = null;
		$classbase  = 'RokSprocket_Provider_Zoo_FieldProcessor_';
		$type_class = $classbase . ucfirst($type);

		if (class_exists($type_class)) {
			$processor = new $type_class;
		} else {
			$basic_class = $classbase . 'Basic';
			if (class_exists($basic_class)) {
			}
			$processor = new $basic_class;
		}
		return $processor;
	}

	public static function getSprocketType($zoo_element_type)
	{
		if (!isset(self ::$types)) {
			self ::$types = json_decode(file_get_contents(dirname(__FILE__) . '/zoo_element_type_map.json'), true);
		}
		$sprocket_type = self::TYPE_UNKNOWN;
		if (array_key_exists($zoo_element_type, self::$types)) {
			$sprocket_type = self::$types[$zoo_element_type][self::TYPE_FIELD];
		}
		return $sprocket_type;
	}

	public static function getValueContainer($type)
	{
		if (!isset(self ::$types)) {
			self ::$types = json_decode(file_get_contents(dirname(__FILE__) . '/zoo_element_type_map.json'), true);
		}
		$value_container = self::DEFAULT_VALUE_CONTAINER;
		if (array_key_exists($type, self::$types)) {
			$value_container = self::$types[$type][self::VALUE_FIELD];
		}
		return $value_container;
	}
}
 