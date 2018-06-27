<?php
/**
 * @version   $Id: Wordpress3.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


/**
 *
 */
class RokCommon_Doctrine_Platform_Wordpress3 implements RokCommon_Doctrine_Platform
{
	/**
	 * @var
	 */
	protected $config;

	/**
	 *
	 */
	public function __construct()
	{
		global $wpdb;
		$this->config = $wpdb;
	}

	/**
	 * @param string $tablename
	 *
	 * @return string
	 */
	public function setTableName($tablename)
	{
		return $this->config->prefix . $tablename;
	}

	/**
	 * @return string a doctrine safe tablename format
	 */
	public function getTableNameFormat()
	{
		return $this->config->prefix . '_%s';
	}

	/**
	 * @return string a doctrine safe connection URL
	 */
	public function getConnectionUrl()
	{
		$host       = $this->config->dbhost != '' ? $this->config->dbhost : (DB_HOST != '' ? DB_HOST : 'localhost');
		$dbpassword = !is_null($this->config->dbpassword) ? $this->config->dbpassword : DB_PASSWORD;
		$dbuser     = !is_null($this->config->dbuser) ? $this->config->dbuser : DB_USER;
		$dbname     = !is_null($this->config->dbname) ? $this->config->dbname : DB_NAME;

		$url = 'mysql';
		$url .= '://';
		$url .= urlencode($dbuser);
		$url .= ':';
		$url .= urlencode($dbpassword);
		$url .= '@';
		$url .= $host;
		$url .= '/';
		$url .= $dbname;
		return $url;
	}

	/**
	 * @return string the schema name for the platform
	 */
	public function getSchema()
	{
		return $this->config->dbname;
	}

	/**
	 * @return string the database username for the platform
	 */
	public function getUsername()
	{
		return $this->config->dbuser;
	}

	/**
	 * @return string the database password for the platform
	 */
	public function getPassword()
	{
		return $this->config->dbpassword;
	}

	/**
	 * @return string the database hostname for the platform
	 */
	public function getHost()
	{
		return $this->config->dbhost;
	}
}
