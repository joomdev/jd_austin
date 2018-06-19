<?php
/**
 * @version   $Id: RokSprocket_Layout_Tabs.php 28636 2015-07-09 15:40:49Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Layout_Tabs extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;

	/**
	 * @var string
	 */
	protected $name = 'tabs';

	/**
	 *
	 */
	protected function cleanItemParams()
	{
		foreach ($this->items as $item_id => &$item) {
			$item->setTitle($this->setupText($item, 'tabs_title_default', false, 'tabs_item_title'));
			$item->setParam('tabs_item_icon', $this->setupImage($item, 'tabs_icon_default', 'tabs_icon_default_custom', 'tabs_item_icon'));
			$item->setPrimaryImage($item->getParam('tabs_item_icon'));
			$item->setPrimaryLink($this->setupLink($item, 'tabs_link_default', 'tabs_link_default_custom', 'tabs_item_link'));
			$item->setParam('tabs_item_title', $this->setupText($item, 'tabs_title_default', false, 'tabs_item_title'));
			$item->setText($this->setupText($item, 'tabs_description_default', 'tabs_description_default_custom', 'tabs_item_description'));

			// clean from tags and limit words amount
			$desc = $item->getText();
			if ($this->parameters->get('tabs_strip_html_tags', false)) {
				$desc = strip_tags($desc);
			}
			$words_amount = $this->parameters->get('tabs_previews_length', 0);
			if ($words_amount === '∞' || $words_amount == '0') {
				$words_amount = false;
			}
			$htmlmanip = new RokSprocket_Util_HTMLManipulator();
			$preview   = $htmlmanip->truncateHTML($desc, $words_amount);
			$append    = strlen($desc) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);

			// resizing images if needed
			if ($item->getPrimaryImage()) {
				if ($this->parameters->get('tabs_resize_enable', false)) {
					$width  = $this->parameters->get('tabs_resize_width', 0);
					$height = $this->parameters->get('tabs_resize_height', 0);
					$item->getPrimaryImage()->resize($width, $height);
				}
				/** @var RokCommon_PlatformInfo $platforminfo */
				$platforminfo = $this->container->platforminfo;
				$urlbase = ($platforminfo->getUrlBase()) ? $platforminfo->getUrlBase() : '/';
				if (!$platforminfo->isLinkexternal($item->getPrimaryImage()->getSource())
					&& strpos($item->getPrimaryImage()->getSource(), '/') !== 0
					&& strpos($item->getPrimaryImage()->getSource(), $urlbase) !== 0) {
					$source = rtrim($urlbase, '/') . '/' . $item->getPrimaryImage()->getSource();
					$item->getPrimaryImage()->setSource($source);
				}
			}
		}
	}

	/**
	 * @return bool|string
	 */
	public function renderBody()
	{

		$theme_basefile = $this->container[sprintf('roksprocket.layouts.%s.themes.%s.basefile', $this->name, $this->theme)];
		return $this->theme_context->load($theme_basefile, array(
		                                                        'layout'     => $this,
		                                                        'items'      => $this->items,
		                                                        'parameters' => $this->parameters
		                                                   ));
	}

	/**
	 * Called to render headers that should be included on a per module instance basis
	 */
	public function renderInstanceHeaders()
	{
		RokCommon_Header::addScript($this->theme_context->getUrl('tabs.js'));
		RokCommon_Header::addStyle($this->theme_context->getUrl('tabs.css'));

		$id                 = $this->parameters->get('module_id');
		$settings           = new stdClass();
		$settings->autoplay = $this->parameters->get('tabs_autoplay', 0);
		$settings->delay    = $this->parameters->get('tabs_autoplay_delay', 1);
		$options            = json_encode($settings);

		$js   = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	RokSprocket.instances.tabs.attach(" . $id . ", '" . $options . "');";
		$js[] = "});";
        $js[] = "window.addEvent('load', function(){";
        $js[] = "   var overridden = false;";
        $js[] = "   if (!overridden && window.G5 && window.G5.offcanvas){";
        $js[] = "       var mod = document.getElement('[data-".$this->name."=\"" . $id . "\"]');";
        $js[] = "       mod.addEvents({";
        $js[] = "           touchstart: function(){ window.G5.offcanvas.detach(); },";
        $js[] = "           touchend: function(){ window.G5.offcanvas.attach(); }";
        $js[] = "       });";
        $js[] = "       overridden = true;";
        $js[] = "   };";
        $js[] = "});";
		RokCommon_Header::addInlineScript(implode("\n", $js) . "\n");
	}

	/**
	 * Called to render headers that should be included only once per Layout type used
	 */
	public function renderLayoutHeaders()
	{
		if (!self::$instanceHeadersRendered) {

			$instance   = array();
			$instance[] = "window.addEvent('domready', function(){";
			$instance[] = "		RokSprocket.instances.tabs = new RokSprocket.Tabs();";
			$instance[] = "});";

			RokCommon_Header::addInlineScript(implode("\n", $instance) . "\n");

			self::$instanceHeadersRendered = true;
		}
	}
}
