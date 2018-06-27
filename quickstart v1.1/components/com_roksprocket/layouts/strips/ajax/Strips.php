<?php
/**
 * @version   $Id: Strips.php 19249 2014-02-27 19:21:50Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocketSiteLayoutAjaxModelStrips extends RokSprocket_AbstractAjaxRenderingLayoutModel
{
	/**
	 * @param $params
	 * {
	 *  "page":1,
	 *  "moduleid": 86
	 * }
	 *
	 * @return RokCommon_Ajax_Result
	 */
	public function getPage($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$html = '';

			$container = RokCommon_Service::getContainer();

			/** @var $platformHelper RokSprocket_PlatformHelper */
			$platformHelper = $container->roksprocket_platformhelper;
			$module_params  = $platformHelper->getModuleParameters($params->moduleid);
			// add the layout classpath
			$layout_lib_path = $container['roksprocket.layouts.strips.library.paths'];
			foreach ($layout_lib_path as $lib_path) {
				RokCommon_ClassLoader::addPath($lib_path);
			}

			$container = RokCommon_Service::getContainer();
			/** @var $platformHelper RokSprocket_PlatformHelper */
			$platformHelper = $container->roksprocket_platformhelper;
			$items = $platformHelper->getFromCache(array('RokSprocket', 'getItemsWithParams'), array($params->moduleid, $module_params, true), $module_params, $params->moduleid);

			/** @var $layout RokSprocket_Layout_Strips */
			$layout = $container->getService('roksprocket.layout.strips');
			$layout->initialize($items, $module_params);
			$items = $layout->getItems();

			$provider_type = $module_params->get('provider', 'joomla');
			$sort_type     = $module_params->get($provider_type . '_sort', 'automatic');
			if ($sort_type == RokSprocket_ItemCollection::SORT_METHOD_RANDOM) {
				$items->sort($sort_type);
			}

			$limit = $module_params->get('display_limit', '∞');
			if ($limit != '∞' && (int)$limit > 0) {
				$items = $items->trim($limit);
			}
			$offset       = ($params->page - 1) * $module_params->get('strips_items_per_page', 1);
			$items        = $items->slice($offset, $module_params->get('strips_items_per_page', 1));
			$items        = $platformHelper->processItemsForEvents($items, $module_params);

			$themecontext = $layout->getThemeContent();
			ob_start();
			$index = 0;
			foreach ($items as $item) {
				echo $themecontext->load('item.php', array(
				                                          'item'       => $item,
				                                          'parameters' => $module_params,
				                                          'index'	  => $index
				                                     ));
				$index++;
			}
			$html .= ob_get_clean();
			$html = $platformHelper->processOutputForEvents($html, $module_params);
			$result->setPayload(array(
			                         'html'  => $html,
			                         'page'  => $params->page
			                    ));
		} catch (Exception $e) {
			throw $e;
		}
		return $result;
	}
}
