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
 * SQL Server database driver
 */
class ADatabaseDriverSqlazure extends ADatabaseDriverSqlsrv
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 */
	public $name = 'sqlzure';

	/**
	 * @var    string  The database technology family supported, e.g. mysql, mssql
	 */
	public static $dbtech = 'mssql';

}
