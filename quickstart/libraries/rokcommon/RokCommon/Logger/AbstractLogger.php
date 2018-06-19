<?php
/**
 * @version   $Id: AbstractLogger.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 *
 */
abstract class RokCommon_Logger_AbstractLogger implements RokCommon_Logger
{

    /**
     * @var int
     */
    protected $registred_levels = 0;

    /**
     * @var array
     */
    protected $log_levels = array(
        'TRACE'     => RokCommon_Logger::TRACE,
        'DEBUG'     => RokCommon_Logger::DEBUG,
        'INFO'      => RokCommon_Logger::INFO,
        'NOTICE'    => RokCommon_Logger::NOTICE,
        'WARNING'   => RokCommon_Logger::WARNING,
        'ERROR'     => RokCommon_Logger::ERROR,
        'FATAL'     => RokCommon_Logger::FATAL,
        'ALL'       => RokCommon_Logger::ALL,
    );

    /**
     * @param array $loglevels
     */
    public function __construct(array $loglevels = array('ALL'))
    {
        foreach ($loglevels as $level) {
            if (!array_key_exists(strtoupper($level), $this->log_levels)) {
                continue;
            }
            $this->registred_levels = $this->registred_levels | $this->log_levels[strtoupper($level)];
        }
    }

    /**
     * See if the Debug level is enabled
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->isLevelEnabled(RokCommon_Logger::DEBUG);
    }

    /**
     * See if the Trace level is enabled
     *
     * @return bool
     */
    public function isTranceEnabled()
    {
        return $this->isLevelEnabled(RokCommon_Logger::TRACE);
    }

    /**
     * See if the Info level is enabled
     *
     * @return bool
     */
    public function isInfoEnabled()
    {
        return $this->isLevelEnabled(RokCommon_Logger::INFO);
    }

    /**
     * See if the passed debug level is enabled
     *
     * @param int $level the debug level
     *
     * @return bool
     */
    public function isLevelEnabled($level)
    {
        return ($level & $this->registred_levels);
    }

}
