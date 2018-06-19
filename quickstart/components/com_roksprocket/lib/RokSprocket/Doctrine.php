<?php
/**
 * @version   $Id: Doctrine.php 30359 2016-07-01 09:07:33Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Doctrine extends RokCommon_Doctrine
{
    /** @var RokCommon_Doctrine */
    protected static $_instance;

    /**
     * @static
     * @return RokCommon_Doctrine
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function __construct()
    {
        parent::__construct();
        //        $migration = new RokGallery_Doctrine_Migration();
        //        if ($migration->getCurrentVersion() != $migration->getLatestVersion())
        //        {
        //            $migration->migrate();
        //        }
        //        $listener = new RokGallery_Listener();
        //        $this->connection->addListener($listener);
    }

    /**
     * @static
     *
     * @param  $path
     *
     * @return void
     */
    public static function addModelPath($path)
    {
        $self = self::getInstance();
        RokCommon_ClassLoader::addPath($path);
        Doctrine_Core::loadModels($path);
    }

    /**
     * @static
     * @return Doctrine_Connection
     */
    public static function getConnection()
    {
        $self = self::getInstance();
        return $self->connection;
    }

    /**
     * @return Doctrine_Manager
     */
    public static function &getManager()
    {
        $self = self::getInstance();
        return $self->manager;
    }

    /**
     * @return RokCommon_Doctrine_Platform
     */
    public static function &getPlatformInstance()
    {
        $self = self::getInstance();
        return $self->platform_instance;
    }
}