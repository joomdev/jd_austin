<?php
/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelDatabase extends AModel
{
	/**
	 * The databases.ini contents
	 *
	 * @var array
	 */
	private $dbini = array();

	/**
	 * Returns the cached databases.ini information, parsing the databases.ini
	 * file if necessary.
	 *
	 * @return array
	 */
	public function getDatabasesIni()
	{
		if (empty($this->dbini))
		{
			$this->dbini = $this->container->session->get('databases.dbini', array());

			if (empty($this->dbini))
			{
				$filename = APATH_INSTALLATION . '/sql/databases.ini';

				if (file_exists($filename))
				{
					$this->dbini = AngieHelperIni::parse_ini_file($filename, true);
				}

				if (!empty($this->dbini))
				{
					// Add the custom options
					$temp    = array();
					$siteSQL = null;

					foreach ($this->dbini as $key => $data)
					{
						if (!array_key_exists('dbtech', $data))
						{
							$data['dbtech'] = null;
						}

						// Skip section that have the db tech set to none (flat-file CMS)
						if (strtolower($data['dbtech']) == 'none')
						{
							continue;
						}

						if (!array_key_exists('existing', $data))
						{
							$data['existing'] = 'drop';
						}

						if (!array_key_exists('prefix', $data))
						{
							$data['prefix'] = 'jos_';
						}

						if (!array_key_exists('foreignkey', $data))
						{
							$data['foreignkey'] = true;
						}

						if (!array_key_exists('noautovalue', $data))
						{
							$data['noautovalue'] = true;
						}

						if (!array_key_exists('replace', $data))
						{
							$data['replace'] = false;
						}

						if (!array_key_exists('utf8db', $data))
						{
							$data['utf8db'] = false;
						}

						if (!array_key_exists('utf8tables', $data))
						{
							$data['utf8tables'] = false;
						}

						if (!array_key_exists('utf8mb4', $data))
						{
							$data['utf8mb4'] = defined('ANGIE_ALLOW_UTF8MB4_DEFAULT') ? ANGIE_ALLOW_UTF8MB4_DEFAULT : false;
						}

						if (!array_key_exists('maxexectime', $data))
						{
							$data['maxexectime'] = 5;
						}

						if (!array_key_exists('throttle', $data))
						{
							$data['throttle'] = 250;
						}

						// If we are using SQLite, let's replace any token we found inside the dbname index
						if ($data['dbtype'] == 'sqlite')
						{
							$data['dbname'] = str_replace('#SITEROOT#', APATH_ROOT, $data['dbname']);
						}

						if ($key == 'site.sql')
						{
							$siteSQL = $data;
						}
						else
						{
							$temp[ $key ] = $data;
						}
					}

					// Add the site db definition only if it was defined
					if ($siteSQL)
					{
						$temp = array_merge(array('site.sql' => $siteSQL), $temp);
					}

					$this->dbini = $temp;
				}

                $this->container->session->set('databases.dbini', $this->dbini);
			}
		}

		return $this->dbini;
	}

	/**
	 * Saves the (modified) databases information to the session
	 */
	public function saveDatabasesIni()
	{
        $this->container->session->set('databases.dbini', $this->dbini);
	}

	/**
	 * Returns the keys of all available database definitions
	 *
	 * @return array
	 */
	public function getDatabaseNames()
	{
		$dbini = $this->getDatabasesIni();

		return array_keys($dbini);
	}

	/**
	 * Returns an object with a database's connection information
	 *
	 * @param   string $key The database's key (name of SQL file)
	 *
	 * @return  null|stdClass
	 */
	public function getDatabaseInfo($key)
	{
		$dbini = $this->getDatabasesIni();

		if (array_key_exists($key, $dbini))
		{
			return (object) $dbini[ $key ];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Sets a database's connection information
	 *
	 * @param   string $key  The database's key (name of SQL file)
	 * @param   mixed  $data The database's data (stdObject or array)
	 */
	public function setDatabaseInfo($key, $data)
	{
		$dbini = $this->getDatabasesIni();

		$this->dbini[ $key ] = (array) $data;

		$this->saveDatabasesIni();
	}

	/**
	 * Detects if we have a flag file for large columns; if so it returns its contents (longest query we will have to run)
	 *
	 * @return  int
	 */
	public function largeTablesDetected()
	{
		$file = APATH_INSTALLATION.'/large_tables_detected';

		if (!file_exists($file))
		{
			return 0;
		}

		$bytes  = (int) file_get_contents($file);

		return $bytes;
	}
}
