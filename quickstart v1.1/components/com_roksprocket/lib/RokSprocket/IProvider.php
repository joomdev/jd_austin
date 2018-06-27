<?php
/**
 * @version   $Id: IProvider.php 19543 2014-03-07 21:49:38Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokSprocket_IProvider
{
	/**
	 *
	 */
	const DO_NOTHING = 0;
	/**
	 *
	 */
	const ATTACH_TO_PROVIDER = 1;
	/**
	 *
	 */
	const EXCLUDE_FROM_PROVIDER = 2;

	/**
	 * @static
	 * @abstract
	 * @return bool
	 */
	public static function isAvailable();

	/**
	 * @abstract
	 * @static
	 * @return array the array of image type and label
	 */
	public static function getImageTypes();

	/**
	 * @abstract
	 * @static
	 * @return array the array of link types and label
	 */
	public static function getLinkTypes();

	/**
	 * @abstract
	 * @static
	 * @return array the array of text types and label
	 */
	public static function getTextTypes();

	/**
	 * Should the passed field be shown for this provider
	 *
	 * @param $type
	 * @param $name
	 *
	 * @return bool
	 */
	public static function shouldShowField($type, $name);

	/**
	 * @param $module_id
	 *
	 * @return bool
	 * @throws RokSprocket_Exception
	 */
	public static function addNewItem($module_id);

	/**
	 * @param $item_id
	 * @param $module_id
	 *
	 * @return bool
	 * @throws RokSprocket_Exception
	 */
	public static function removeItem($item_id, $module_id);

	/**
	 * @abstract
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems();

	/**
	 * @abstract
	 *
	 * @param array $filters
	 * @param array $sort_filters
	 */
	public function setFilterChoices($filters, $sort_filters);

	/**
	 * @abstract
	 *
	 * @param $id
	 */
	public function setModuleId($id);

	/**
	 * @param $ids
	 *
	 * @return mixed
	 */
	public function setDisplayedIds($ids);

	/**
	 * @abstract
	 *
	 */
	public function getFilterProcessor();

	/**
	 * @abstract
	 *
	 * @param $id
	 *
	 * @return RokSprocket_Item
	 */
	public function getArticleInfo($id);

	/**
	 * @abstract
	 *
	 * @param $id
	 *
	 * @return RokSprocket_Item
	 */
	public function getArticlePreview($id);

	/**
	 * @abstract
	 *
	 * @param       $method
	 * @param array $options
	 */
	public function setSortInfo($method, array $options = array());

	/**
	 * @abstract
	 *
	 * @param RokCommon_Registry $params
	 */
	public function setParams(RokCommon_Registry $params);

	/**
	 * @abstract
	 *
	 * @param bool $show
	 */
	public function setShowUnpublished($show = false);

	/**
	 * @abstract
	 *
	 * @param       $type
	 * @param       $name
	 * @param array $currentTypes the current per item types list
	 *
	 * @return
	 */
	public function filterPerItemTypes($type, $name, array &$currentTypes);

	/**
	 * @param $id string|int the module or widget id just saved
	 */
	public function postSave($id);
}
