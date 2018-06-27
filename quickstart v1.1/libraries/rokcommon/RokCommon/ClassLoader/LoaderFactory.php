<?php
/**
 * @version   $Id: LoaderFactory.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_ClassLoader_LoaderFactory
{
	public static function getLoader()
	{
		$container = RokCommon_Service::getContainer();
		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $container->platforminfo;


		$loader = false;
		$cache_loaders = $container->getParameter('classloader.cacheloaders');
		foreach($cache_loaders as $cache_type => $cache_loader)
		{
			if ($cache_loader->use && extension_loaded($cache_loader->extension))
			{
				$cache_loader->prefix = $platforminfo->getInstanceId().'-'.$cache_loader->prefix;
				$loader = $container->getService($cache_loader->service);
				break;
			}
		}
		if (!$loader)
		{
			$loader = $container->classloader_basicloader;
		}

		return $loader;
	}
}
