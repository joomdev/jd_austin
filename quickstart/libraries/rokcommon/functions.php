<?php
/**
 * @version   $Id: functions.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if (!defined('ROKCOMMON_FUNCTIONS')) {

	define('ROKCOMMON_FUNCTIONS', __FILE__);

	/**
	 * @param  $string
	 *
	 * @return string
	 */
	function rc__($string)
	{
		try {
			$container = RokCommon_Service::getContainer();
			$i18n      = $container->i18n;
			$args      = func_get_args();
			if (count($args) == 1) {
				return call_user_func_array(array($i18n, 'translate'), $args);
			} else {
				return call_user_func_array(array($i18n, 'translateFormatted'), $args);
			}
		} catch (RokCommon_Loader_Exception $le) {
			//TODO: log a failure to load a translation driver
			return $string;
		}
	}

	/**
	 * @param $string
	 */
	function rc_e($string)
	{
		$args = func_get_args();
		$out  = call_user_func_array('rc__', $args);
		echo $out;
	}

	/**
	 * @param $string
	 * @param $n
	 *
	 * @return string
	 */
	function rc_n($string, $n)
	{
		try {
			$container = RokCommon_Service::getContainer();
			$i18n      = $container->i18n;
			$args      = func_get_args();
			return call_user_func_array(array($i18n, 'translatePlural'), $args);
		} catch (RokCommon_Loader_Exception $le) {
			//TODO: log a failure to load a translation driver
			return $string;
		}
	}

	/**
	 * @param $string
	 * @param $n
	 */
	function rc_ne($string, $n)
	{
		echo rc_n($string, $n);
	}

	function rc_alt($string, $alt)
	{
		$out = rc__($string . '_' . $alt);
		if ($out == $string . '_' . $alt){
			$out = rc__($string);
		}
		return $out;
	}

	function rc_unichr($u) {
	    return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
	}
}
