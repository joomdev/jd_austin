<?php
/**
 * @version   $Id: Record.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Doctrine_Record extends Doctrine_Record
{
	/**
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();

		$classname = get_class($this) . "Filter";
		if (class_exists($classname, true) && is_subclass_of($classname, 'Doctrine_Record_Filter')) {
			$this->unshiftFilter(new $classname());
		}
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function addField($name, $value)
	{
		$this->{$name} = $value;
	}

	/**
	 * returns the record representation as an array
	 *
	 * @link http://www.doctrine-project.org/documentation/manual/1_1/en/working-with-models
	 *
	 * @param boolean $deep         whether to include relations
	 * @param boolean $prefixKey    not used
	 *
	 * @return array
	 */
	public function toJsonableArray($deep = true, $prefixKey = false)
	{
		if ($this->_state == self::STATE_LOCKED || $this->_state == self::STATE_TLOCKED) {
			return false;
		}

		$stateBeforeLock = $this->_state;
		$this->_state    = $this->exists() ? self::STATE_LOCKED : self::STATE_TLOCKED;

		$a = array();

		foreach ($this as $column => $value) {
			if ($value === self::$_null || is_object($value)) {
				$value = null;
			}

			$columnValue = $this->get($column, false);

			if ($columnValue instanceof Doctrine_Record) {
				$a[$column] = $columnValue->getIncremented();
			} else {
				$a[$column] = $columnValue;
			}
		}

		if ($this->_table->getIdentifierType() == Doctrine_Core::IDENTIFIER_AUTOINC) {
			$i     = $this->_table->getIdentifier();
			$a[$i] = $this->getIncremented();
		}

		if ($deep) {
			foreach ($this->_references as $key => $relation) {
				if (!$relation instanceof Doctrine_Null) {
					$a[$key] = $relation->toArray($deep, $prefixKey);
					$link    = $this->getTable()->getRelation($key)->getForeignColumnName();
					if ($relation instanceof Doctrine_Record) {
						unset($a[$key][$link]);
					} elseif ($relation instanceof Doctrine_Collection) {
						foreach ($a[$key] as $relkey => $relation_item) {
							unset($relation_item[$link]);
							$a[$key][$relkey] = $relation_item;
						}
					}

				}
			}
		}

		// [FIX] Prevent mapped Doctrine_Records from being displayed fully
		foreach ($this->_values as $key => $value) {
			$a[$key] = ($value instanceof Doctrine_Record || $value instanceof Doctrine_Collection) ? $value->toArray($deep, $prefixKey) : $value;
		}

		$this->_state = $stateBeforeLock;

		return $a;
	}


	/**
	 * Contains fix for filterget performace issue
	 *
	 * @throws Doctrine_Exception|Doctrine_Table_Exception
	 *
	 * @param      $fieldName
	 * @param bool $load
	 *
	 * @return null
	 */
	protected function _get($fieldName, $load = true)
	{
		$value = self::$_null;

		if (array_key_exists($fieldName, $this->_values)) {
			return $this->_values[$fieldName];
		}

		if (array_key_exists($fieldName, $this->_data)) {
			// check if the value is the Doctrine_Null object located in self::$_null)
			if ($this->_data[$fieldName] === self::$_null && $load) {
				$this->load();
			}

			if ($this->_data[$fieldName] === self::$_null) {
				$value = null;
			} else {
				$value = $this->_data[$fieldName];
			}

			return $value;
		}

		if (isset($this->_references[$fieldName])) {
			if ($this->_references[$fieldName] === self::$_null) {
				return null;
			}
			return $this->_references[$fieldName];
		}

		$rel = $this->_table->getRelation($fieldName);
		if ($load && $rel !== false) {
			$this->_references[$fieldName] = $rel->fetchRelatedFor($this);
		} elseif ($rel === false) {
			$success = false;
			$value   = null;
			foreach ($this->_table->getFilters() as $filter) {
				try {
					$value   = $filter->filterGet($this, $fieldName);
					$success = true;
					break;
				} catch (Doctrine_Exception $e) {
				}
			}
			if ($success) {
				return $value;
			} else {
				throw $e;
			}
		} else {
			$this->_references[$fieldName] = null;
		}

		if (!isset($this->_references[$fieldName])) {
			if ($load) {
				$rel                           = $this->_table->getRelation($fieldName);
				$this->_references[$fieldName] = $rel->fetchRelatedFor($this);
			} else {
				$this->_references[$fieldName] = null;
			}
		}

		if ($this->_references[$fieldName] === self::$_null) {
			return null;
		}


		return $this->_references[$fieldName];
	}

}
