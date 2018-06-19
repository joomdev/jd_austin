<?php
/**
 * @version   $Id: RokInjectModule_Header.php 19267 2014-02-28 00:18:56Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

class RokInjectModule_Header extends RokCommon_Header_AbstractHeader
{
	/**
	 * @var JDocument
	 */
	protected $document;

	/**
	 * @var bool
	 */
	protected $populated = false;

	public function isPopulated()
	{
		return $this->populated;
	}

	public function __construct()
	{
		parent::__construct();
		$this->document = JFactory::getDocument();
	}

	public function addScript($file, $order = self::DEFAULT_ORDER)
	{
		if (!empty($file)) {
			$this->document->addScript($file);
		}
	}

	public function addInlineScript($text, $order = self::DEFAULT_ORDER)
	{
		if (!empty($text)) {
			$this->document->addScriptDeclaration($text);
		}
	}

	public function addStyle($file, $order = self::DEFAULT_ORDER)
	{
		if (!empty($file)) {
			$this->document->addStyleSheet($file);
		}
	}

	public function addInlineStyle($text, $order = self::DEFAULT_ORDER)
	{
		if (!empty($text)) {
			$this->document->addStyleDeclaration($text);

		}
	}

	public function addDomReadyScript($js, $order = self::DEFAULT_ORDER)
	{
		if (!empty($js)) {
			$this->document->addScriptDeclaration($js);
		}
	}

	public function addLoadScript($js, $order = self::DEFAULT_ORDER)
	{
		if (!empty($js)) {
			$this->document->addScriptDeclaration($js);
		}
	}
	
	public function populate()
	{
		return true;
	}
}
