<?php
/**
 * @version   $Id: Unsupported.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Logger_Unsupported extends RokCommon_Logger_AbstractLogger
{

    /**
     * General trace  messages
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function trace($message, $throwable = null)
    {
    }

    /**
     * Send a debug message to the log
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function debug($message, $throwable = null)
    {
    }

    /**
     * Send a Info Message to the log
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function info($message, $throwable = null)
    {
    }

    /**
     * Send a notice to the log
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function notice($message, $throwable = null)
    {
    }

    /**
     * Send a warning to the log
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function warning($message, $throwable = null)
    {
    }

    /**
     * Send an Error message to the log
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function error($message, $throwable = null)
    {
    }

    /**
     * Send a Fatal message to the log
     *
     * @param string    $message    The message for the log
     * @param Exception $throwable  The Exception for the log
     */
    public function fatal($message, $throwable = null)
    {
    }

}
