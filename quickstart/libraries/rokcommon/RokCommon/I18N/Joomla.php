<?php
/**
 * @version   $Id: Joomla.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


/**
 *
 */
class RokCommon_I18N_Joomla extends JText implements RokCommon_I18N
{
	/**
	 * javascript strings
	 */
	protected static $strings = array();

	/**
	 * @param  $string
	 *
	 * @return string
	 */
	public function translateFormatted($string)
	{
		$args = func_get_args();
		$out  = call_user_func_array(array($this, 'sprintf'), $args);
		return $out;
	}

	/**
	 * @param  $string
	 * @param  $count
	 *
	 * @return string
	 */
	public function translatePlural($string, $count)
	{
		$args = func_get_args();
		$out  = call_user_func_array(array($this, 'plural'), $args);
		return $out;
	}

	/**
	 * @param  $string
	 *
	 * @return string
	 */
	public function translate($string)
	{
		$args = func_get_args();
		$out  = call_user_func_array(array($this, '_'), $args);
		return $out;
	}

	/**
	 *
	 * @param $domain
	 * @param $path
	 *
	 * @return bool
	 */
	public function loadLanguageFiles($domain, $path)
	{
		$lang = JFactory::getLanguage();
		$lang->load($domain, $path, $lang->getDefault(), false, false);
		return $lang->load($domain, $path, null, false, false);
	}


}
	