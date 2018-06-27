<?php
/**
 * @version   $Id: BaseDefinition.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokCommon_Platform_BaseDefinition implements RokCommon_Platform_Definition
{

    /**
     * @var bool
     */
    protected static $full_version_check = true;

    /**
     * @var bool
     */
    protected static $full_version_condensed_check = false;


    /**
     * @var bool
     */
    protected static $short_version_check = true;

    /**
     * @var bool
     */
    protected static $short_version_condensed_check = true;

    /**
     * @var int
     */
    protected static $short_version_max_parts = 2;

    /**
     * @var int
     */
    protected static $short_version_min_parts = 2;


    /**
     * @var string the platform name;
     */
    protected $_name = RokCommon_Platform_Definition::UNKNOWN;

    /** @var string The version of the running framework */
    protected $_version = RokCommon_Platform_Definition::UNKNOWN_VERSION;


    /**
     * @var string
     * @todo See if short version can be removed
     */
    protected $_shortversion = '';


    /** @var RokCommon_Platform_Javascript */
    protected $_javascriptInfo;

    /**
     * @var array
     */
    protected $_loaderchecks = array();


    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * @return array
     */
    public function getLoaderChecks()
    {
        return $this->_loaderchecks;
    }

    /**
     * @return RokCommon_Platform_Javascript
     */
    public function getJavascriptInfo()
    {
        return $this->_javascriptInfo;
    }

    /**
     *
     */
    protected function populateLoaderChecks()
    {
        if ($this->_version != RokCommon_Platform_Definition::UNKNOWN_VERSION) {
            $this->_loaderchecks = self::getChecksForVersion($this->_name, $this->_version);
        }
        $this->_loaderchecks[] = $this->_name;
    }

    /**
     * @param $platform
     * @param $version
     * @return array;
     */
    protected static function getChecksForVersion($platform, $version)
    {
        $checks = array();
        $version_parts = explode('.', $version);

        if (self::$full_version_check) {
            $checks[] = $platform . implode('_', $version_parts);
        }

        if (self::$full_version_condensed_check) {
            $checks[] = $platform . implode('', $version_parts);
        }
        array_pop($version_parts);
        while (count($version_parts) >= self::$short_version_min_parts) {
            if (count($version_parts) >= self::$short_version_min_parts && count($version_parts) <= self::$short_version_max_parts) {
                if (self::$short_version_check) {
                    $checks[] = $platform . implode('_', $version_parts);
                }
                if (self::$short_version_condensed_check){
                    $checks[] = $platform . implode('',$version_parts);
                }
            }
            array_pop($version_parts);
        }
        $checks = array_unique($checks);
        return $checks;
    }

    /**
     * @return string The platform's name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getShortversion()
    {
        return $this->_shortversion;
    }

}
