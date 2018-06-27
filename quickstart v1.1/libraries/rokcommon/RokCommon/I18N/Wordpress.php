<?php
/**
 * @version   $Id: Wordpress.php 29686 2015-12-21 08:27:39Z jakub $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


/**
 *
 */
class RokCommon_I18N_Wordpress implements RokCommon_I18N
{
	/**
	 * @var array
	 */
	protected $domains = array('default');

	/**
	 * @param  $string
	 *
	 * @return string
	 */
	public function translateFormatted($string)
	{
		$args       = func_get_args();
		$translated = $string;
		$entry      = new Translation_Entry(array(
		                                         'singular'  => $string,
		                                         'context'   => null
		                                    ));

		foreach ($this->domains as $domain) {
			/** @var $translator Translations */
			$translator  = get_translations_for_domain($domain);
			$translation = $translator->translate_entry($entry);
			if ($translation && !empty($translation->translations)) {
				$translated = $translation->translations[0];
				$translated = apply_filters('gettext', $translated, $string, $domain);
				break;
			}
		}
		array_shift($args);
		array_unshift($args, $translated);
		$out = call_user_func_array('sprintf', $args);
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
		$args       = func_get_args();
		$singular   = $string . '_1';
		$plural     = $string . '_0';
		$translated = ($count == 1) ? $singular : $plural;
		$entry      = new Translation_Entry(array(
		                                         'singular'  => $string,
		                                         'plural'    => $string,
		                                         'context'   => null
		                                    ));

		foreach ($this->domains as $domain) {
			/** @var $translator Translations */
			$translator  = get_translations_for_domain($domain);
			$translation = $translator->translate_entry($entry);
			if ($translation && !empty($translation->translations)) {
				$translated = $translation->translations[0];
				$translated = apply_filters('gettext', $translated, $string, $domain);
				break;
			}
		}
		return $translated;
	}

	/**
	 * @param  $string
	 *
	 * @return string
	 */
	public function translate($string)
	{
		$args            = func_get_args();
		$translated      = $string;
		$entry           = new Translation_Entry();
		$entry->singular = $string;
		$entry->context  = null;

		foreach ($this->domains as $domain) {
			/** @var $translator Translations */
			$translator  = get_translations_for_domain($domain);
			$translation = $translator->translate_entry($entry);
			if ($translation && !empty($translation->translations)) {
				$translated = $translation->translations[0];
				$translated = apply_filters('gettext', $translated, $string, $domain);
				break;
			}
		}
		return $translated;
	}

	/**
	 * @param $domain
	 */
	public function addDomain($domain)
	{
		$this->domains[] = $domain;
		$this->domains   = array_unique($this->domains);
	}

	/**
	 *
	 * @param $domain
	 * @param $path
	 *
	 * @return mixed
	 */
	public function loadLanguageFiles($domain, $path)
	{
		//load translator
		load_plugin_textdomain($domain, false, $path);
		$this->addDomain($domain);
		return true;
	}

}
	