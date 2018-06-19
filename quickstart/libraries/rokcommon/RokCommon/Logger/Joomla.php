<?php
/**
 * @version   $Id: Joomla.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Logger_Joomla extends RokCommon_Logger_AbstractLogger
{
	protected $category;

	/**
	 * @var array
	 */
	protected $mapped_levels = array(
		RokCommon_Logger::TRACE   => JLog::DEBUG,
		RokCommon_Logger::DEBUG   => JLog::DEBUG,
		RokCommon_Logger::INFO    => JLog::INFO,
		RokCommon_Logger::NOTICE  => JLog::NOTICE,
		RokCommon_Logger::WARNING => JLog::WARNING,
		RokCommon_Logger::ERROR   => JLog::ERROR,
		RokCommon_Logger::FATAL   => JLog::ALERT,
		RokCommon_Logger::ALL     => JLog::ALL,
	);


	/**
	 * @param array  $options
	 * @param array  $loglevels
	 * @param string $category
	 */
	public function __construct(array $loglevels = array('ALL'), array $options, $category)
	{
		parent::__construct($loglevels);

		$levels_needed = 0;
		foreach ($loglevels as $level) {
			if (!array_key_exists(strtoupper($level), $this->log_levels)) {
				continue;
			}
			$levels_needed = $levels_needed | $this->mapped_levels[$this->log_levels[strtoupper($level)]];
		}
		// setup the logger for Joomla 1.7
		$this->category = $category;
		JLog::addLogger($options, $levels_needed, array($category));
	}


	/**
	 * General trace  messages
	 *
	 * @param string    $message    The message for the log
	 * @param Exception $throwable  The Exception for the log
	 */
	public function trace($message, $throwable = null)
	{
		JLog::add($message, JLog::DEBUG, $this->category);
	}

	/**
	 * Send a debug message to the log
	 *
	 * @param string    $message    The message for the log
	 * @param Exception $throwable  The Exception for the log
	 */
	public function debug($message, $throwable = null)
	{
		JLog::add($message, JLog::DEBUG, $this->category);
	}

	/**
	 * Send a Info Message to the log
	 *
	 * @param string    $message    The message for the log
	 * @param Exception $throwable  The Exception for the log
	 */
	public function info($message, $throwable = null)
	{
		JLog::add($message, JLog::INFO, $this->category);
	}

	/**
	 * Send a notice to the log
	 *
	 * @param string    $message    The message for the log
	 * @param Exception $throwable  The Exception for the log
	 */
	public function notice($message, $throwable = null)
	{
		JLog::add($message, JLog::NOTICE, $this->category);
	}

	/**
	 * Send a warning to the log
	 *
	 * @param string    $message    The message for the log
	 * @param Exception $throwable  The Exception for the log
	 */
	public function warning($message, $throwable = null)
	{
		JLog::add($message, JLog::WARNING, $this->category);
	}

	/**
	 * Send an Error message to the log
	 *
	 * @param string    $message    The message for the log
	 * @param Exception $throwable  The Exception for the log
	 */
	public function error($message, $throwable = null)
	{
		JLog::add($message, JLog::ERROR, $this->category);
	}

	/**
	 * Send a Fatal message to the log
	 *
	 * @param string    $message    The message for the log
	 * @param Exception $throwable  The Exception for the log
	 */
	public function fatal($message, $throwable = null)
	{
		JLog::add($message, JLog::ALERT, $this->category);
	}

}
