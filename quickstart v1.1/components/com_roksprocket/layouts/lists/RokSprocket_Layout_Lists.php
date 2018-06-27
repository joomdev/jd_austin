<?php
/**
 * @version   $Id: RokSprocket_Layout_Lists.php 28636 2015-07-09 15:40:49Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Layout_Lists extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;

	/**
	 * @var string
	 */
	protected $name = 'lists';


	/**
	 *
	 */
	protected function cleanItemParams()
	{
		foreach ($this->items as $item_id => &$item) {

			$item->setPrimaryImage($this->setupImage($item, 'lists_image_default', 'lists_image_default_custom', 'lists_item_image'));
			$item->setPrimaryLink($this->setupLink($item, 'lists_link_default', 'lists_link_default_custom', 'lists_item_link'));
			$item->setTitle($this->setupText($item, 'lists_title_default', false, 'lists_item_title'));
			$item->setText($this->setupText($item, 'lists_description_default', false, 'lists_item_description'));

			// clean for accordion/non-accordion mode

			$empty_title = !$item->getTitle() || !strlen($item->getTitle());
			if ($empty_title) $item->setTitle('&nbsp;');
			$item->custom_can_show_title = 1;
			$item->custom_can_have_link  = 0;

			if (!$this->parameters->get('lists_enable_accordion')) {
				if ($empty_title) $item->custom_can_show_title = 0;
			}

			if (!$this->parameters->get('lists_enable_accordion') && $item->getPrimaryLink()) {
				$item->custom_can_have_link = 1;
			}

			// clean from tags and limit words amount
			$desc = $item->getText();
			if ($this->parameters->get('lists_strip_html_tags', true)) {
				$desc = strip_tags($desc);
			}
			$words_amount = $this->parameters->get('lists_previews_length', 20);
			if ($words_amount === '∞' || $words_amount == '0'){
				$words_amount = false;
			}
			$htmlmanip    = new RokSprocket_Util_HTMLManipulator();
			$preview      = $htmlmanip->truncateHTML($desc, $words_amount);
			$append       = strlen($desc) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);

			// resizing images if needed
			if ($item->getPrimaryImage()) {
				if ($this->parameters->get('lists_resize_enable', false)) {
					$width  = $this->parameters->get('lists_resize_width', 0);
					$height = $this->parameters->get('lists_resize_height', 0);
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
		$items          = $this->items->slice(0, $this->parameters->get('lists_items_per_page', 5));

		if (!$this->items->count() && !count($items)) $pages = 0;
		else $pages          = ceil($this->items->count() / count($items));

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
		$filename = ($this->theme == 'default' ? 'lists' : $this->theme);
		RokCommon_Header::addStyle($this->theme_context->getUrl($filename . '.css'));
		RokCommon_Header::addScript($this->theme_context->getUrl($filename . '.js'));

		$id                  = $this->parameters->get('module_id');
		$settings            = new stdClass();
		$settings->accordion = $this->parameters->get('lists_enable_accordion', 1);
		$settings->autoplay  = $this->parameters->get('lists_autoplay', 0);
		$settings->delay     = $this->parameters->get('lists_autoplay_delay', 5);
		$options             = json_encode($settings);
		if ($settings->accordion){
	        if(defined('_JEXEC')){
	            JHtml::_('behavior.framework', true);
	        }
		}
		$js   = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	RokSprocket.instances.lists.attach(" . $id . ", '" . $options . "');";
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
			RokCommon_Header::addScript($root_assets->getUrl('roksprocket.request.js'));
			RokCommon_Header::addScript($layout_assets->getUrl('lists.js'));

			$instance   = array();
			$instance[] = "window.addEvent('domready', function(){";
			$instance[] = "		RokSprocket.instances.lists = new RokSprocket.Lists();";
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
