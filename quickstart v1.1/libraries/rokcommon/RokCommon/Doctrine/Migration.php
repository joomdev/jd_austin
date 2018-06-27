<?php
/**
 * @version   $Id: Migration.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Based on with Original License
 *            THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 *            LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 *            A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 *            OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *            SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 *            LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 *            DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 *            THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *            OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

defined('ROKCOMMON') or die;
/**
 * Doctrine_Migration
 *
 * this class represents a database view
 *
 * @package     Doctrine
 * @subpackage  Migration
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 1080 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class RokCommon_Doctrine_Migration extends Doctrine_Migration
{
	/**
	 * Specify the path to the directory with the migration classes.
	 * The classes will be loaded and the migration table will be created if it
	 * does not already exist
	 *
	 * @param string $directory  The path to your migrations directory
	 * @param mixed  $connection The connection name or instance to use for this migration
	 *
	 * @return \RokCommon_Doctrine_Migration
	 */
	public function __construct($directory = null, $connection = null)
	{
		$this->_reflectionClass = new ReflectionClass('Doctrine_Migration_Base');

		if (is_null($connection)) {
			$this->_connection = Doctrine_Manager::connection();
		} else {
			if (is_string($connection)) {
				$this->_connection = Doctrine_Manager::getInstance()->getConnection($connection);
			} else {
				$this->_connection = $connection;
			}
		}

		$this->_process = new Doctrine_Migration_Process($this);

		if ($directory != null) {
			$this->_migrationClassesDirectory = $directory;

			$this->loadMigrationClassesFromDirectory();
		}
	}

	/**
	 * Get the table name for storing the version number for this migration instance
	 *
	 * @return string $migrationTableName
	 */
	public function getTableName()
	{
		return RokCommon_Doctrine::getPlatformInstance()->setTableName($this->_migrationTableName);
	}

	/**
	 * Load migration classes from the passed directory. Any file found with a .php
	 * extension will be passed to the loadMigrationClass()
	 *
	 * @param string $directory  Directory to load migration classes from
	 *
	 * @return void
	 */
	public function loadMigrationClassesFromDirectory($directory = null)
	{
		$directory = $directory ? $directory : $this->_migrationClassesDirectory;

		$classesToLoad = array();
		$classes       = get_declared_classes();
		foreach ((array)$directory as $dir) {
			$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::LEAVES_ONLY);

			if (isset(self::$_migrationClassesForDirectories[$dir])) {
				foreach (self::$_migrationClassesForDirectories[$dir] as $num => $className) {
					$this->_migrationClasses[$num] = $className;
				}
			}

			foreach ($it as $file) {
				$info = pathinfo($file->getFileName());
				if (isset($info['extension']) && $info['extension'] == 'php') {
					require_once($file->getPathName());

					$array     = array_diff(get_declared_classes(), $classes);
					$className = end($array);

					if ($className) {
						$e         = explode('_', $file->getFileName());
						$timestamp = $e[0];

						$classesToLoad[$timestamp] = array(
							'className' => $className,
							'path'      => $file->getPathName()
						);
					}
				}
			}
		}
		ksort($classesToLoad, SORT_NUMERIC);
		foreach ($classesToLoad as $class) {
			$this->loadMigrationClass($class['className'], $class['path']);
		}
	}

	/**
	 * Set the current version of the database
	 *
	 * @param integer $number
	 *
	 * @return void
	 */
	public function setCurrentVersion($number)
	{
		if ($this->hasMigrated()) {
			$this->_connection->exec("UPDATE " . $this->getTableName() . " SET version = $number");
		} else {
			$this->_connection->exec("INSERT INTO " . $this->getTableName() . " (version) VALUES ($number)");
		}
	}

	/**
	 * Get the current version of the database
	 *
	 * @return integer $version
	 */
	public function getCurrentVersion()
	{
		$this->_createMigrationTable();

		$result = $this->_connection->fetchColumn("SELECT version FROM " . $this->getTableName());

		return isset($result[0]) ? $result[0] : 0;
	}

	/**
	 * hReturns true/false for whether or not this database has been migrated in the past
	 *
	 * @return boolean $migrated
	 */
	public function hasMigrated()
	{
		$this->_createMigrationTable();

		$result = $this->_connection->fetchColumn("SELECT version FROM " . $this->getTableName());

		return isset($result[0]) ? true : false;
	}

	/**
	 * Create the migration table and return true. If it already exists it will
	 * silence the exception and return false
	 *
	 * @return boolean $created Whether or not the table was created. Exceptions
	 *                          are silenced when table already exists
	 */
	protected function _createMigrationTable()
	{
		if ($this->_migrationTableCreated) {
			return true;
		}

		$this->_migrationTableCreated = true;

		try {
			$this->_connection->export->createTable($this->getTableName(), array(
			                                                                    'version' => array(
				                                                                    'type' => 'integer',
				                                                                    'size' => 11
			                                                                    )
			                                                               ));

			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}