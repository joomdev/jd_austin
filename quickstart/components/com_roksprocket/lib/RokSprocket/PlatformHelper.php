<?php

/**
 * @version   $Id: PlatformHelper.php 19249 2014-02-27 19:21:50Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
interface RokSprocket_PlatformHelper
{
	/**
	 * @abstract
	 * @return mixed
	 */
	public function getCurrentTemplate();

	/**
	 * Get the parameters for the passes in module id
	 * @abstract
	 *
	 * @param $id
	 *
	 * @return RokCommon_Registry
	 */
	public function getModuleParameters($id);

	/**
	 * @abstract
	 *
	 * @param RokSprocket_ItemCollection $items
	 *
	 * @param \RokCommon_Registry        $parameters
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function processItemsForEvents(RokSprocket_ItemCollection $items, RokCommon_Registry $parameters);

	/**
	 * @abstract
	 *
	 * @param string              $output
	 *
	 * @param \RokCommon_Registry $parameters
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function processOutputForEvents($output, RokCommon_Registry $parameters);


	/**
	 * @abstract
	 *
	 * @param $callback
	 * @param $args
	 * @param $params
	 * @param $moduleid
	 *
	 * @return RokSprocket_ItemCollection|bool
	 */
	public function getFromCache($callback, $args, $params, $moduleid);

	/**
	 * Gets the cache directory for the platform
	 *
	 * @abstract
	 * @return string the absolute path to the cache dir
	 */
	public function getCacheDir();

	/**
	 * @abstract
	 * @return string
	 */
	public function getCacheUrl();

	/**
	 * @abstract
	 *
	 * @param $buffer
	 *
	 * @return string
	 */
	public function cleanup($buffer);
}
