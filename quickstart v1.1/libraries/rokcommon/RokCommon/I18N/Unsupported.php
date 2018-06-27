<?php
/**
 * @version   $Id: Unsupported.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


/**
 *
 */
class RokCommon_I18N_Unsupported implements RokCommon_I18N
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
		return $string;
	}

	/**
	 * @param  $count
	 * @param  $string
	 *
	 * @return string
	 */
	public function translatePlural($string, $count)
	{
		return $string;
	}

	/**
	 * @param  $string
	 *
	 * @return string
	 */
	public function translate($string)
	{
		return $string;
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
		return true;
	}

}
	