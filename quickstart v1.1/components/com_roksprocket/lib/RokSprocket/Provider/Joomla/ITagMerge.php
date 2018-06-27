<?php
 /**
  * @version   $Id: ITagMerge.php 19581 2014-03-10 22:02:54Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
interface RokSprocket_Provider_Joomla_ITagMerge {
	/**
	 * @param array $items
	 * @throws RokSprocket_Exception
	 */
	public function populateTags(array $items);
}
 