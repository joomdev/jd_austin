<?php
/**
 * @version   $Id: RokSprocket_Layout_Headlines.php 28636 2015-07-09 15:40:49Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Layout_Headlines extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;
	/**
	 * @var string
	 */
	protected $name = 'headlines';


	/**
	 *
	 */
	protected function cleanItemParams()
	{
		foreach ($this->items as $item_id => &$item) {

			$item->setPrimaryImage($this->setupImage($item, 'headlines_image_default', 'headlines_image_default_custom', 'headlines_item_image'));
			$item->setPrimaryLink($this->setupLink($item, 'headlines_link_default', 'headlines_link_default_custom', 'headlines_item_link'));
			$item->setText($this->setupText($item, 'headlines_description_default', 'headlines_description_default_custom', 'headlines_item_description'));

			// clean from tags and limit words amount
			$words_amount = $this->parameters->get('headlines_previews_length', 20);
			if ($words_amount === '∞' || $words_amount == '0') {
				$words_amount = false;
			}

			$item_text = $item->getText();
			if ($this->parameters->get('headlines_strip_html_tags', true)) {
				$item_text = strip_tags($item_text);
			}
			$preview  = $this->_getWords($item_text, $words_amount);
			$append   = strlen($item_text) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);

			// resizing images if needed
			if ($item->getPrimaryImage()) {
				if ($this->parameters->get('headlines_resize_enable', false)) {
					$width  = $this->parameters->get('headlines_resize_width', 0);
					$height = $this->parameters->get('headlines_resize_height', 0);
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
		RokCommon_Header::addScript($this->theme_context->getUrl('headlines.js'));
		RokCommon_Header::addStyle($this->theme_context->getUrl('headlines.css'));

		$id                  = $this->parameters->get('module_id');
		$settings            = new stdClass();
		$settings->accordion = $this->parameters->get('headlines_enable_accordion', 1);
		$settings->autoplay  = $this->parameters->get('headlines_autoplay', 0);
		$settings->delay     = $this->parameters->get('headlines_autoplay_delay', 5);
		$options             = json_encode($settings);

		$js   = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	RokSprocket.instances.headlines.attach(" . $id . ", '" . $options . "');";
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
			$instance[] = "		RokSprocket.instances.headlines = new RokSprocket.Headlines();";
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
