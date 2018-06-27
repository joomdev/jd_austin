<?php
/**
 * @version   $Id: Joomla.php 14373 2013-10-09 22:49:38Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Simple_Storage_Joomla extends RokSprocket_Provider_Simple_Storage_Abstract
{
	/**
	 * @return bool
	 */
	protected function isAdmin()
	{
		return JFactory::getApplication()->isAdmin();
	}

	/**
	 * @param $module_id
	 *
	 * @return RokSprocket_ItemCollection
	 */
	protected function getItemsFromDB($module_id, $displayedIds = array())
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('i.provider_id as id, i.order, i.params')->from('#__roksprocket_items as i');
		$query->where('i.module_id = ' . $db->quote($module_id));
		$query->where('i.provider = ' . $db->quote(RokSprocket_Provider_Simple_Storage_Interface::PROVIDER_NAME));
		if(count($displayedIds) > 0){
			$query->where('i.provider_id  NOT IN (' . implode(',', $displayedIds) . ')');
		}
		$query->order('i.order');
		$db->setQuery($query);

		$raw_results = $db->loadObjectList();

		$converted = $this->convertRawToItems($raw_results);
		$this->mapPerItemData($converted, $module_id);
		return $converted;
	}

	/**
	 * @param RokSprocket_ItemCollection $items
	 *
	 * @param string                     $module_id
	 *
	 * @throws RokSprocket_Exception
	 */
	protected function mapPerItemData(RokSprocket_ItemCollection &$items, $module_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('i.provider_id as id, i.order, i.params')->from('#__roksprocket_items as i');
		$query->where('i.module_id = ' . $db->quote($module_id));
		$query->where('i.provider = ' . $db->quote(RokSprocket_Provider_Simple_Storage_Interface::PROVIDER_NAME));
		$db->setQuery($query);
		$sprocket_items = $db->loadObjectList('id');
		if ($error = $db->getErrorMsg()) {
			throw new RokSprocket_Exception($error);
		}

		/** @var $items RokSprocket_Item[] */
		foreach ($items as $item_id => &$item) {
			list($provider, $id) = explode('-', $item_id);
			if (array_key_exists($id, $sprocket_items)) {
				$items[$item_id]->setOrder((int)$sprocket_items[$id]->order);
				if (null != $sprocket_items[$id]->params) {
					$decoded = null;
					try {
						$decoded = RokCommon_Utils_ArrayHelper::fromObject(RokCommon_JSON::decode($sprocket_items[$id]->params));
					} catch (RokCommon_JSON_Exception $jse) {
					}
					$items[$item_id]->setParams($decoded);
				} else {
					$items[$item_id]->setParams(array());
				}
			}
		}
	}

}
