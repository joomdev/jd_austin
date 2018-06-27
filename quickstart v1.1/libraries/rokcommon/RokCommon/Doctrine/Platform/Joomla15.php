<?php
/**
 * @version   $Id: Joomla15.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


/**
 *
 */
class RokCommon_Doctrine_Platform_Joomla15 implements RokCommon_Doctrine_Platform
{
	/**
	 * @var JRegistry
	 */
	protected $config;

	/**
	 *
	 */
	public function __construct()
	{
		$this->config = JFactory::getConfig();
	}

	/**
	 * @param string $tablename
	 *
	 * @return string
	 */
	public function setTableName($tablename)
	{
		return $this->config->getValue('config.dbprefix') . $tablename;
	}

	/**
	 * @return string a doctrine safe tablename format
	 */
	public function getTableNameFormat()
	{
		return $this->config->getValue('config.dbprefix') . '_%s';
	}

	/**
	 * @return string a doctrine safe connection URL
	 */
	public function getConnectionUrl()
	{
		$host = $this->config->getValue('config.host') != '' ? $this->config->getValue('config.host') : 'localhost';

		$url = 'mysql';
		$url .= '://';
		$url .= urlencode($this->config->getValue('config.user'));
		$url .= ':';
		$url .= urlencode($this->config->getValue('config.password'));
		$url .= '@';
		$url .= $host;
		$url .= '/';
		$url .= $this->config->getValue('config.db');
		return $url;
	}

	/**
	 * @return string the schema name for the platform
	 */
	public function getSchema()
	{
		return $this->config->getValue('config.db');
	}

	/**
	 * @return string the database username for the platform
	 */
	public function getUsername()
	{
		return $this->config->getValue('config.user');
	}

	/**
	 * @return string the database password for the platform
	 */
	public function getPassword()
	{
		return $this->config->getValue('config.password');
	}

	/**
	 * @return string the database hostname for the platform
	 */
	public function getHost()
	{
		return $this->config->getValue('config.host');
	}


}
