<?php
/**
 * @version   $Id: LinkFieldProcessorInterface.php 13467 2013-09-13 23:41:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokSprocket_Provider_Zoo_LinkFieldProcessorInterface extends RokSprocket_Provider_Zoo_FieldProcessorInterface
{
	/**
	 * @param Element $element
	 *
	 * @return RokSprocket_Item_Link
	 */
	public function getAsSprocketLink(Element $element);
}
