<?php
/**
 * @version   $Id: Header.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;
/**
 * @deprecated Use the container to get the header service
 */
class RokCommon_Header
{
	/**
	 * @param \RokCommon_IHeader $platform_instance
	 * @deprecated
	 */
	public function __construct(RokCommon_IHeader $platform_instance)
	{
		$this->platform_instance = $platform_instance;

	}

	/**
	 * @param $file
	 * @deprecated
	 */
	public static function addScript($file)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $header RokCommon_IHeader */
		$header = $container->header;
		$header->addScript($file);
	}

	/**
	 * @param $text
	 * @deprecated
	 */
	public static function addInlineScript($text)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $header RokCommon_IHeader */
		$header = $container->header;
		$header->addInlineScript($text);
	}

	/**
	 * @param $file
	 * @deprecated
	 */
	public static function addStyle($file)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $header RokCommon_IHeader */
		$header = $container->header;
		$header->addStyle($file);
	}

	/**
	 * @param $text
	 * @deprecated
	 */
	public static function addInlineStyle($text)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $header RokCommon_IHeader */
		$header = $container->header;
		$header->addInlineStyle($text);
	}
}
