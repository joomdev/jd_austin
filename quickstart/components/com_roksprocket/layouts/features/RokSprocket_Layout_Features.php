<?php
/**
 * @version   $Id: RokSprocket_Layout_Features.php 28605 2015-07-02 21:04:00Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Layout_Features extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;

	/**
	 * @var array
	 */
	protected static $instanceHeadersRenderedTheme = array();

	/**
	 * @var string
	 */
	protected $name = 'features';

	/**
	 *
	 */
	protected function cleanItemParams()
	{
		foreach ($this->items as $item_id => &$item) {
			$item->setPrimaryImage($this->setupImage($item, 'features_image_default', 'features_image_default_custom', 'features_item_image'));
			$item->setPrimaryLink($this->setupLink($item, 'features_link_default', 'features_link_default_custom', 'features_item_link'));
			$item->setTitle($this->setupText($item, 'features_title_default', false, 'features_item_title'));
			$item->setText($this->setupText($item, 'features_description_default', false, 'features_item_description'));

			// clean from tags and limit words amount
			$desc = $item->getText();
			if ($this->parameters->get('features_strip_html_tags', true)) {
				$desc = strip_tags($desc);
			}
			$words_amount = $this->parameters->get('features_previews_length', false);
			if ($words_amount === '∞' || $words_amount == '0') {
				$words_amount = false;
			}
			$htmlmanip = new RokSprocket_Util_HTMLManipulator();
			$preview   = $htmlmanip->truncateHTML($desc, $words_amount);
			$append    = strlen($desc) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);

			// resizing images if needed
			if ($item->getPrimaryImage()) {
				if ($this->parameters->get('features_resize_enable', false)) {
					$width  = $this->parameters->get('features_resize_width', 0);
					$height = $this->parameters->get('features_resize_height', 0);
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
		RokCommon_Header::addStyle($this->theme_context->getUrl($this->theme . '.css'));
		RokCommon_Header::addScript($this->theme_context->getUrl($this->theme . '.js'));

		$id                  = $this->parameters->get('module_id');
		$settings            = new stdClass();
		$settings->animation = $this->parameters->get('features_animation', 'crossfade');
		$settings->autoplay  = $this->parameters->get('features_autoplay', 1);
		$settings->delay     = $this->parameters->get('features_autoplay_delay', 5);
		$options             = json_encode($settings);

		$js   = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	RokSprocket.instances." . $this->theme . ".attach(" . $id . ", '" . $options . "');";
		$js[] = "});";
        $js[] = "window.addEvent('load', function(){";
        $js[] = "   var overridden = false;";
        $js[] = "   if (!overridden && window.G5 && window.G5.offcanvas){";
        $js[] = "       var mod = document.getElement('[data-".$this->theme."=\"" . $id . "\"]');";
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
		$rendered = self::$instanceHeadersRenderedTheme;
		if (!isset($rendered[$this->theme]) || !$rendered[$this->theme]) {
			$instance   = array();
			$instance[] = "window.addEvent('domready', function(){";
			$instance[] = "		RokSprocket.instances." . $this->theme . " = new RokSprocket." . ucfirst($this->theme) . "();";
			$instance[] = "});";

			RokCommon_Header::addInlineScript(implode("\n", $instance) . "\n");

			self::$instanceHeadersRenderedTheme[$this->theme] = true;
		}

		if (!self::$instanceHeadersRendered) {
			$root_assets   = RokCommon_Composite::get($this->basePackage . '.assets.js');
			$layout_assets = RokCommon_Composite::get($this->layoutPackage . '.assets.js');
			RokCommon_Header::addScript($root_assets->getUrl('moofx.js'));
			RokCommon_Header::addScript($layout_assets->getUrl('features.js'));

			self::$instanceHeadersRendered = true;
		}
	}
}
