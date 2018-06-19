<?php
/**
 * @package angifw
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

if (!defined('DATA_CHUNK_LENGTH'))
{
	define('DATA_CHUNK_LENGTH',	65536);			// How many bytes to read per step
	define('MAX_QUERY_LINES',	300);			// How many lines may be considered to be one query (except text lines)
}

abstract class ADatabaseRestore
{
	/**
	 * A list of error codes (numbers) which should not block cause the
	 * restoration to halt. Used for soft errors and warnings which do not cause
	 * problems with the restored site.
	 *
	 * @var  array
	 */
	protected $allowedErrorCodes = array();

	/**
	 * A list of comment line delimiters. Lines starting with these strings are
	 * skipped over during restoration.
	 *
	 * @var  array
	 */
	protected $comment = array();

	/**
	 * A list of the part files of the database dump we are importing
	 *
	 * @var  array
	 */
	protected $partsMap = array();

	/**
	 * The total size of all database dump files
	 *
	 * @var  int
	 */
	protected $totalSize = 0;

	/**
	 * The part file currently being processed
	 *
	 * @var  string
	 */
	protected $curpart = null;

	/**
	 * The offset into the part file being processed
	 *
	 * @var  int
	 */
	protected $foffset = 0;

	/**
	 * The total size of all database dump files processed so far
	 *
	 * @var  int
	 */
	protected $runSize = 0;

	/**
	 * The file pointer to the SQL file currently being restored
	 *
	 * @var  resource
	 */
	protected $file = null;

	/**
	 * The filename of the SQL file currently being restored
	 *
	 * @var  string
	 */
	protected $filename = null;

	/**
	 * The starting line number of processing the current file
	 *
	 * @var  integer
	 */
	protected $start = null;

	/**
	 * The ATimer object used to guard against timeouts
	 *
	 * @var  ATimer
	 */
	protected $timer = null;

	/**
	 * The database file key used to determine which dump we're restoring
	 *
	 * @var  string
	 */
	protected $dbkey = null;

	/**
	 * Cached copy of the up-to-date databases.ini values of the database dump
	 * we are currently restoring.
	 *
	 * @var  array
	 */
	protected $dbiniValues = null;

	/**
	 * The database driver used to connect to this database
	 *
	 * @var  ADatabaseDriver
	 */
	protected $db = null;

	/**
	 * Total queries run so far
	 *
	 * @var  integer
	 */
	protected $totalqueries = null;

	/**
	 * Line number in the current file being processed
	 *
	 * @var  integer
	 */
	protected $linenumber = null;

	/**
	 * Number of queries run in this restoration step
	 *
	 * @var  integer
	 */
	protected $queries = null;

    /** @var  AContainer    Application container */
    protected $container;

	/**
	 * Public constructor. Initialises the database restoration engine.
	 *
	 * @param   string      $dbkey          The databases.ini key of the current database
	 * @param   string      $dbiniValues    The databases.ini configuration variables for the current database
     * @param   AContainer  $container      Application container
	 */
	public function __construct($dbkey, $dbiniValues, AContainer $container = null)
	{
        if(is_null($container))
        {
            $container = AApplication::getInstance()->getContainer();
        }

        $this->container = $container;

		$this->dbkey = $dbkey;
		$this->dbiniValues = $dbiniValues;

		$this->populatePartsMap();

		if (!key_exists('maxexectime', $this->dbiniValues))
		{
			$this->dbiniValues['maxexectime'] = 5;
		}
		if (!key_exists('runtimebias', $this->dbiniValues))
		{
			$this->dbiniValues['runtimebias'] = 75;
		}

		$this->timer = new ATimer(0, (int)$this->dbiniValues['maxexectime'], (int)$this->dbiniValues['runtimebias']);
	}

	/**
	 * Public destructor. Closes open handlers.
	 */
	public function __destruct()
	{
		if (is_object($this->db) && ($this->db instanceof ADatabaseDriver))
		{
			try
			{
				$this->db->disconnect();
			}
			catch (Exception $exc)
			{
				// Nothing. We just never want to fail when closing the
				// database connection.
			}
		}

		if (is_resource($this->file))
		{
			@fclose($this->file);
			$this->file = null;
		}
	}

	/**
	 * Gets an instance of the database restoration class based on the $dbkey.
	 * If it doesn't exist, a new instance is created based on $dbkey and
	 * $dbiniValues provided.
	 *
	 * @staticvar  array  $instances  The array of ADatabaseRestore instances
	 *
	 * @param   string      $dbkey        The key of the database being restored
	 * @param   array       $dbiniValues  The database restoration configuration variables
     * @param   AContainer  $container      Application container
	 *
	 * @return  ADatabaseRestore
	 *
	 * @throws Exception
	 */
	public static function getInstance($dbkey, $dbiniValues = null, AContainer $container = null)
	{
		static $instances = array();

		if (!array_key_exists($dbkey, $instances))
		{
			if (empty($dbiniValues))
			{
				throw new Exception(AText::sprintf('ANGI_RESTORE_ERROR_UNKNOWNKEY', $dbkey));
			}

			if (is_object($dbiniValues))
			{
				$dbiniValues = (array)$dbiniValues;
			}

            if(is_null($container))
            {
                $container = AApplication::getInstance()->getContainer();
            }

			$class = 'ADatabaseRestore' . ucfirst($dbiniValues['dbtype']);
			$instances[$dbkey] = new $class($dbkey, $dbiniValues, $container);
		}

		return $instances[$dbkey];
	}

	/**
	 * Remove all cached information from the session storage
	 */
	public function removeInformationFromStorage()
	{
		$variables = array('start', 'foffset', 'totalqueries', 'curpart',
			'partsmap', 'totalsize', 'runsize');
		$session = $this->container->session;

		foreach($variables as $var)
		{
			$session->remove('restore.' . $this->dbkey . '.' . $var);
		}
	}

	/**
	 * Return a value from the session storage
	 *
	 * @param   string  $var      The name of the variable
	 * @param   mixed   $default  The default value (null if ommitted)
	 *
	 * @return  mixed  The variable's value
	 */
	protected function getFromStorage($var, $default = null)
	{
		$session = $this->container->session;

		return $session->get('restore.' . $this->dbkey . '.' . $var, $default);
	}

	/**
	 * Sets a value to the session storage
	 *
	 * @param   string  $var    The name of the variable
	 * @param   mixed   $value  The value to store
	 */
	protected function setToStorage($var, $value)
	{
		$session = $this->container->session;

		return $session->set('restore.' . $this->dbkey . '.' . $var, $value);
	}

	/**
	 * Gets a database configuration variable, as cached in the $dbiniValues
	 * array
	 *
	 * @param   string  $key      The name of the variable to get
	 * @param   mixed   $default  Default value (null if skipped)
	 *
	 * @return  mixed  The configuration variable's value
	 */
	protected function getParam($key, $default = null)
	{
		if (is_array($this->dbiniValues))
		{
			if (array_key_exists($key, $this->dbiniValues))
			{
				return $this->dbiniValues[$key];
			}
			else
			{
				return $default;
			}
		}
		else
		{
			return $default;
		}
	}

	protected function populatePartsMap()
	{
		// Nothing to do if it's already populated, right?
		if (!empty($this->partsMap))
		{
			return;
		}

		// First, try to fetch from the session storage
		$this->totalSize = $this->getFromStorage('totalsize', 0);
		$this->runSize = $this->getFromStorage('runsize', 0);
		$this->partsMap = $this->getFromStorage('partsmap', array());
		$this->curpart = $this->getFromStorage('curpart', 0);
		$this->foffset = $this->getFromStorage('foffset', 0);
		$this->start = $this->getFromStorage('start', 0);
		$this->totalqueries = $this->getFromStorage('totalqueries', 0);

		// If that didn't work try a full initalisation
		if (empty($this->partsMap))
		{
			$sqlfile = $this->dbiniValues['sqlfile'];

			$parts = $this->getParam('parts', 1);

			$this->partsMap = array();
			$path = APATH_INSTALLATION . '/sql';
			$this->totalSize = 0;
			$this->runSize = 0;
			$this->curpart = 0;
			$this->foffset = 0;

			for ($index = 0; $index <= $parts; $index++)
			{
				if ($index == 0)
				{
					$basename = $sqlfile;
				}
				else
				{
					$basename = substr($sqlfile, 0, -4).'.s'.sprintf('%02u', $index);
				}

				$file = $path.'/'.$basename;
				if (!file_exists($file))
				{
					$file = 'sql/'.$basename;
				}
				$filesize = @filesize($file) ;
				$this->totalSize += intval($filesize);
				$this->partsMap[] = $file;
			}

			$this->setToStorage('totalsize', $this->totalSize);
			$this->setToStorage('runsize', $this->runSize);
			$this->setToStorage('partsmap', $this->partsMap);
			$this->setToStorage('curpart', $this->curpart);
			$this->setToStorage('foffset', $this->foffset);
			$this->setToStorage('start', $this->start);
			$this->setToStorage('totalqueries', $this->totalqueries);

			$this->container->session->saveData();
		}
	}

	/**
	 * Proceeds to opening the next SQL part file
	 * @return bool True on success
	 */
   protected function getNextFile()
   {
	   $parts = $this->getParam('parts', 1);

	   if ($this->curpart >= ($parts - 1))
	   {
		   return false;
	   }

	   $this->curpart++;
	   $this->foffset = 0;

	   $this->setToStorage('curpart', $this->curpart);
	   $this->setToStorage('foffset', $this->foffset);

	   $this->container->session->saveData();

	   // Close an already open file (if one was indeed already open)
	   if (!empty($this->file) && is_resource($this->file))
	   {
		   @fclose($this->file);
		   $this->file = null;
	   }

	   return $this->openFile();
   }

    /**
     * Opens the SQL part file whose ID is specified in the $curpart variable
     * and updates the $file, $start and $foffset variables.
     *
     * @return bool True on success
     *
     * @throws \Exception
     */
	protected function openFile()
	{
		// If there is an already open file, close it before proceeding
		if (!empty($this->file) && is_resource($this->file))
		{
			@fclose($this->file);
			$this->file = null;
		}

		if (!is_numeric($this->curpart))
		{
			$this->curpart = 0;
		}
		$this->filename = $this->partsMap[$this->curpart];

		if (!$this->file = @fopen($this->filename, "rt"))
		{
			throw new Exception(AText::sprintf('ANGI_RESTORE_ERROR_CANTOPENDUMPFILE', $this->filename));
		}
		else
		{
			// Get the file size
			if (fseek($this->file, 0, SEEK_END) == 0)
			{
				$this->filesize = ftell($this->file);
			}
			else
			{
				throw new Exception(AText::_('ANGI_RESTORE_ERROR_UNKNOWNFILESIZE'));
			}
		}

		// Check start and foffset are numeric values
		if (!is_numeric($this->start) || !is_numeric($this->foffset))
		{
			throw new Exception(AText::_('ANGI_RESTORE_ERROR_INVALIDPARAMETERS'));
		}

		$this->start = floor($this->start);
		$this->foffset = floor($this->foffset);

		// Check $foffset upon $filesize
		if ($this->foffset > $this->filesize)
		{
			throw new Exception(AText::_('ANGI_RESTORE_ERROR_AFTEREOF'));
		}

		// Set file pointer to $foffset
		if (fseek($this->file, $this->foffset) != 0)
		{
			throw new Exception(AText::_('ANGI_RESTORE_ERROR_CANTSETOFFSET'));
		}

		return true;
	}

	/**
	 * Returns the instance of the database driver, creating it if it doesn't
	 * exist.
	 *
	 * @return  ADatabaseDriver
	 *
	 * @throws RuntimeException
	 */
	protected function getDatabase()
	{
		if (!is_object($this->db))
		{
			$options = array(
				'driver'	=> $this->dbiniValues['dbtype'],
				'database'	=> $this->dbiniValues['dbname'],
				'select'	=> 0,
				'host'		=> $this->dbiniValues['dbhost'],
				'user'		=> $this->dbiniValues['dbuser'],
				'password'	=> $this->dbiniValues['dbpass'],
				'prefix'	=> $this->dbiniValues['prefix'],
			);

			$class = 'ADatabaseDriver' . ucfirst(strtolower($options['driver']));

			try
			{
				$this->db = new $class($options);
				$this->db->setUTF();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()));
			}
		}

		return $this->db;
	}

	/**
	 * Executes a SQL statement, ignoring errors in the $allowedErrorCodes list.
	 *
	 * @param   string  $sql  The SQL statement to execute
	 *
	 * @return  mixed  A database cursor on success, false on failure
	 */
	protected function execute($sql)
	{
		$db = $this->getDatabase();

		try
		{
			$db->setQuery($sql);
			$result = $db->execute();
		}
		catch (Exception $exc)
		{
			$result = false;
			if (!in_array($exc->getCode(), $this->allowedErrorCodes))
			{
				// Format the error message and throw it again
				$message = '<h2>' . AText::sprintf('ANGI_RESTORE_ERROR_ERRORATLINE', $this->linenumber) . '</h2>' . "\n";
				$message .= '<p>' . AText::_('ANGI_RESTORE_ERROR_MYSQLERROR') . '</p>' . "\n";
				$message .= '<tt>ErrNo #' . htmlspecialchars($exc->getCode()) . '</tt>' . "\n";
				$message .= '<pre>' . htmlspecialchars($exc->getMessage()) . '</pre>' . "\n";
				$message .= '<p>' . AText::_('ANGI_RESTORE_ERROR_RAWQUERY') . '</p>' . "\n";
				$message .= '<pre>' . htmlspecialchars($sql) . '</pre>' . "\n";

				// Rethrow the exception if we're not supposed to handle it
				throw new Exception($message);
			}
		}

		return $result;
	}

	/**
	 * Read the next line from the database dump
	 *
	 * @return  string  The query string
	 *
	 * @throws Exception
	 */
	protected function readNextLine()
	{
		$parts = $this->getParam('parts', 1);

		$query = "";
		while (!feof($this->file) && (strpos($query, "\n") === false))
		{
			$query .= fgets($this->file, DATA_CHUNK_LENGTH);
		}

		// An empty query is EOF. Are we done or should I skip to the next file?
		if (empty($query) || ($query === false))
		{
			if ($this->curpart >= ($parts - 1))
			{
				throw new Exception('All done', 200);
			}

			// Register the bytes read
			$current_foffset = @ftell($this->file);

			if (is_null($this->foffset))
			{
				$this->foffset = 0;
			}

			$this->runSize = (is_null($this->runSize) ? 0 : $this->runSize) + ($current_foffset - $this->foffset);

			// Get the next file
			$this->getNextFile();

			// Rerun the fetcher
			throw new Exception('Continue', 201);
		}

		if (substr($query, -1) != "\n")
		{
			// We read more data than we should. Roll back the file...
			$rollback = strlen($query) - strpos($query, "\n");
			fseek($this->file, -$rollback, SEEK_CUR);
			// ...and chop the line
			$query = substr($query, 0, $rollback);
		}

		// Handle DOS linebreaks
		$query = str_replace("\r\n", "\n", $query);
		$query = str_replace("\r", "\n", $query);

		// Skip comments and blank lines only if NOT in parents
		$skipline = false;
		reset($this->comment);

		foreach ($this->comment as $comment_value)
		{
			if (trim($query) == "" || strpos($query, $comment_value) === 0)
			{
				$skipline = true;
				break;
			}
		}

		if ($skipline)
		{
			$this->linenumber++;
			throw new Exception('Continue', 201);
		}

		$query = trim($query, " \n");
		$query = rtrim($query, ';');

		return $query;
	}

	/**
	 * Runs a restoration step and returns an array to be used in the response.
	 *
	 * @return  array
	 *
	 * @throws Exception
	 */
	public function stepRestoration()
	{
		$parts = $this->getParam('parts', 1);
		$this->openFile();
		$this->linenumber = $this->start;
		$this->totalsizeread = 0;
		$this->queries = 0;

		while ($this->timer->getTimeLeft() > 0)
		{
			// Get the next query line
			try
			{
				$query = $this->readNextLine();
			}
			catch (Exception $exc)
			{
				if ($exc->getCode() == 200)
				{
					break;
				}
				elseif ($exc->getCode() == 201)
				{
					continue;
				}
			}

			// Process the query line, running drop/rename queries as necessary
			$this->processQueryLine($query);

			// Update variables
			$this->totalsizeread += strlen($query);
			$this->totalqueries++;
			$this->queries++;
			$query = "";
			$this->linenumber++;
		}

		// Get the current file position
		$current_foffset = ftell($this->file);

		if ($current_foffset === false)
		{
			if (is_resource($this->file))
			{
				@fclose($this->file);
				$this->file = null;
			}

			throw new Exception(AText::_('ANGI_RESTORE_ERROR_CANTREADPOINTER'));
		}

		if (is_null($this->foffset))
		{
			$this->foffset = 0;
		}

		$bytes_in_step = $current_foffset - $this->foffset;
		$this->runSize = (is_null($this->runSize) ? 0 : $this->runSize) + $bytes_in_step;
		$this->foffset = $current_foffset;

		// Return statistics
		$bytes_togo = $this->totalSize - $this->runSize;

		// Check for global EOF
		if (($this->curpart >= ($parts-1)) && feof($this->file))
		{
			$bytes_togo = 0;
		}

		// Save variables in storage
		$this->setToStorage('start', $this->start);
		$this->setToStorage('foffset', $this->foffset);
		$this->setToStorage('totalqueries', $this->totalqueries);
		$this->setToStorage('runsize', $this->runSize);

		if ($bytes_togo == 0)
		{
			// Clear stored variables if we're finished
			$lines_togo = '0';
			$lines_tota = $this->linenumber - 1;
			$queries_togo = '0';
			$queries_tota = $this->totalqueries;
			$this->removeInformationFromStorage();
		}

		$this->container->session->saveData();

		// Calculate estimated time
		$bytesPerSecond = $bytes_in_step / $this->timer->getRunningTime();

		if ($bytesPerSecond <= 0.01)
		{
			$remainingSeconds = 120;
		}
		else
		{
			$remainingSeconds = round($bytes_togo / $bytesPerSecond, 0);
		}

		// Close the file if it is still open at this point
		if (!empty($this->file) && is_resource($this->file))
		{
			@fclose($this->file);
			$this->file = null;
		}

		// Return meaningful data
		return array(
			'percent'			=> round(100 * ($this->runSize / $this->totalSize), 1),
			'restored'			=> $this->sizeformat($this->runSize),
			'total'				=> $this->sizeformat($this->totalSize),
			'queries_restored'	=> $this->totalqueries,
			'current_line'		=> $this->linenumber,
			'current_part'		=> $this->curpart,
			'total_parts'		=> $parts,
			'eta'				=> $this->etaformat($remainingSeconds),
			'error'				=> '',
			'done'				=> ($bytes_togo == 0) ? '1' : '0'
		);
	}

	/**
	 * Processes the query line in the best way each restoration engine sees
	 * fit. This method is supposed to take care of backing up and dropping
	 * tables, changing table collation if requested and converting INSERT to
	 * REPLACE if requested. It is also supposed to execute $query against the
	 * database, replacing the metaprefix #__ with the real prefix.
	 */
	abstract protected function processQueryLine($query);

	private function etaformat($Raw, $measureby='', $autotext=true)
	{
		$Clean = abs($Raw);

		$calcNum = array(
			array('s', 60),
			array('m', 60*60),
			array('h', 60*60*60),
			array('d', 60*60*60*24),
			array('y', 60*60*60*24*365)
		);

		$calc = array(
			's' => array(1, 'second'),
			'm' => array(60, 'minute'),
			'h' => array(60*60, 'hour'),
			'd' => array(60*60*24, 'day'),
			'y' => array(60*60*24*365, 'year')
		);

		if($measureby == ''){
			$usemeasure = 's';

			for($i=0; $i<count($calcNum); $i++){
				if($Clean <= $calcNum[$i][1]){
					$usemeasure = $calcNum[$i][0];
					$i = count($calcNum);
				}
			}
		} else {
			$usemeasure = $measureby;
		}

		$datedifference = floor($Clean/$calc[$usemeasure][0]);

		if($datedifference == 1){
			return $datedifference . ' ' . $calc[$usemeasure][1];
		} else {
			return $datedifference . ' ' . $calc[$usemeasure][1] . 's';
		}
	}

	/**
	 * Returns the cached total size of the SQL dump.
	 *
	 * @param   boolean  $use_units  Should I automatically figure out and use
	 *
	 * @return  string
	 */
	public function getTotalSize($use_units = false)
	{
		$size = $this->totalSize;

		if($use_units)
		{
			$size = $this->sizeformat($size);
		}

		return $size;
	}

	private function sizeformat($size)
	{
		if ($size < 0)
		{
			return 0;
		}
		$unit=array('b','KB','MB','GB','TB','PB');
		$i = floor(log($size,1024));
		if (($i < 0) || ($i > 5))
		{
			$i = 0;
		}
		return @round($size/pow(1024,($i)),2).' '.$unit[$i];
	}

	public function getTimer()
	{
		return $this->timer;
	}
}
