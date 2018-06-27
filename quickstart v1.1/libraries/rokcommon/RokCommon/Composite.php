<?php
/**
 * @version   $Id: Composite.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Composite
{
    /**
     *
     */
    const DEFAULT_PRIORITY = 10;
    /**
     *
     */
    const DEFAULT_TEMPLATE_PRIORITY = 20;

    /**
     * @var RokCommon_Composite_Platform
     */
    protected static $_platform;

    /**
     * @var array[]
     */
    protected static $_packages_paths = array();

    /**
     * @var bool
     */
    protected static $_packages_paths_dirty = true;

    /**
     * @var RokCommon_Composite_Package[]
     */
    protected static $_packages = array();

    /**
     * @param     $path
     * @param int $priority
     */
    public static function addPath($path, $priority = self::DEFAULT_PRIORITY)
    {
        if (is_dir($path)) {
            self::$_packages_paths[$priority][$path] = $path;
            self::$_packages_paths_dirty             = true;
        }
    }

    /**
     * @param     $package
     * @param     $path
     * @param int $priority
     */
    public static function addPackagePath($package, $path, $priority = self::DEFAULT_PRIORITY)
    {
        $package_name = strtolower($package);
        self::populatePackage($package_name);
        if (!array_key_exists($package_name, self::$_packages)) self::$_packages[$package_name] = new RokCommon_Composite_Package($package_name);
        self::$_packages[$package_name]->addPath($path, $priority);
    }

    /**
     * @param $package_name
     */
    protected static function populatePackage($package_name)
    {
        if (self::$_packages_paths_dirty) {
            foreach (self::$_packages_paths as $priority => $paths) {
                foreach ($paths as $path) {
                    $package_path = $path . DS . $package_name;
                    if (file_exists($package_path) && is_dir($package_path)) {

                        // create a context if it wasnt there
                        if (!array_key_exists($package_name, self::$_packages)) {
                            self::$_packages[$package_name] = new RokCommon_Composite_Package($package_name);
                        }
                        // add the path to the context
                        self::$_packages[$package_name]->addPath($package_path, $priority);
                    }
                }
            }
        }
    }

    /**
     * @param $context_path
     *
     * @return bool|RokCommon_Composite_Context
     */
    public static function &get($context_path)
    {
        $ret = false;
        if (empty($context_path)) return false;

        $context_path = strtolower($context_path);
        $split        = explode('.', $context_path);
        $package_name = array_shift($split);
        $sub_path     = implode('.', $split);


        self::populatePackage($package_name);

        if (array_key_exists($package_name, self::$_packages)) {
            $ret = self::$_packages[$package_name]->getContext($sub_path);
        }
        return $ret;
    }
}
