<?php
/**
 * @package angifw
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 *
 * This file may contain code from the Joomla! Platform, Copyright (c) 2005 -
 * 2012 Open Source Matters, Inc. This file is NOT part of the Joomla! Platform.
 * It is derivative work and clearly marked as such as per the provisions of the
 * GNU General Public License.
 */

defined('_AKEEBA') or die();

/**
 * Query Building Class.
 */
class ADatabaseQuerySqlsrv extends ADatabaseQuery
{
	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc.  The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 *
	 */
	protected $name_quotes = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 *
	 */
	protected $null_date = '1900-01-01 00:00:00';

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string	The completed query.
	 *
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->type)
		{
			case 'insert':
				$query .= (string) $this->insert;

				// Set method
				if ($this->set)
				{
					$query .= (string) $this->set;
				}
				// Columns-Values method
				elseif ($this->values)
				{
					if ($this->columns)
					{
						$query .= (string) $this->columns;
					}

					$elements = $this->insert->getElements();
					$tableName = array_shift($elements);

					$query .= 'VALUES ';
					$query .= (string) $this->values;

					if ($this->autoIncrementField)
					{
						$query = 'SET IDENTITY_INSERT ' . $tableName . ' ON;' . $query . 'SET IDENTITY_INSERT ' . $tableName . ' OFF;';
					}

					if ($this->where)
					{
						$query .= (string) $this->where;
					}
				}

				break;

			default:
				$query = parent::__toString();
				break;
		}

		return $query;
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * @param   string  $value  The value to cast as a char.
	 *
	 * @return  string  Returns the cast value.
	 *
	 */
	public function castAsChar($value)
	{
		return 'CAST(' . $value . ' as NVARCHAR(10))';
	}

	/**
	 * Gets the function to determine the length of a character string.
	 *
	 * @param   string  $field      A value.
	 * @param   string  $operator   Comparison operator between charLength integer value and $condition
	 * @param   string  $condition  Integer value to compare charLength with.
	 *
	 * @return  string  The required char length call.
	 *
	 */
	public function charLength($field, $operator = null, $condition = null)
	{
		return 'DATALENGTH(' . $field . ')' . (isset($operator) && isset($condition) ? ' ' . $operator . ' ' . $condition : '');
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			return '(' . implode('+' . $this->quote($separator) . '+', $values) . ')';
		}
		else
		{
			return '(' . implode('+', $values) . ')';
		}
	}

	/**
	 * Gets the current date and time.
	 *
	 * @return  string
	 *
	 */
	public function currentTimestamp()
	{
		return 'GETDATE()';
	}

	/**
	 * Get the length of a string in bytes.
	 *
	 * @param   string  $value  The string to measure.
	 *
	 * @return  integer
	 *
	 */
	public function length($value)
	{
		return 'LEN(' . $value . ')';
	}
}
