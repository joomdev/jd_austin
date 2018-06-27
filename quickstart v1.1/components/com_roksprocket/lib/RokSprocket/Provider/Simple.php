<?php
/**
 * @version   $Id: Simple.php 19543 2014-03-07 21:49:38Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Simple extends RokSprocket_Provider
{

	/**
	 * @param array $filters
	 * @param array $sort_filters
	 */
	public function __construct($filters = array(), $sort_filters = array())
	{
		parent::__construct('simple');
		$this->setFilterChoices($filters, $sort_filters);
	}

	/**
	 * @static
	 * @return bool
	 */
	public static function isAvailable()
	{
		return true;
	}

	/**
	 * @return array the array of image type and label
	 */
	public static function getImageTypes()
	{
		return array();
	}

	/**
	 * @return array the array of link types and label
	 */
	public static function getLinkTypes()
	{
		return array();
	}

	/**
	 * @return array the array of link types and label
	 */
	public static function getTextTypes()
	{
		return array();
	}

	public static function shouldShowField($type, $name)
	{
		switch (strtolower($type)) {
			case 'label':
			case 'provideroptionedselector':
				if (preg_match('/_default(s_title|_custom)*$/', strtolower($name))) {
					return self::EXCLUDE_FROM_PROVIDER;
				}
			default:
				return self::DO_NOTHING;
		}
	}

	public static function removeItem($item_id, $module_id)
	{
		$container = RokCommon_Service::getContainer();
		/** @var RokSprocket_Provider_Simple_Storage_Interface $storage */
		$storage = $container->getService('roksprocket.provider.simple_storage');
		return $storage->removeItem($item_id, $module_id);
	}

	/**
	 * @param $module_id
	 *
	 * @return bool
	 */
	public static function addNewItem($module_id)
	{
		$container = RokCommon_Service::getContainer();
		/** @var RokSprocket_Provider_Simple_Storage_Interface $storage */
		$storage = $container->getService('roksprocket.provider.simple_storage');
		return $storage->addNewItem($module_id);
	}

	public function getFilterProcessor()
	{
		$processor_service = $this->container['roksprocket.providers.registered.' . $this->provider_name . '.filter.processor'];
		/** @var $processor RokCommon_Filter_IProcessor */
		$processor = $this->container->$processor_service;
		return $processor;
	}

	/**
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems()
	{
		/** @var RokSprocket_Provider_Simple_Storage_Interface $storage */
		$storage = $this->container->getService('roksprocket.provider.simple_storage');
		return $storage->getItems($this->module_id, $this->displayed_ids);
	}

	/**
	 *
	 * @param $id
	 *
	 * @return RokSprocket_Item
	 */
	public function getArticleInfo($id)
	{
		return false;
	}

	/**
	 *
	 * @param $id
	 *
	 * @return RokSprocket_Item
	 */
	public function getArticlePreview($id)
	{
		return false;
	}

	public function filterPerItemTypes($type, $name, array &$currentTypes)
	{
		if (strtolower($type) == 'peritempicker' && !preg_match('/_title$/', $name)) {
			if (array_key_exists('-title-', $currentTypes)) unset($currentTypes['-title-']);
		}
		if (array_key_exists('-default-', $currentTypes)) unset($currentTypes['-default-']);
		if (array_key_exists('-article-', $currentTypes)) unset($currentTypes['-article-']);

		return;
	}

	public function postSave($id)
	{
		RokCommon_Session::clear('roksprocket.module_'. $id);
	}


}
