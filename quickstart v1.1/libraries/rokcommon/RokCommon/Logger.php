<?php
/**
 * @version   $Id: Logger.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokCommon_Logger
{
    const TRACE   = 1;
    const DEBUG   = 2;
    const INFO    = 4;
    const NOTICE  = 8;
    const WARNING = 16;
    const ERROR   = 32;
    const FATAL   = 64;
    const ALL     = 127;

    /**
     * General trace  messages
     * @abstract
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function trace($message, $throwable = null);

    /**
     * Send a debug message to the log
     * @abstract
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function debug($message, $throwable = null);

    /**
     * Send a Info Message to the log
     * @abstract
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function info($message, $throwable = null);

    /**
     * Send a notice to the log
     * @abstract
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function notice($message, $throwable = null);

    /**
     * Send a warning to the log
     * @abstract
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function warning($message, $throwable = null);

    /**
     * Send an Error message to the log
     * @abstract
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function error($message, $throwable = null);

    /**
     * Send a Fatal message to the log
     * @abstract
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function fatal($message, $throwable = null);


    /**
     * See if the Debug level is enabled
     * @abstract
     *
     * @return bool
     */
    public function isDebugEnabled();

    /**
     * See if the Trace level is enabled
     * @abstract
     *
     * @return bool
     */
    public function isTranceEnabled();

    /**
     * See if the Info level is enabled
     * @abstract
     *
     * @return bool
     */
    public function isInfoEnabled();

    /**
     * See if the passed debug level is enabled
     * @abstract
     *
     * @param int $level the debug level
     */
    public function isLevelEnabled($level);

}
