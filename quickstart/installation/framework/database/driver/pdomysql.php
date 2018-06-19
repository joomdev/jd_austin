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
 * MySQL database driver through PDO
 */
class ADatabaseDriverPdomysql extends ADatabaseDriverMysqli
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 */
	public $name = 'pdomysql';

	/**
	 * @var    string  The database technology family supported, e.g. mysql, mssql
	 */
	public static $dbtech = 'mysql';

	/** @var PDO The db connection resource */
	protected $connection = null;

	/** @var PDOStatement The database connection cursor from the last query. */
	protected $cursor;

	/** @var string Connection character set */
	protected $charset = 'UTF8';

	/** @var array Driver options for PDO */
	protected $driverOptions = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of database options with keys: host, user, password, database, select.
	 *
	 */
	public function __construct($options)
	{
		// Get some basic values from the options.
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user'] = (isset($options['user'])) ? $options['user'] : 'root';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['select'] = (isset($options['select'])) ? (bool) $options['select'] : true;
		$options['charset'] = (isset($options['charset'])) ? $options['charset'] : 'UTF8';
		$options['driverOptions'] = (isset($options['driverOptions'])) ? (array) $options['driverOptions'] : array();

		// Finalize initialisation.
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 *
	 */
	public function __destruct()
	{
		if (is_object($this->connection))
		{
			$this->disconnect();
		}
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @throws  RuntimeException
	 */
	public function connect()
	{
		if ($this->connected())
		{
			return;
		}
		else
		{
			$this->disconnect();
		}

		// Make sure the server is compatible
		if (!$this->isSupported())
		{
			throw new RuntimeException('PDO MySQL is not supported on this server.');
		}

		if (!isset($this->charset))
		{
			$this->charset = 'UTF8';
		}

		$this->options['port'] = $this->options['port'] ? $this->options['port'] : 3306;

		$format = 'mysql:host=#HOST#;port=#PORT#;dbname=#DBNAME#;charset=#CHARSET#';

		if ($this->options['socket'])
		{
			$format = 'mysql:socket=#SOCKET#;dbname=#DBNAME#;charset=#CHARSET#';
		}

		$replace = array('#HOST#', '#PORT#', '#SOCKET#', '#DBNAME#', '#CHARSET#');
		$with = array($this->options['host'], $this->options['port'], $this->options['socket'], $this->options['database'], $this->options['charset']);

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);

		// connect to the server
		try
		{
			$this->connection = new PDO(
				$connectionString,
				$this->options['user'],
				$this->options['password'],
				$this->options['driverOptions']
			);
		}
		catch (PDOException $e)
		{
			throw new RuntimeException('Could not connect to MySQL via PDO: ' . $e->getMessage(), 2);
		}

		// Reset the SQL mode of the connection
		try
		{
			$this->connection->exec("SET @@SESSION.sql_mode = '';");
		}
			// Ignore any exceptions (incompatible MySQL versions)
		catch (Exception $e)
		{
		}

		// Set the max_allowed_packet variable to a larger value (64Mb), so we can restore columns with huge data in it
		// MySQL is very fishy about this option: in some version we can change only the GLOBAL, in others only the SESSION...
		// We will try both, worst case scenario they simply won't work
		try
		{
			$this->connection->exec('SET GLOBAL max_allowed_packet=67108864;');
		}
		catch (Exception $e)
		{
			// Ignore the error
		}

		try
		{
			$this->connection->exec('SET SESSION max_allowed_packet=67108864;');
		}
		catch (Exception $e)
		{
			// Ignore the error
		}

		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		$this->freeResult();
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 */
	public function disconnect()
	{
		$return = false;

		if (is_object($this->cursor))
		{
			$this->cursor->closeCursor();
		}

		$this->connection = null;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 */
	public function escape($text, $extra = false)
	{
		$this->connect();

		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		$result = substr($this->connection->quote($text), 1, -1);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 */
	public static function isSupported()
	{
		if (!defined('PDO::ATTR_DRIVER_NAME'))
		{
			return false;
		}

		return in_array('mysql', PDO::getAvailableDrivers());
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 */
	public function connected()
	{
		if (!is_object($this->connection))
		{
			return false;
		}

		// Do not try doing a SELECT 1 query here. It seems that the query fails when we are connected to a database
		// which has no tables?!

		return true;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 */
	public function getAffectedRows()
	{
		if ($this->cursor instanceof PDOStatement)
		{
			return $this->cursor->rowCount();
		}

		return 0;
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 */
	public function getNumRows($cursor = null)
	{
		if ($cursor instanceof PDOStatement)
		{
			return $cursor->rowCount();
		}

		if ($this->cursor instanceof PDOStatement)
		{
			return $this->cursor->rowCount();
		}

		return 0;
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 */
	public function getVersion()
	{
		if (!is_object($this->connection))
		{
			$this->connect();
		}

		return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 */
	public function insertid()
	{
		$this->connect();

		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->connection->lastInsertId();
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		static $isReconnecting = false;

		$this->connect();

		if (!is_object($this->connection))
		{
			throw new RuntimeException($this->errorMsg, $this->errorNum);
		}

		$this->freeResult();

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);

		if ($this->limit > 0 || $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// Increment the query counter.
		$this->count++;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $sql;
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		try
		{
			$this->cursor = $this->connection->query($sql);
		}
		catch (Exception $e)
		{
		}

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$errorInfo = $this->connection->errorInfo();
			$this->errorNum = $errorInfo[1];
			$this->errorMsg = $errorInfo[2] . ' SQL=' . $sql;

			unset($sql);

			// Check if the server was disconnected.
			if (!$this->connected() && !$isReconnecting)
			{
				$isReconnecting = true;

				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->connect();
				}

				// If connect fails, ignore that exception and throw the normal exception.
				catch (RuntimeException $e)
				{
					// Throw the normal query exception.
					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result = $this->execute();
				$isReconnecting = false;

				return $result;
			}
			// The server was not disconnected.
			else
			{
				// Throw the normal query exception.
				throw new RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @throws  RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		try
		{
			$this->connection->exec('USE ' . $this->quoteName($database));
		}
		catch (Exception $e)
		{
			$errorInfo = $this->connection->errorInfo();
			$this->errorNum = $errorInfo[1];
			$this->errorMsg = $errorInfo[2];

			throw new RuntimeException('Could not connect to database: ' . $this->errorMsg, $this->errorNum);
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 */
	public function setUTF()
	{
		return true;
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 */
	protected function fetchArray($cursor = null)
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetch(PDO::FETCH_NUM);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetch(PDO::FETCH_NUM);
		}

		return $ret;
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 */
	protected function fetchAssoc($cursor = null)
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetch(PDO::FETCH_ASSOC);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetch(PDO::FETCH_ASSOC);
		}

		return $ret;
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret =  $cursor->fetchObject($class);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetchObject($class);
		}

		return $ret;
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 */
	protected function freeResult($cursor = null)
	{
		if ($cursor instanceof PDOStatement)
		{
			$cursor->closeCursor();
			$cursor = null;
		}

		if ($this->cursor instanceof PDOStatement)
		{
			$this->cursor->closeCursor();
			$this->cursor = null;
		}
	}

	/**
	 * PDO does not support serialize
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		$serializedProperties = array();

		$reflect = new ReflectionClass($this);

		// Get properties of the current class
		$properties = $reflect->getProperties();

		foreach ($properties as $property)
		{
			// Do not serialize properties that are PDO
			if ($property->isStatic() == false && !($this->{$property->name} instanceof PDO))
			{
				array_push($serializedProperties, $property->name);
			}
		}

		return $serializedProperties;
	}

	/**
	 * Wake up after serialization
	 *
	 * @return  array
	 */
	public function __wakeup()
	{
		// Get connection back
		$this->__construct($this->options);
	}

	/**
	 * Determine whether or not the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if the database engine supports UTF-8 character encoding.
	 *
	 */
	public function hasUTFSupport()
	{
		$verParts = explode('.', $this->getVersion());

		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 */
	public function transactionCommit()
	{
		$this->connection->commit();
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @return  void
	 */
	public function transactionRollback()
	{
		$this->connection->rollBack();
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 */
	public function transactionStart()
	{
		$this->connection->beginTransaction();
	}
}
