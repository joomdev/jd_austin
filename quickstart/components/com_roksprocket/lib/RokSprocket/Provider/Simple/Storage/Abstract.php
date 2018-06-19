<?php
/**
 * @version   $Id: Abstract.php 19264 2014-02-27 23:28:13Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider_Simple_Storage_Abstract implements RokSprocket_Provider_Simple_Storage_Interface
{
	/**
	 * @param $item_id
	 * @param $module_id
	 *
	 * @return bool|void
	 * @throws RokSprocket_Exception
	 */
	public function removeItem($item_id, $module_id)
	{

		$items              = $this->getItems($module_id);
		$removed_item_order = $items[RokSprocket_Provider_Simple_Storage_Interface::PROVIDER_NAME . '-' . $item_id]->getOrder();
		unset($items[RokSprocket_Provider_Simple_Storage_Interface::PROVIDER_NAME . '-' . $item_id]);
		/** @var $item RokSprocket_Item */
		foreach ($items as $item) {
			if ($item->getOrder() > $removed_item_order) {
				$item->setOrder($item->getOrder() - 1);
			}
		}
		$items->sort(RokSprocket_ItemCollection::SORT_METHOD_MANUAL);
		RokCommon_Session::set('roksprocket.module_' . $module_id, serialize($items));
		return true;
	}

	/**
	 * @param $module_id
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems($module_id, $displayedIds = array())
	{
		/** @var  RokSprocket_ItemCollection $items */
		if ($this->isAdmin()) {
			$items = RokCommon_Session::get('roksprocket.module_' . $module_id, false);
			if ($items === false) {
				$items = RokCommon_Session::set('roksprocket.module_' . $module_id, serialize($this->getItemsFromDB($module_id)));
			}
			$items = unserialize($items);
		} else {
			$items = $this->getItemsFromDB($module_id, $displayedIds);
		}
		$items->sort(RokSprocket_ItemCollection::SORT_METHOD_MANUAL);
		return $items;
	}

	/**
	 * @return mixed
	 */
	abstract protected function isAdmin();

	/**
	 * @param $module_id
	 *
	 * @return mixed
	 */
	abstract protected function getItemsFromDB($module_id, $displayedIds = array());

	/**
	 * @param $module_id
	 *
	 * @return bool
	 * @throws RokSprocket_Exception
	 */
	public function addNewItem($module_id)
	{

		/** @var RokSprocket_ItemCollection $items */
		$items       = $this->getItems($module_id);
		$new_rs_item = new RokSprocket_Item();
		$new_rs_item->setProvider(RokSprocket_Provider_Simple_Storage_Interface::PROVIDER_NAME);
		$new_rs_item->setId($items->getNextId());
		$new_rs_item->setParam('_article_title', rc__('ROKSPROCKET_NEW_SIMPLE_ITEM_TITLE', $new_rs_item->getId()));
		$new_rs_item->setTitle(rc__('ROKSPROCKET_NEW_SIMPLE_ITEM_TITLE', $new_rs_item->getId()));
		$new_rs_item->setOrder(0);
		/** @var RokSprocket_Item $item */
		foreach ($items as $item) {
			$item->setOrder($item->getOrder() + 1);
		}
		$items[$new_rs_item->getArticleId()] = $new_rs_item;
		$items->sort(RokSprocket_ItemCollection::SORT_METHOD_MANUAL);
		RokCommon_Session::set('roksprocket.module_' . $module_id, serialize($items));
		return true;
	}

	/**
	 * @param array $data
	 *
	 * @return RokSprocket_ItemCollection
	 */
	protected function convertRawToItems(array $data)
	{
		$collection = new RokSprocket_ItemCollection();
		$dborder    = 0;
		foreach ($data as $raw_item) {
			$item                              = $this->convertRawToItem($raw_item, $dborder);
			$collection[$item->getArticleId()] = $item;
			$dborder++;
		}
		return $collection;
	}

	/**
	 * @param     $raw_item
	 * @param int $dborder
	 *
	 * @return \RokSprocket_Item
	 */
	protected function convertRawToItem($raw_item, $dborder = 0)
	{
		$item = new RokSprocket_Item();
		$item->setProvider(RokSprocket_Provider_Simple_Storage_Interface::PROVIDER_NAME);
		$item->setId($raw_item->id);

		$params = RokCommon_JSON::decode($raw_item->params, null, true);
		if ($params !== false && is_array($params) && array_key_exists('_article_title', $params)) {
			$item->setTitle($params['_article_title']);
		} else {
			$item->setTitle('Simple Item');
		}
		return $item;
	}
}
