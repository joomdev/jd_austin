<?php
/**
 * @version   $Id: Layout.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokSprocket_Layout
{
    public function __construct(RokCommon_Dispatcher $dispatcher);

    public function setParameters(RokCommon_Registry $parameters);

    public function setItems(RokSprocket_ItemCollection $items);

    public function initialize(RokSprocket_ItemCollection $items, RokCommon_Registry $parameters);

	/**
	 * @abstract
	 * Called to render the body of the Layout on every instance that it is used.
	 */
    public function renderBody();

	/**
	 * @abstract
	 * Called to render headers that should be included on a per module instance basis
	 */
    public function renderInstanceHeaders();

	/**
	 * @abstract
	 * Called to render headers that should be included only once per Layout type used
	 */
	public function renderLayoutHeaders();

}
