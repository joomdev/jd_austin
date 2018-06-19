<?php
/**
 * @version   $Id: Base.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
abstract class RokCommon_Doctrine_Migration_Base extends Doctrine_Migration_Base
{
	/**
	 * Add a create or drop table change.
	 *
	 * @param string $upDown     Whether to add the up(create) or down(drop) table change.
	 * @param string $tableName  Name of the table
	 * @param array  $fields     Array of fields for table
	 * @param array  $options    Array of options for the table
	 *
	 * @return void
	 */
	public function table($upDown, $tableName, array $fields = array(), array $options = array())
	{
		parent::table($upDown, RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $fields, $options);
	}

	/**
	 * Add a rename table change
	 *
	 * @param string $oldTableName      Name of the table to change
	 * @param string $newTableName      Name to change the table to
	 *
	 * @return void
	 */
	public function renameTable($oldTableName, $newTableName)
	{
		parent::renameTable(RokCommon_Doctrine::getPlatformInstance()->setTableName($oldTableName), RokCommon_Doctrine::getPlatformInstance()->setTableName($newTableName));
	}

	/**
	 * Add a create or drop constraint change.
	 *
	 * @param string $upDown            Whether to add the up(create) or down(drop) create change.
	 * @param string $tableName         Name of the table.
	 * @param string $constraintName    Name of the constraint.
	 * @param array  $definition        Array for the constraint definition.
	 *
	 * @return void
	 */
	public function constraint($upDown, $tableName, $constraintName, array $definition)
	{
		parent::constraint($upDown, RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $constraintName, $definition);
	}


	/**
	 * Convenience method for creating or dropping primary keys.
	 *
	 * @param string $direction
	 * @param string $tableName     Name of the table
	 * @param string $columnNames   Array of column names and column definitions
	 *
	 * @return void
	 */
	public function primaryKey($direction, $tableName, $columnNames)
	{
		parent::primaryKey($direction, RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $columnNames);
	}


	/**
	 * Add a create or drop foreign key change.
	 *
	 * @param string $upDown        Whether to add the up(create) or down(drop) foreign key change.
	 * @param string $tableName     Name of the table.
	 * @param string $name          Name of the foreign key.
	 * @param array  $definition    Array for the foreign key definition
	 *
	 * @return void
	 */
	public function foreignKey($upDown, $tableName, $name, array $definition = array())
	{
		parent::foreignKey($upDown, RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $name, $definition);
	}

	/**
	 * Add a add or remove column change.
	 *
	 * @param string $upDown        Whether to add the up(add) or down(remove) column change.
	 * @param string $tableName     Name of the table
	 * @param string $columnName    Name of the column
	 * @param string $type          Type of the column
	 * @param string $length        Length of the column
	 * @param array  $options       Array of options for the column
	 *
	 * @return void
	 */
	public function column($upDown, $tableName, $columnName, $type = null, $length = null, array $options = array())
	{
		parent::column($upDown, RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $columnName, $type, $length, $options);
	}

	/**
	 * Add a rename column change
	 *
	 * @param string $tableName         Name of the table to rename the column on
	 * @param string $oldColumnName     The old column name
	 * @param string $newColumnName     The new column name
	 *
	 * @return void
	 */
	public function renameColumn($tableName, $oldColumnName, $newColumnName)
	{
		parent::renameColumn(RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $oldColumnName, $newColumnName);
	}

	/**
	 * Add a change column change
	 *
	 * @param string $tableName     Name of the table to change the column on
	 * @param string $columnName    Name of the column to change
	 * @param string $type          New type of column
	 * @param string $length        The length of the column
	 * @param array  $options       New options for the column
	 *
	 * @return void
	 */
	public function changeColumn($tableName, $columnName, $type = null, $length = null, array $options = array())
	{
		parent::changeColumn(RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $columnName, $type, $length, $options);
	}

	/**
	 * Add a add or remove index change.
	 *
	 * @param string $upDown       Whether to add the up(add) or down(remove) index change.
	 * @param string $tableName    Name of the table
	 * @param string $indexName    Name of the index
	 * @param array  $definition   Array for the index definition
	 *
	 * @return void
	 */
	public function index($upDown, $tableName, $indexName, array $definition = array())
	{
		parent::index($upDown, RokCommon_Doctrine::getPlatformInstance()->setTableName($tableName), $indexName, $definition);
	}
}