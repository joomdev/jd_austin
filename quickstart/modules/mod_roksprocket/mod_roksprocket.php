<?php
/**
 * @version   $Id: mod_roksprocket.php 19251 2014-02-27 21:49:01Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die;
try {
	if (defined('ROKSPROCKET')) {

		$lang = JFactory::getLanguage();
		$lang->load('com_roksprocket', JPATH_BASE, $lang->getDefault(), false, false);
		$lang->load('com_roksprocket', JPATH_BASE, null, false, false);
		$lang->load('com_roksprocket', JPATH_SITE.'/components/com_roksprocket', $lang->getDefault(), false, false);
		$lang->load('com_roksprocket', JPATH_SITE.'/components/com_roksprocket', null, false, false);

		RokCommon_ClassLoader::addPath(dirname(__FILE__) . '/lib');

        $container = RokCommon_Service::getContainer();

        foreach ($container['roksprocket.layouts'] as $type => $layoutinfo) {
            foreach ($layoutinfo->paths as $layoutpath) {
                if (is_dir($layoutpath . '/language')) {
	                $lang->load('roksprocket_layout_'.$type, $layoutpath, $lang->getDefault(), true, false);
                    $lang->load('roksprocket_layout_'.$type, $layoutpath, null, true, false);
                }
            }
        }

		/** @var $logger logger */
		$logger            = $container->logger;
		$module_parameters = RokCommon_Registry_Converter::convert($params);
		$module_parameters->set('module_id', $module->id);
		$roksprocket = new ModRokSprocket($module_parameters);
		$items       = $roksprocket->getData();
		echo $content_items = $roksprocket->render($items);
		/** @var $header RokCommon_Header_Joomla */
		$header = $container->getService('header');
		$header->populate();
	}
} catch (Exception $e) {
	JError::raiseWarning(100, $e->getMessage());
}