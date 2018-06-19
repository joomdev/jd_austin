<?php
/**
 * @version   $Id: Request.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

class RokCommon_Request
{
    protected static $_instance;

    /**
     * @return RokCommon_Request
     */
    public static function &getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new RokCommon_Request();
        }
        return self::$_instance;
    }

    /**
     * @param $context
     * @param $default
     * @return mixed
     */
    public static function get($context, $default = null)
    {
        $request = self::getInstance();
        return $request->_get($context, $default);
    }

    /**
     * @param $context
     * @param $value
     * @return mixed
     */
    public static function set($context, $value)
    {
        $request = self::getInstance();
        return $request->_set($context, $value);
    }

    /**
     * @param $context
     * @return mixed
     */
    public static function exists($context)
    {

        $request = self::getInstance();
        return $request->_exists($context);
    }


    /**
     * @var \RokCommon_Registry
     */
    protected $_storage;

    /**
     *
     */
    protected function __construct()
    {
        $this->_storage = new RokCommon_Registry();
    }

    /**
     * @param $context
     * @param $default
     * @return mixed
     */
    protected function _get($context, $default)
    {
        return $this->_storage->get($context, $default);
    }

    /**
     * @param $context
     * @param $value
     * @return mixed
     */
    protected function _set($context, $value)
    {
        return $this->_storage->set($context, $value);
    }

    /**
     * @param $context
     * @return bool
     */
    protected function _exists($context)
    {
        return $this->_storage->exists($context);
    }
}
