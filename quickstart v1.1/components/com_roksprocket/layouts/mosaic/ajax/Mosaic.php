<?php
/**
 * @version   $Id: Mosaic.php 30593 2018-05-26 07:41:08Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocketSiteLayoutAjaxModelMosaic extends RokSprocket_AbstractAjaxRenderingLayoutModel
{
	/**
	 * @param $params
	 *  {
	 *  "page":1,
	 *  "moduleid": 86
	 *  }
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
			$layout_lib_path = $container['roksprocket.layouts.mosaic.library.paths'];
			foreach ($layout_lib_path as $lib_path) {
				RokCommon_ClassLoader::addPath($lib_path);
			}

			$container = RokCommon_Service::getContainer();
			/** @var $platformHelper RokSprocket_PlatformHelper */
			$platformHelper = $container->roksprocket_platformhelper;


			$items = $platformHelper->getFromCache(array(
			                                            'RokSprocket',
			                                            'getItemsWithParams'
			                                       ), array(
			                                               $params->moduleid,
			                                               $module_params,
			                                               true,
			                                               false,
			                                               $params->displayed
			                                          ), $module_params, $params->moduleid);


			/** @var $layout RokSprocket_Layout_Mosaic */
			$layout = $container->getService('roksprocket.layout.mosaic');

			$layout->initialize($items, $module_params);
			$items = $layout->getItems();


			if (isset($params->filter) && $params->filter && $params->filter != 'all') {
				$filtered = new RokSprocket_ItemCollection();
				foreach ($items as $item) {
					if ($this->in_arrayi($params->filter, $item->getTags())) $filtered->addItem($item);
				}

				$items = $filtered;
			}

			$total_items     = count($items);
			$limit           = $module_params->get('display_limit', '∞');
			$per_page        = $module_params->get('mosaic_items_per_page', 1);
			$displayed_items = (isset($params->displayed)) ? count($params->displayed) : 0;
			$new_limit       = ((int)$limit > (int)$displayed_items) ? (int)$limit - (int)$displayed_items : 0;
			$original_total  = $displayed_items + $total_items;

			//trim to allowed limit
			if ($limit != '∞' && (int)$new_limit > 0 && $total_items > $new_limit) {
				$items = $items->trim($new_limit);
			}
			//trim if showing only next page
			if (!isset($params->all) && count($items) > $per_page) {
				$items = $items->trim($per_page);
			}
			$items = $platformHelper->processItemsForEvents($items, $module_params);


			$more = true;
			if (isset($params->all) && $params->all) //showing all
				$more = false;
			if ($limit != '∞' && ($displayed_items + count($items)) >= (int)$limit) //limit is met
				$more = false;
			if (($displayed_items + count($items)) >= $original_total) //all have been shown
				$more = false;


			$provider_type = $module_params->get('provider', 'joomla');
			$sort_type     = $module_params->get($provider_type . '_sort', 'automatic');
			if ($sort_type == RokSprocket_ItemCollection::SORT_METHOD_RANDOM) {
				$items->sort($sort_type);
			}


			$themecontext = $layout->getThemeContent();
			ob_start();

			$index     = 0;
			$displayed = $params->displayed;
			foreach ($items as $item_id => &$item) {
				echo $themecontext->load('item.php', array(
				                                          'item'       => $item,
				                                          'parameters' => $module_params,
				                                          'index'      => $index
				                                     ));

				array_push($displayed, (int)$item->getId());
				$index++;
			}
			$html .= ob_get_clean();
			$html = $platformHelper->processOutputForEvents($html, $module_params);
			$result->setPayload(array(
			                         'page'      => $params->page,
			                         'more'      => $more,
			                         'behavior'  => $params->behavior,
			                         'displayed' => $displayed,
			                         'html'      => $html
			                    ));
		} catch (Exception $e) {
			throw $e;
		}
		return $result;
	}

	function in_arrayi($needle, $haystack) {
		$keys = array_keys($haystack);
	    return in_array(strtolower($needle), array_map('strtolower', $keys));
	}
}
