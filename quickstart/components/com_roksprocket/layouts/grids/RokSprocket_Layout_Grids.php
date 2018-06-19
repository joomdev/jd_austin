<?php

/**
 * @version   $Id: RokSprocket_Layout_Grids.php 20743 2014-04-30 16:38:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokSprocket_Layout_Grids extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;

	/**
	 * @var string
	 */
	protected $name = 'grids';
	/**
	 * @var array
	 */

	protected $displayed = array();


	/**
	 *
	 */
	protected function cleanItemParams()
	{


		foreach ($this->items as $item_id => &$item) {

			$item->setPrimaryImage($this->setupImage($item, 'grids_image_default', 'grids_image_default_custom', 'grids_item_image'));
			$item->setPrimaryLink($this->setupLink($item, 'grids_link_default', 'grids_link_default_custom', 'grids_item_link'));
			$item->setTitle($this->setupText($item, 'grids_title_default', 'grids_title_default_custom', 'grids_item_title'));
			$item->setText($this->setupText($item, 'grids_description_default', 'grids_description_default_custom', 'grids_item_description'));


			// clean from tags and limit words amount
			$desc = $item->getText();
			if ($this->parameters->get('grids_strip_html_tags', true)) {
				$desc = strip_tags($desc);
			}
			$words_amount = $this->parameters->get('grids_previews_length', 20);
			if ($words_amount === '∞' || $words_amount == '0') {
				$words_amount = false;
			}
			$htmlmanip = new RokSprocket_Util_HTMLManipulator();
			$preview   = $htmlmanip->truncateHTML($desc, $words_amount);
			$append    = strlen($desc) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);

			// resizing images if needed
			if ($item->getPrimaryImage()) {
				if ($this->parameters->get('grids_resize_enable', false)) {
					$width  = $this->parameters->get('grids_resize_width', 0);
					$height = $this->parameters->get('grids_resize_height', 0);
					$item->getPrimaryImage()->resize($width, $height);
				}
				/** @var RokCommon_PlatformInfo $platforminfo */
				$platforminfo = $this->container->platforminfo;
				$urlbase      = ($platforminfo->getUrlBase()) ? $platforminfo->getUrlBase() : '/';
				if (!$platforminfo->isLinkexternal($item->getPrimaryImage()->getSource()) && strpos($item->getPrimaryImage()->getSource(), '/') !== 0 && strpos($item->getPrimaryImage()->getSource(), $urlbase) !== 0) {
					$source = rtrim($urlbase, '/') . '/' . $item->getPrimaryImage()->getSource();
					$item->getPrimaryImage()->setSource($source);
				}
			}

		}
	}

/**
 * @return RokSprocket_ItemCollection
 */
public
function getItems()
{
	return $this->items;
}


/**
 * @return RokCommon_Composite_Context
 */
public
function getThemeContent()
{
	return $this->theme_context;
}

/**
 * @return bool|string
 */
public
function renderBody()
{

	$theme_basefile = $this->container[sprintf('roksprocket.layouts.%s.themes.%s.basefile', $this->name, $this->theme)];
	$items          = $this->items;

	// article details
	$this->parameters->set('grids_article_details', $this->parameters->get('grids_article_details', false));

	return $this->theme_context->load($theme_basefile, array(
		'layout'     => $this,
		'items'      => $items,
		'parameters' => $this->parameters
	));
}

/**
 * Called to render headers that should be included on a per module instance basis
 */
public
function renderInstanceHeaders()
{
	RokCommon_Header::addStyle($this->theme_context->getUrl($this->theme . '.css'));
	RokCommon_Header::addScript($this->theme_context->getUrl($this->theme . '.js'));

	$items = $this->items;

	$id                   = $this->parameters->get('module_id');
	$settings             = new stdClass();
	$settings->animations = explode(',', implode(',', (array)$this->parameters->get('grids_animations', array(
		'fade',
		'scale',
		'rotate'
	))));

	$settings->displayed = array();

	foreach ($items as $item_id => &$item) {
		array_push($settings->displayed, (int)$item->getId());
	}

	array_unique($settings->displayed);

	$options = json_encode($settings);

	if (defined('_JEXEC')) {
		JHtml::_('behavior.framework', true);
	}

	$js   = array();
	$js[] = "window.addEvent('domready', function(){";
	$js[] = "	RokSprocket.instances.grids.attach(" . $id . ", '" . $options . "');";
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
	/*		$js[] = "window.addEvent('load', function(){";
			$js[] = "   RokSprocket.instances.grids.grids['id-" . $id . "'].reload();";
			$js[] = "});";*/
	RokCommon_Header::addInlineScript(implode("\n", $js) . "\n");
}

/**
 * Called to render headers that should be included only once per Layout type used
 */
public
function renderLayoutHeaders()
{
	if (!self::$instanceHeadersRendered) {

		$root_assets = RokCommon_Composite::get($this->basePackage . '.assets.js');
		RokCommon_Header::addScript($root_assets->getUrl('moofx.js'));
		RokCommon_Header::addScript($root_assets->getUrl('roksprocket.request.js'));

		$instance   = array();
		$instance[] = "window.addEvent('domready', function(){";
		$instance[] = "		RokSprocket.instances.grids = new RokSprocket.Grids();";
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
public
function _getWords($string, $amount = false)
{
	if (!$amount) $amount = strlen($string);
	$words = explode(' ', $string, ($amount + 1));
	if (count($words) > $amount) array_pop($words);

	return implode(' ', $words);
}

/**
 * @param      $string
 * @param bool $pascalCase
 * @param bool $keepSpaces
 *
 * @return mixed|string
 */
public
function _camelize($string, $pascalCase = false, $keepSpaces = false)
{
	$string = str_replace(array('-', '_'), ' ', $string);
	$string = ucwords($string);

	if (!$keepSpaces) $string = str_replace(' ', '', $string);
	if (!$pascalCase) {
		return lcfirst($string);
	}

	return $string;
}
}
