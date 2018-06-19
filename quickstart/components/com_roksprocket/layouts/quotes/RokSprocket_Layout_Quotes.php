<?php
/**
 * @version   $Id: RokSprocket_Layout_Quotes.php 11547 2013-06-18 20:17:15Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Layout_Quotes extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;

	/**
	 * @var string
	 */
	protected $name = 'quotes';


	/**
	 *
	 */
	protected function cleanItemParams()
	{
		foreach ($this->items as $item_id => &$item) {

			$item->setPrimaryImage($this->setupImage($item, 'quotes_image_default', 'quotes_image_default_custom', 'quotes_item_image'));
			$item->setPrimaryLink($this->setupLink($item, 'quotes_link_default', 'quotes_link_default_custom', 'quotes_item_link'));
			$item->setTitle($this->setupText($item, 'quotes_title_default', false, 'quotes_item_title'));
			$item->setText($this->setupText($item, 'quotes_description_default', false, 'quotes_item_description'));
			$item->setParam('quotes_item_author', $this->setupText($item, 'quotes_author_default', 'quotes_author_default_custom', 'quotes_item_author'));
			$item->setParam('quotes_item_subtext', $this->setupText($item, 'quotes_subtext_default', 'quotes_subtext_default_custom', 'quotes_item_subtext'));
			$item->setParam('quotes_item_direction', $this->setupText($item, 'quotes_direction_default', 'quotes_direction_default_custom', 'quotes_item_direction'));

			// clean from tags and limit words amount
			$desc = $item->getText();
			if ($this->parameters->get('quotes_strip_html_tags', true)) {
				$desc = strip_tags($desc);
			}
			$words_amount = $this->parameters->get('quotes_previews_length', 20);
			if ($words_amount === '∞' || $words_amount == '0') {
				$words_amount = false;
			}
			$htmlmanip = new RokSprocket_Util_HTMLManipulator();
			$preview   = $htmlmanip->truncateHTML($desc, $words_amount);
			$append    = strlen($desc) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);

			// resizing images if needed
			if ($item->getPrimaryImage()) {
				if ($this->parameters->get('quotes_resize_enable', false)) {
					$width  = $this->parameters->get('quotes_resize_width', 0);
					$height = $this->parameters->get('quotes_resize_height', 0);
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
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems()
	{
		return $this->items;
	}


	/**
	 * @return RokCommon_Composite_Context
	 */
	public function getThemeContent()
	{
		return $this->theme_context;
	}

	/**
	 * @return bool|string
	 */
	public function renderBody()
	{

		$theme_basefile = $this->container[sprintf('roksprocket.layouts.%s.themes.%s.basefile', $this->name, $this->theme)];
		$items          = $this->items->slice(0, $this->parameters->get('quotes_items_per_page', 5));

		if (!$this->items->count() && !count($items)) $pages = 0; else $pages = ceil($this->items->count() / count($items));

		return $this->theme_context->load($theme_basefile, array(
		                                                        'layout'     => $this,
		                                                        'items'      => $items,
		                                                        'pages'      => $pages,
		                                                        'parameters' => $this->parameters
		                                                   ));
	}

	/**
	 * Called to render headers that should be included on a per module instance basis
	 */
	public function renderInstanceHeaders()
	{

		$filename = ($this->theme == 'default' ? 'quotes' : $this->theme);
		RokCommon_Header::addStyle($this->theme_context->getUrl($filename . '.css'));
		RokCommon_Header::addScript($this->theme_context->getUrl($filename . '.js'));
		RokCommon_Header::addScript($this->theme_context->getUrl($filename . '-speeds.js'));

		$id                  = $this->parameters->get('module_id');
		$settings            = new stdClass();
		$settings->animation = $this->parameters->get('quotes_animation', 'fadeDelay');
		$settings->autoplay  = $this->parameters->get('quotes_autoplay', 0);
		$settings->delay     = $this->parameters->get('quotes_autoplay_delay', 5);
		$options             = json_encode($settings);

		$js   = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	RokSprocket.instances.quotes.attach(" . $id . ", '" . $options . "');";
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

			$root_assets = RokCommon_Composite::get($this->basePackage . '.assets.js');
			$layout_assets = RokCommon_Composite::get($this->layoutPackage . '.assets.js');
			RokCommon_Header::addScript($root_assets->getUrl('moofx.js'));
			RokCommon_Header::addScript($root_assets->getUrl('roksprocket.request.js'));
			RokCommon_Header::addScript($layout_assets->getUrl('quotes.js'));
			RokCommon_Header::addScript($layout_assets->getUrl('quotes-speeds.js'));

			$instance   = array();
			$instance[] = "window.addEvent('domready', function(){";
			$instance[] = "		RokSprocket.instances.quotes = new RokSprocket.Quotes();";
			$instance[] = "});";

			RokCommon_Header::addInlineScript(implode("\n", $instance) . "\n");

			self::$instanceHeadersRendered = true;
		}
	}

	/**
	 * @param      $string
	 * @param bool $amount
	 *
	 * @return string
	 */
	public function _getWords($string, $amount = false)
	{
		if (!$amount) $amount = strlen($string);
		$words = explode(' ', $string, ($amount + 1));
		if (count($words) > $amount) array_pop($words);

		return implode(' ', $words);
	}
}
