<?php
/**
 * @version   $Id: Interface.php 14373 2013-10-09 22:49:38Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokSprocket_Provider_Simple_Storage_Interface
{
	/**
	 *
	 */
	const PROVIDER_NAME = 'simple';

	/**
	 * @param $item_id
	 * @param $module_id
	 *
	 * @return bool
	 * @throws RokSprocket_Exception
	 */
	public function removeItem($item_id, $module_id);

	/**
	 * @param $module_id
	 *
	 * @return bool
	 * @throws RokSprocket_Exception
	 */
	public function addNewItem($module_id);

	/**
	 *
	 * @param $module_id
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems($module_id, $displayedIds = array());
}
