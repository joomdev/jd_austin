<?php
/**
 * @version   $Id: Helper.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Helper
{
	public static function getRedirectionOption(){
		$session = JFactory::getSession();
		$option = $session->get('com_roksprocket.redirected.from', 'com_modules');
		$session->set('com_roksprocket.redirected.from', null);
		return $option;
	}
}
