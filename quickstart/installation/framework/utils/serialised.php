<?php
/**
 * @package angifw
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

/**
 * Exception class for serialised data parsing
 */
class AUtilsSerialisedDecodingException extends Exception {};

/**
 * Exception class for serialised data encoding
 */
class AUtilsSerialisedEncodingException extends Exception {};

/**
 * A class to manipulate PHP serialised data without using unserialise(). This is useful if the serialised data contains
 * references to classes which are not present in the PHP code whcih
 */
class AUtilsSerialised
{
	/**
	 * Does this string look like PHP serialised data? Please note that this is a quick pre-test. You cannot be sure
	 * that it's valid serialised data until you try decoding it.
	 *
	 * @param string $string The string to test
	 *
	 * @return boolean True if it looks like serialised data
	 */
	public function isSerialised($string)
	{
		$scalar = array('s:', 'i:', 'b:', 'd:');
		$structured = array('a:', 'O:');

		// Is it null?
		if ($string == 'N;')
		{
			return true;
		}

		// Is it scalar?
		if (in_array(substr($string, 0, 2), $scalar))
		{
			return substr($string, -1) == ';';
		}

		// Is it structured?
		if (!in_array(substr($string, 0, 2), $structured))
		{
			return false;
		}

		// Do we have a semicolon to denote the object length?
		$semicolonPos = strpos($string, ':', 3);

		if ($semicolonPos === false)
		{
			return false;
		}

		// Do we have another semicolon afterwards?
		$secondPos = strpos($string, ':', $semicolonPos + 1);

		if ($secondPos === false)
		{
			return false;
		}

		// Is the length an integer?
		$length = substr($string, $semicolonPos + 1, $secondPos - $semicolonPos - 1);

		return (int)$length == $length;
	}

	/**
	 * Checks whether a given array looks like a proper decoded serialiased value
	 *
	 * @param array $arr The array to check
	 *
	 * @return bool True if it looks like a proper decoded serialiased value
	 */
	public function isDecoded(array $arr)
	{
		if (empty($arr))
		{
			return false;
		}

		$keys = array_keys($arr);
		$properKeys = array('type', 'class', 'length', 'value');

		// Make sure all contained keys are expected
		foreach ($keys as $k)
		{
			if (!in_array($k, $properKeys))
			{
				return false;
			}
		}

		// Make sure all expected keys are present
		foreach ($properKeys as $k)
		{
			if (!in_array($k, $keys))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Decodes a serialised string into a structured array. The array keys are:
	 * - type            s (string), i (integer), d (decimal), b (bool), N (null), O (object), a (array)
	 * - class            Class name, only for type O
	 * - length            Length, only for types s (string length), O (number of properties), a (number of elements)
	 * - value            Scalar value for types s, i, d, b; null for type N; array of 2 * length elements for types O, a
	 *
	 * @param string $string
	 *
	 * @return array See above
	 *
	 * @throws AUtilsSerialisedDecodingException When the serialised data cannot be decoded
	 */
	public function decode($string)
	{
		if (!$this->isSerialised($string))
		{
			throw new AUtilsSerialisedDecodingException('String is not serialised data');
		}

		$charCount = 0;
		$temp = $this->decodeString($string, $charCount);

		return array_shift($temp);
	}

	/**
	 * Encodes an array with serialised data into a serialised string.
	 *
	 * @param array $arr The array of serialised decoded data @see decode()
	 *
	 * @return string
	 *
	 * @throws AUtilsSerialisedEncodingException
	 */
	public function encode(array $arr)
	{
		if (!$this->isDecoded($arr))
		{
			throw new AUtilsSerialisedEncodingException('Array is not decoded serialised data');
		}

		$charCount = 0;
		return $this->encodeArray(array($arr));
	}

	public function replaceText($serialised, $from, $to)
	{
		$decoded = $this->decode($serialised);

		$this->replaceTextInDecoded($decoded, $from, $to);

		return $this->encode($decoded);
	}

	public function replaceTextInDecoded(&$decoded, &$from, &$to)
	{
		switch ($decoded['type'])
		{
			case 's':
				$decoded['value'] = str_replace($from, $to, $decoded['value']);
				$decoded['length'] = strlen($decoded['value']);
				break;

			case 'a':
			case 'O':
				foreach($decoded['value'] as $k => $element)
				{
					if (in_array($element['type'], array('s', 'a', 'O')))
					{
						$this->replaceTextInDecoded($element, $from, $to);
						$decoded['value'][$k] = $element;
					}
				}
				break;

			default:
				break;
		}
	}

	/**
	 * Recursive function to decode serialised / partial serialised strings
	 *
	 * @param string $string
	 * @param int $scopeFrom Initialize with zero
	 *
	 * @return array
	 *
	 * @throws AUtilsSerialisedDecodingException
	 */
	protected function decodeString(&$string, &$scopeFrom)
	{
		$ret = array();
		$strlen = strlen($string);

		while ($scopeFrom < $strlen)
		{
			$element = array(
				'type'   => 'N',
				'class'  => null,
				'length' => 0,
				'value'  => null,
			);

			$type = $string[$scopeFrom];

			// If we have an end of structure (}) break the loop
			if ($type == '}')
			{
				$scopeFrom += 1;
				break;
			}

			$colon = $string[$scopeFrom + 1];

			// Parse null values
			if ($type == 'N')
			{
				// A null value type MUST end with a semicolon
				if ($colon != ';')
				{
					throw new AUtilsSerialisedDecodingException("Invalid token {$type}{$colon} at $scopeFrom");
				}

				$scopeFrom += 2;

				$ret[] = $element;

				continue;
			}

			// All other types MUST be followed by a colon
			if ($colon != ':')
			{
				throw new AUtilsSerialisedDecodingException("Invalid token {$type}{$colon} at $scopeFrom");
			}

			// Set the element type
			$element['type'] = $type;

			$scopeFrom += 2;

			// Objects are followed by class length and class name
			if ($type == 'O')
			{
				$colonPos = strpos($string, ':', $scopeFrom);

				if ($colonPos === false)
				{
					throw new AUtilsSerialisedDecodingException("Expected colon at $scopeFrom");
				}

				$classLength = (int)substr($string, $scopeFrom, ($colonPos - $scopeFrom) + 1);

				$scopeFrom = $colonPos + 1;
				$className = substr($string, $scopeFrom, $classLength + 3);

				if (($className[0] != '"') || (substr($className, -2) != '":'))
				{
					throw new AUtilsSerialisedDecodingException("Expected class name in double quotes at $scopeFrom");
				}

				$element['class'] = substr($className, 1, -2);
				$scopeFrom += $classLength + 3;
			}

			// Types s, O, a are followed by a length
			if (in_array($type, array('s', 'O', 'a')))
			{
				$colonPos = strpos($string, ':', $scopeFrom);

				if ($colonPos === false)
				{
					throw new AUtilsSerialisedDecodingException("Expected colon at $scopeFrom");
				}

				$element['length'] = (int)substr($string, $scopeFrom, $colonPos - $scopeFrom);

				$scopeFrom = $colonPos + 1;
			}

			switch ($type)
			{
				// Simple scalars. Look for end of data and parse value
				case 'i':
				case 'b':
				case 'd':
					$endOfData = strpos($string, ';', $scopeFrom);

					if ($endOfData === false)
					{
						throw new AUtilsSerialisedDecodingException("End-of-data not found for {$type} at $scopeFrom");
					}

					$element['value'] = substr($string, $scopeFrom, $endOfData - $scopeFrom);
					$scopeFrom = $endOfData + 1;

					$ret[] = $element;
					continue;

					break;

				// Strings. We expect "string"; where string is $element['length'] characters long
				case 's':
					$rawString = substr($string, $scopeFrom, $element['length'] + 3);

					if (($rawString[0] != '"') || substr($rawString, -2) != '";')
					{
						throw new AUtilsSerialisedDecodingException("Invalid string data at $scopeFrom");
					}

					$element['value'] = substr($rawString, 1, -2);

					$scopeFrom += $element['length'] + 3;

					$ret[] = $element;
					continue;

					break;

				// Structures. We have a start-of-structure ({) followed by serialised data. Recurse.
				case 'a':
				case 'O':
					$startOfStructure = $string[$scopeFrom];

					if ($startOfStructure != '{')
					{
						throw new AUtilsSerialisedDecodingException("Invalid start of structured data at $scopeFrom");
					}

					$scopeFrom += 1;

					$element['value'] = $this->decodeString($string, $scopeFrom);

					$num = count($element['value']);
					$exp = 2 * $element['length'];

					if ($num != $exp)
					{
						throw new AUtilsSerialisedDecodingException("Invalid number of structured data at $scopeFrom. Got $num, expected $exp");
					}

					$ret[] = $element;
					continue;

					break;

				default:
					throw new AUtilsSerialisedDecodingException("Unknown data type $type at $scopeFrom");
					break;
			}
		}

		return $ret;
	}

	/**
	 * Encodes the serialised decoded array back to a serialised string
	 *
	 * @param array $arr The array to encode
	 *
	 * @return string Encoded serialised data
	 *
	 * @throws AUtilsSerialisedEncodingException
	 */
	protected function encodeArray($arr)
	{
		$ret = '';

		foreach ($arr as $element)
		{
			switch ($element['type'])
			{
				case 'N':
					$ret .= 'N;';
					break;

				case 'b':
					$element['value'] = $element['value'] ? '1' : '0';
					$ret .= $element['type'] . ':' . $element['value'] . ';';
					break;

				case 'i':
				case 'd':
					$ret .= $element['type'] . ':' . $element['value'] . ';';
					break;

				case 's':
					$ret .= 's:' . $element['length'] . ':"' . $element['value'] . '";';
					break;

				case 'O':
					$ret .= 'O:' . strlen($element['class']) . ':"' . $element['class'] . '":' .
						$element['length'] . ':{' . $this->encodeArray($element['value']) . '}';
					break;

				case 'a':
					$ret .= 'a:' . $element['length'] . ':{' . $this->encodeArray($element['value']) . '}';
					break;

				default:
					throw new AUtilsSerialisedEncodingException("Unknown data type {$element['type']}");
			}
		}

		return $ret;
	}
}
