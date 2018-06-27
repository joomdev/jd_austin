<?php
/**
 * @version   $Id: JSON.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

class RokCommon_JSON_Exception extends Exception
{
}

/**
 * RokCommon_Annotation to Ignore a Property on JSON Encode
 * @Target("property")
 */
class RokCommon_JSON_Annotation_JSONEncodeIgnore extends RokCommon_Annotation
{
}

/**
 * RokCommon_Annotation to Ignore a Property on JSON Decode
 * @Target("property")
 */
class RokCommon_JSON_Annotation_JSONDecodeIgnore extends RokCommon_Annotation
{
}

/**
 * RokCommon_Annotation to Ignore a Property on JSON Decode
 * @Target("class")
 */
class RokCommon_JSON_Annotation_JSONDefaultKey extends RokCommon_Annotation
{
	/**
	 * @param RokCommon_Annotation_ReflectionClass $target
	 */
	protected function checkConstraints($target)
	{
		if (!$target->hasProperty($this->value)) {
			trigger_error(sprintf('Class %s has a JSONDefaultKey annotation which references a property \'%s\' which does not exist.', $target->getName(), $this->value), E_USER_ERROR);
		}
	}
}

/**
 *
 */
class RokCommon_JSON
{

	const TYPE_FIELD = '_type';
	/**
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * A wrapper for json_encode
	 * @static
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public static function encode($data)
	{
		return json_encode(self::prepDataForEncode($data));
	}

	/**
	 * @static
	 *
	 * @param $data
	 *
	 * @return array|stdClass
	 */
	protected static function prepDataForEncode($data)
	{
		if (is_object($data)) {
			$json_preped_encode                    = new stdClass();
			$class                                 = new RokCommon_Annotation_ReflectionClass($data);
			$class_type_field                      = self::TYPE_FIELD;
			$json_preped_encode->{$class_type_field} = $class->getName();
			/** @var $properties ReflectionAnnotatedProperty[] */
			$properties = $class->getProperties();
			foreach ($properties as $property) {
				if (!$property->hasAnnotation('RokCommon_JSON_Annotation_JSONEncodeIgnore') && !$property->isStatic()) {
					$name = $property->getName();

					$json_preped_encode->{$name} = self::prepDataForEncode(self::getPropertyValue($data, $property));
				}
			}

		} elseif (is_array($data)) {
			$json_preped_encode = array();
			foreach ($data as $key => $value) {
				$json_preped_encode[$key] = self::prepDataForEncode($value);
			}
		} else {
			$json_preped_encode = $data;
		}
		return $json_preped_encode;
	}

	/**
	 * @static
	 *
	 * @param                                         $object
	 * @param RokCommon_Annotation_ReflectionProperty $property
	 *
	 * @return mixed|null
	 */
	protected static function getPropertyValue(&$object, RokCommon_Annotation_ReflectionProperty &$property)
	{
		$out = null;
		$tried = false;
		if (empty($out)) {
			if ($property->isPublic() && !$property->isStatic()) {
				$out = $property->getValue($object);
				$tried = true;
			}
		}
		if (empty($out) && !$tried) {
			if ($property->getDeclaringClass()->hasMethod('__get') && $property->getDeclaringClass()->getMethod('__get')->isPublic() && !$property->getDeclaringClass()->getMethod('__get')->isStatic()) {
				$property_name = $property->getName();
				$out           = $object->{$property_name};
				$tried = true;
			}
		}
		if (empty($out) && !$tried){
			$out = self::getPropertyValueFromGetter($object, $property);
			$tried = true;
		}

		return $out;
	}

	/**
	 * @static
	 *
	 * @param                                         $object
	 * @param RokCommon_Annotation_ReflectionProperty $property
	 *
	 * @return mixed|null
	 */
	protected static function getPropertyValueFromGetter(&$object, RokCommon_Annotation_ReflectionProperty &$property)
	{
		/*
		* See if the property has a setter and use that setter if it only has one parameters or
		* if the following parameters are all optional
		*/
		$getter_name = 'get' . ucfirst(preg_replace('/^\W+/', '', $property->getName()));
		if (!$property->getDeclaringClass()->hasMethod($getter_name)) {
			return null;
		}
		$getter = $property->getDeclaringClass()->getMethod($getter_name);
		if (!($getter->isPublic() && !$getter->isStatic())) // Only use a public no static setter
		{
			return null;
		}
		/** @var $getter_params ReflectionParameter[] */
		$getter_params = $getter->getParameters();
		if (count($getter_params) > 0) {
			reset($getter_params); // reset to first parameter
			/** @var $checking_param ReflectionParameter */
			$checking_param = current($getter_params); // get the first
			while ($checking_param = next($getter_params)) {
				if (!$checking_param->isOptional()) {
					return null;
				}
			}
		}

		// call the getter with the property value
		return $getter->invoke($object);
	}


	/**
	 * A wrapper for json_decode that will also map the decoded json string to an object of a specific class if the classname is passed in.
	 *
	 * @static
	 *
	 * @param string    $json
	 * @param string    $classname
	 * @param bool      $assoc
	 * @param bool      $strict
	 *
	 * @return mixed
	 */
	public static function decode($json, $classname = null, $assoc = false, $strict = false)
	{
		$_assoc  = (null != $classname) ? true : $assoc;
		$decoded = json_decode($json, $_assoc);
		if (null == $decoded) {
			throw new RokCommon_JSON_Exception('Error decoding JSON string');
		}
		if (null != $classname) {
			$decoded = self::decodeToObject($decoded, $classname, $strict);
		}
		return $decoded;
	}


	/**
	 * Recursive method to decode a JSON decoded array into an instance of the provided class
	 * @static
	 *
	 * @param array  $json_array
	 * @param string $classname
	 * @param bool   $strict
	 *
	 * @return mixed
	 * @throws RokCommon_JSON_Exception
	 */
	protected static function decodeToObject(array $json_array, $classname, $strict)
	{

		if (!class_exists($classname)) {
			throw new RokCommon_JSON_Exception('Unable to load class: ' . $classname);
		}

		// Make sure the constructor for the object being deserialized is no argument or all defaulted
		$class = new RokCommon_Annotation_ReflectionClass($classname);
		/** @var $constructor RokCommon_Annotation_ReflectionMethod */
		$constructor = $class->getConstructor();
		if (null != $constructor) {
			$all_optional = true;
			/** @var $constructor_params ReflectionParameter[] */
			$constructor_params = $constructor->getParameters();
			if (!empty($constructor_params)) {
				foreach ($constructor_params as $constructor_param) {
					if (!$constructor_param->isOptional()) {
						$all_optional = false;
					}
				}
			}
			if (!$all_optional) {
				throw new RokCommon_JSON_Exception('Classes used with JSON unserialize need to have default or no argument constructor: failed for ' . $classname);
			}
		}

		// make new instance to deserialize into
		$mapped_object = $class->newInstance();

		foreach ($json_array as $json_key_name => $json_value) {

			// Throw an exception if there is no matching property on the class
			if (!$class->hasProperty($json_key_name)) {
				if ($strict) {
					throw new RokCommon_JSON_Exception(sprintf('JSON value does not have matching property %s on class %s', $json_key_name, $classname));
				} else {
					continue;
				}
			}

			$property = $class->getProperty($json_key_name);

			// See if we need to ignore this Decode
			if ($property->hasAnnotation('RokCommon_JSON_Annotation_JSONDecodeIgnore')) {
				continue;
			}

			if (is_array($json_value)) {

			}


			/*
			 * Handle the different types of properties
			 */
			if (self::isPropertyAScalar($property)) {
				//If the property is a scalar or scalar array just set the value
				self::setPropertyValue($mapped_object, $property, $json_value);
			} else {
				if (self::isPropertyAnArray($property) && !is_array($json_value)) {
					throw new RokCommon_JSON_Exception(sprintf('JSON value \'%s\' is not an array or object as expected for property %s on class %s', $json_value, $property->getName(), $class->getName()));
				} elseif (self::isPropertyAnArray($property) && is_array($json_value)) {
					$unserlized_array = array();
					foreach ($json_value as $json_decode_object_key => $json_decode_object_value_array) {
						// get the object from the array
						$decoded_object = self::decodeToObject($json_decode_object_value_array, self::getPropertyType($property), $strict);

						// if there is a default key property for the object class set it on the object
						$default_key_property = self::getDefaultKeyProperty(new RokCommon_Annotation_ReflectionClass(self::getPropertyType($property)));
						if ($default_key_property) {
							self::setPropertyValue($decoded_object, $default_key_property, $json_decode_object_key);
						}

						// add the object to the array
						$unserlized_array[$json_decode_object_key] = $decoded_object;
					}

					// set the array of objects to the current property
					self::setPropertyValue($mapped_object, $property, $unserlized_array);
				} else {
					// property is not an array but the json value is
					// means that its a single object of the property type
					self::setPropertyValue($mapped_object, $property, self::decodeToObject($json_value, self::getPropertyType($property), $strict));
				}
			}
		}
		return $mapped_object;
	}


	/**
	 * @param object                                  $object
	 * @param RokCommon_Annotation_ReflectionProperty $property
	 * @param mixed                                   $value
	 *
	 * @return bool
	 */
	protected function setPropertyValue(&$object, RokCommon_Annotation_ReflectionProperty &$property, $value)
	{
		/*
		 * Try to set by Setter
		 */
		if (self::setPropertyBySetter($value, $property, $object)) {
			return true;
		}

		/*
		 * Ok, that didnt work  lets try the direct approach
		 */
		if ($property->isPublic() && !$property->isStatic()) {
			$property->setValue($object, $value);
			return true;
		}

		/*
		 * well we cant set directly  lets try a magic method
		 */
		if ($property->getDeclaringClass()->hasMethod('__set') && $property->getDeclaringClass()->getMethod('__set')->isPublic() && !$property->getDeclaringClass()->getMethod('__set')->isStatic()) {
			$property_name          = $property->getName();
			$object->{$property_name} = $value;
			return true;
		}

		// well we tried
		return false;
	}

	/**
	 * @param RokCommon_Annotation_ReflectionClass $class
	 *
	 * @return bool|RokCommon_Annotation_ReflectionProperty
	 */
	protected static function &getDefaultKeyProperty(RokCommon_Annotation_ReflectionClass &$class)
	{
		if (!isset(self::$cache[$class->getName()]['_default_key_'])) {
			self::$cache[$class->getName()]['_default_key_'] = false;
			if ($class->hasAnnotation('RokCommon_JSON_Annotation_JSONDefaultKey')) {
				$property                                        = $class->getProperty($class->getAnnotation('RokCommon_JSON_Annotation_JSONDefaultKey')->value);
				self::$cache[$class->getName()]['_default_key_'] = $property;
			}
		}
		return self::$cache[$class->getName()]['_default_key_'];
	}


	/**
	 * Set the value of the property using a setter if the setter is public, non static, and has only one parameter or
	 * if the following parameters are all optional
	 *
	 * @param mixed                                   $value
	 * @param RokCommon_Annotation_ReflectionProperty $property
	 * @param object                                  $object
	 *
	 * @return bool true if the value was set by the setter, false if not
	 */
	protected static function setPropertyBySetter($value, RokCommon_Annotation_ReflectionProperty &$property, &$object)
	{
		/*
		* See if the property has a setter and use that setter if it only has one parameters or
		* if the following parameters are all optional
		*/
		$setter_name = 'set' . ucfirst(preg_replace('/^\W+/', '', $property->getName()));
		if (!$property->getDeclaringClass()->hasMethod($setter_name)) {
			return false;
		}
		$setter = $property->getDeclaringClass()->getMethod($setter_name);
		if (!($setter->isPublic() && !$setter->isStatic())) // Only use a public no static setter
		{
			return false;
		}
		/** @var $setter_params ReflectionParameter[] */
		$setter_params = $setter->getParameters();
		if (count($setter_params) > 1) {
			reset($setter_params); // reset to first parameter
			/** @var $checking_param ReflectionParameter */
			$checking_param = next($setter_params); // skip the first param
			do {
				if (!$checking_param->isOptional()) {
					return false;
				}
			} while ($checking_param = next($setter_params));
		}

		// call the setter with the property value
		$setter->invoke($object, $value);
		return true;
	}

	/**
	 * Helper function to determine if a passed data type description is a scalar data type.
	 * @static
	 *
	 * @param \ReflectionProperty $property
	 *
	 * @return bool
	 */
	protected static function isPropertyAScalar(ReflectionProperty &$property)
	{
		$prop_info = self::getPropertyInfoFromDocs($property);
		switch (strtolower($prop_info->type)) {
			case 'int':
			case 'integer':
			case 'bool':
			case 'boolean':
			case 'string':
			case 'float':
			case 'double':
			case 'number':
				return true;
			default:
				return false;
		}
	}

	/**
	 * @static
	 *
	 * @param \ReflectionProperty $property
	 *
	 * @return bool
	 */
	protected static function isPropertyAnArray(ReflectionProperty &$property)
	{
		$prop_info = self::getPropertyInfoFromDocs($property);
		return $prop_info->array;
	}

	/**
	 * Get the type of the property form the @var doc tag
	 * @static
	 *
	 * @param ReflectionProperty $property
	 *
	 * @return mixed
	 */
	protected static function getPropertyType(ReflectionProperty &$property)
	{
		$prop_info = self::getPropertyInfoFromDocs($property);
		return $prop_info->type;
	}


	//protected static function getPropertyInfo()
	/**
	 * Method to get the data type form the PHPDoc block of a class property
	 * @static
	 *
	 * @param ReflectionProperty $property
	 *
	 * @return null|string
	 */
	protected static function getPropertyInfoFromDocs(ReflectionProperty &$property)
	{
		// See if its in the cache and if not process
		if (!isset(self::$cache[$property->getDeclaringClass()->getName()][$property->getName()])) {
			$docComment = $property->getDocComment();
			if (trim($docComment) == '') {
				return null;
			}
			$docComment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docComment);
			$docComment = ltrim($docComment, "\r\n");
			$docComment = rtrim($docComment, "*/");
			if (substr_count($docComment, "\n") == 0) $docComment .= "\n";
			$parsedDocComment   = $docComment;
			$lineNumber         = $firstBlandLineEncountered = 0;
			$results            = new stdClass();
			$results->full_type = 'stdClass';
			$results->type      = 'stdClass';
			$results->array     = false;
			while (($newlinePos = strpos($parsedDocComment, "\n")) !== false) {
				$lineNumber++;
				$line = substr($parsedDocComment, 0, $newlinePos);

				$content_matches = array();
				if ((strpos($line, '@') === 0) && (preg_match('#^(@\w+.*?)(\n)(?:@|\r?\n|$)#s', $parsedDocComment, $content_matches))) {
					$tagDocblockLine = $content_matches[1];
					$tag_matches     = array();

					if (!preg_match('#^@(\w+)(\s|$)#', $tagDocblockLine, $tag_matches)) {
						break;
					}
					$type_matches = array();
					if (!preg_match('#^@(\w+)\s+([\w|\\\]+[\[\]]*)(?:\s+(\$\S+))?(?:\s+(.*))?#s', $tagDocblockLine, $type_matches)) {
						break;
					}

					if (strtolower($type_matches[1]) == 'var') {
						$results->full_type = $type_matches[2];
						break;
					}
					$parsedDocComment = str_replace($content_matches[1] . $content_matches[2], '', $parsedDocComment);
				}
			}

			// check if its an array;
			$array_matches = array();
			preg_match('#^([\w|\\\]+)(\[\])?$#', $results->full_type, $array_matches);
			$results->type  = $array_matches[1];
			$results->array = isset($array_matches[2]);

			// set the cached value
			self::$cache[$property->getDeclaringClass()->getName()][$property->getName()] = $results;
		}
		return self::$cache[$property->getDeclaringClass()->getName()][$property->getName()];
	}


	/**
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 *
	 * @throws ErrorException
	 */
	public function exception_error_handler($errno, $errstr, $errfile, $errline)
	{
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

}
