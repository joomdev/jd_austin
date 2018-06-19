<?php
/**
 * @version   $Id: include.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if (!defined('ROKCOMMON')) {
	function rokcommon_cleanPath($path)
	{
		if (!preg_match('#^/$#', $path)) {
			$path = preg_replace('#[/\\\\]+#', '/', $path);
			$path = preg_replace('#/$#', '', $path);
		}
		return $path;
	}

	if (!defined('ROKCOMMON_LIB_PATH')) define('ROKCOMMON_LIB_PATH', rokcommon_cleanPath(dirname(__FILE__)));

	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

	// Check to see if there is a requiments file and run it.
	// Catch any exceptions and log them as errors.
	$requirements_file = rokcommon_cleanPath(ROKCOMMON_LIB_PATH . '/requirements.php');
	if (file_exists($requirements_file)) {
		try {
			require_once($requirements_file);
		} catch (Exception $e) {
			return;
		}
	}
	define('ROKCOMMON', '3.2.5');
	define('ROKCOMMON_CORE_DEBUG', true);

	// Bootstrap the base classloader and overrides
	require_once(rokcommon_cleanPath(ROKCOMMON_LIB_PATH . '/RokCommon/ClassLoader.php'));
	RokCommon_ClassLoader::getInstance();
	$container = RokCommon_Service::getContainer();
	$container->classloader;

	// load up the supporting functions
	$functions_path = rokcommon_cleanPath(ROKCOMMON_LIB_PATH . '/functions.php');
	if (file_exists($functions_path)) {
		require_once($functions_path);
	}

    RokCommon_Composite::addPackagePath('rc_context_path', ROKCOMMON_LIB_PATH);
}
return "ROKCOMMON_LIB_INCLUDED";