<?php
/**
 * @version   $Id: Session.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Session
{
	/**
	 *
	 */
	const SESSION_STARTED = TRUE;
	/**
	 *
	 */
	const SESSION_NOT_STARTED = FALSE;

	// The state of the session
	/**
	 * @var bool
	 */
	private $sessionState = self::SESSION_NOT_STARTED;

	/** @var RokCommon_Session */
	protected static $_instance = array();


	/**
	 * @var string
	 */
	protected $_namespace = 'default';

	/**
	 * @param string $namespace
	 *
	 * @return RokCommon_Session
	 */
	public static function &getInstance($namespace = 'default')
	{
		if (!isset(self::$_instance[$namespace])) {
			self::$_instance[$namespace] = new RokCommon_Session($namespace);
		}
		return self::$_instance[$namespace];
	}


	/**
	 * @param string $namespace
	 *
	 * @return \RokCommon_Session
	 */
	protected function __construct($namespace = 'default')
	{
		$this->startSession();
		$this->_namespace = $namespace;
		$this->makeNameSpace($namespace);
	}

	/**
	 * @param $namespace
	 *
	 * @return bool
	 */
	function makeNameSpace($namespace)
	{
		if (!isset($_SESSION[$namespace])) $_SESSION[$namespace] = array('data' => new stdClass());
		return true;
	}

	/**
	 * @return bool
	 */
	protected function startSession()
	{
		if (!isset($_SESSION) && $this->sessionState == self::SESSION_NOT_STARTED) {
			$this->sessionState = session_start();
		}
		return $this->sessionState;
	}

	/**
	 * @param $context
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function _set($context, $value)
	{
		// Explode the registry path into an array
		$nodes = explode('.', $context);

		// Get the namespace
		$count = count($nodes);
		if ($count < 2) {
			$namespace = $this->_namespace;
		} else {
			$namespace = array_shift($nodes);
			$count--;
		}

		if (!isset($_SESSION[$namespace])) {
			$this->makeNameSpace($namespace);
		}

		$ns =& $_SESSION[$namespace]['data'];

		$pathNodes = $count - 1;

		if ($pathNodes < 0) {
			$pathNodes = 0;
		}

		for ($i = 0; $i < $pathNodes; $i++) {
			// If any node along the registry path does not exist, create it
			if (!isset($ns->{$nodes[$i]})) {
				$ns->{$nodes[$i]} = new stdClass();
			}
			$ns =& $ns->{$nodes[$i]};
		}

		// Get the old value if exists so we can return it
		$ns->{$nodes[$i]} =& $value;

		return $ns->{$nodes[$i]};
	}

	/**
	 * @param $context
	 */
	protected function _unset($context)
	{
		// Explode the registry path into an array
		$nodes = explode('.', $context);

		// Get the namespace
		$count = count($nodes);
		if ($count < 2) {
			$namespace = $this->_namespace;
		} else {
			$namespace = array_shift($nodes);
			$count--;
		}

		if (!isset($_SESSION[$namespace])) {
			$this->makeNameSpace($namespace);
		}

		$ns =& $_SESSION[$namespace]['data'];

		$pathNodes = $count - 1;

		if ($pathNodes < 0) {
			$pathNodes = 0;
		}

		for ($i = 0; $i < $pathNodes; $i++) {
			// If any node along the registry path does not exist, create it
			if (!isset($ns->{$nodes[$i]})) {
				$ns->{$nodes[$i]} = new stdClass();
			}
			$ns =& $ns->{$nodes[$i]};
		}

		// Get the old value if exists so we can return it
		unset($ns->{$nodes[$i]});
	}

	/**
	 * @param $context
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function set($context, $value)
	{
		$context_parts = explode('.', $context);
		return self::getInstance($context_parts[0])->_set($context, $value);
	}


	/**
	 * @param $context
	 */
	public static function clear($context)
	{
		$context_parts = explode('.', $context);
		return self::getInstance($context_parts[0])->_unset($context);
	}


	/**
	 * @param $context
	 * @param $default
	 *
	 * @return mixed
	 */
	protected function _get($context, $default = null)
	{
		$result = $default;

		// Explode the registry path into an array
		if ($nodes = explode('.', $context)) {
			// Get the namespace
			//$namespace = array_shift($nodes);
			$count = count($nodes);
			if ($count < 2) {
				$namespace = $this->_namespace;
				$nodes[1]  = $nodes[0];
			} else {
				$namespace = $nodes[0];
			}

			if (isset($_SESSION[$namespace])) {
				$ns        =& $_SESSION[$namespace]['data'];
				$pathNodes = $count - 1;

				//for ($i = 0; $i < $pathNodes; $i ++) {
				for ($i = 1; $i < $pathNodes; $i++) {
					if ((isset($ns->{$nodes[$i]}))) $ns =& $ns->{$nodes[$i]};
				}

				if (isset($ns->{$nodes[$i]})) {
					$result = $ns->{$nodes[$i]};
				}
			}
		}
		return $result;
	}

	/**
	 * @param $context
	 * @param $default
	 *
	 * @return mixed
	 */
	public static function get($context, $default = null)
	{
		$context_parts = explode('.', $context);
		return self::getInstance($context_parts[0])->_get($context, $default);
	}
}
