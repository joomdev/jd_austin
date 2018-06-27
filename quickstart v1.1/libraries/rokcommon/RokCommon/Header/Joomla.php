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
class RokCommon_Header_Joomla extends RokCommon_Header_AbstractHeader
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
			if (!$this->isPopulated()) {
				parent::addScript($file, $order);
			} else {
				$this->document->addScript($file);
			}
		}
	}

	public function addInlineScript($text, $order = self::DEFAULT_ORDER)
	{
		if (!empty($text)) {
			if (!$this->isPopulated()) {
				parent::addInlineScript($text, $order);
			} else {
				$this->document->addScriptDeclaration($text);
			}
		}
	}

	public function addStyle($file, $order = self::DEFAULT_ORDER)
	{
		if (!empty($file)) {
			if (!$this->isPopulated()) {

				parent::addStyle($file, $order);
			} else {
				$this->document->addStyleSheet($file);
			}
		}
	}

	public function addInlineStyle($text, $order = self::DEFAULT_ORDER)
	{
		if (!empty($text)) {
			if (!$this->isPopulated()) {
				parent::addInlineStyle($text, $order);
			} else {
				$this->document->addStyleDeclaration($text);
			}
		}
	}

	public function addDomReadyScript($js, $order = self::DEFAULT_ORDER)
	{
		if (!empty($js)) {
			if (!$this->isPopulated()) {
				parent::addDomReadyScript($js, $order);
			} else {
				$this->document->addScriptDeclaration($js);
			}
		}
	}

	public function addLoadScript($js, $order = self::DEFAULT_ORDER)
	{
		if (!empty($js)) {
			if (!$this->isPopulated()) {
				parent::addLoadScript($js, $order);
			} else {
				$this->document->addScriptDeclaration($js);
			}
		}
	}

	public function populate()
	{
		if (!empty($this->script_files)) {
			ksort($this->script_files);
			foreach ($this->script_files as $order => $order_entries) {
				foreach ($order_entries as $entry_key => $entry) {
					$this->document->addScript($entry);
				}
			}
		}
		if (!empty($this->inline_scripts)) {
			ksort($this->inline_scripts);
			foreach ($this->inline_scripts as $order => $order_entries) {
				foreach ($order_entries as $entry_key => $entry) {
					$this->document->addScriptDeclaration($entry);
				}
			}

		}
		if (!empty($this->style_files)) {
			ksort($this->style_files);
			foreach ($this->style_files as $order => $order_entries) {
				foreach ($order_entries as $entry_key => $entry) {
					$this->document->addStyleSheet($entry);
				}
			}
		}
		if (!empty($this->inline_styles)) {
			ksort($this->inline_styles);
			foreach ($this->inline_styles as $order => $order_entries) {
				foreach ($order_entries as $entry_key => $entry) {
					$this->document->addStyleDeclaration($entry);
				}
			}
		}

		// Generate domready script
		if (!empty($this->domready_scripts)) {
			ksort($this->domready_scripts);
			$strHtml = 'window.addEvent(\'domready\', function() {';
			foreach ($this->domready_scripts as $order => $order_entries) {
				foreach ($order_entries as $entry_key => $entry) {
					$strHtml .= chr(13) . $entry;
				}
			}
			$strHtml .= chr(13) . '});' . chr(13);
			$this->document->addScriptDeclaration($strHtml);
		}

		if (!empty($this->loadevent_scripts)) {
			ksort($this->loadevent_scripts);
			$strHtml = 'window.addEvent(\'load\', function() {';
			foreach ($this->loadevent_scripts as $order => $order_entries) {
				foreach ($order_entries as $entry_key => $entry) {
					$strHtml .= chr(13) . $entry;
				}
			}
			$strHtml .= chr(13) . '});' . chr(13);
			$this->document->addScriptDeclaration($strHtml);
		}
		$this->populated = true;
		$this->reset();
	}
}
