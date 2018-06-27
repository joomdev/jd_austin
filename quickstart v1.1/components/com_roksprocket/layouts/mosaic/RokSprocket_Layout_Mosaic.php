<?php

/**
 * @version   $Id: RokSprocket_Layout_Mosaic.php 28636 2015-07-09 15:40:49Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokSprocket_Layout_Mosaic extends RokSprocket_AbstractLayout
{
	/**
	 * @var bool
	 */
	protected static $instanceHeadersRendered = false;

	/**
	 * @var string
	 */
	protected $name = 'mosaic';
	/**
	 * @var array
	 */
	protected $tagging = array("all" => "");

	protected $displayed = array();


	/**
	 *
	 */
	protected function cleanItemParams()
	{


		foreach ($this->items as $item_id => &$item) {

			$item->setPrimaryImage($this->setupImage($item, 'mosaic_image_default', 'mosaic_image_default_custom', 'mosaic_item_image'));
			$item->setPrimaryLink($this->setupLink($item, 'mosaic_link_default', 'mosaic_link_default_custom', 'mosaic_item_link'));
			$item->setTitle($this->setupText($item, 'mosaic_title_default', 'mosaic_title_default_custom', 'mosaic_item_title'));
			$item->setText($this->setupText($item, 'mosaic_description_default', 'mosaic_description_default_custom', 'mosaic_item_description'));
			$item->setTags($this->filterTags($this->setupTags($item, 'mosaic_item_tags')));


			// clean from tags and limit words amount
			$desc = $item->getText();
			if ($this->parameters->get('mosaic_strip_html_tags', true)) {
				$desc = strip_tags($desc);
			}
			$words_amount = $this->parameters->get('mosaic_previews_length', 20);
			if ($words_amount === '∞' || $words_amount == '0') {
				$words_amount = false;
			}
			$htmlmanip = new RokSprocket_Util_HTMLManipulator();
			$preview   = $htmlmanip->truncateHTML($desc, $words_amount);
			$append    = strlen($desc) != strlen($preview) ? '<span class="roksprocket-ellipsis">…</span>' : "";
			$item->setText($preview . $append);

			// resizing images if needed
			if ($item->getPrimaryImage()) {
				if ($this->parameters->get('mosaic_resize_enable', false)) {
					$width  = $this->parameters->get('mosaic_resize_width', 0);
					$height = $this->parameters->get('mosaic_resize_height', 0);
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

			// ordering
			$item->custom_ordering_items = '<div style="display: none;" data-mosaic-order-title="' . $item->getTitle() . '" data-mosaic-order-date="' . $item->getDate() . '"></div>';

			// tagging
			$item->custom_tags      = "";
			$item->custom_tags_list = array();
			foreach ($this->filterTags($item->getTags()) as $key => $name) {
				$item->custom_tags .= " sprocket-tags-" . $key;
				$item->custom_tags_list[$key] = $name;

				if (!array_key_exists($key, $this->tagging)) {
					$this->tagging[$key] = $name;
				}
			}
			$item->custom_tags = trim($item->custom_tags);
			natcasesort($item->custom_tags_list);

		}
		// sort the tags for display
		natcasesort($this->tagging);
		// add the all
		$this->tagging = array('all' => rc__('ALL')) + $this->tagging;
	}

	/**
	 * @param $tags
	 *
	 * @return array
	 */
	protected function cleanupTags($tags)
	{
		$outtags = array();
		if (is_array($tags)) {
            foreach ($tags as $tag) {
                $cleanName     = trim($tag);
                $key           = str_replace(' ', '-', str_replace(array("'", '"'), '', $cleanName));
                $name          = $this->_camelize($cleanName, true, true);
                $outtags[$key] = $name;
            }
        }
		return $outtags;
	}

	/**
	 * @param $tags
	 *
	 * @return string[]
	 */
	protected function filterTags($tags)
	{
		if ($this->parameters->get('mosaic_filter_tags', false) !== false) {
			$filtered_taglist = $this->parameters->get('mosaic_filter_tags', '');
			if (!empty($filtered_taglist)) {
				$filtered_taglist = explode(",", $filtered_taglist);
				$filtered_taglist = $this->cleanupTags($filtered_taglist);
				$tags = array_intersect($tags, $filtered_taglist);
			}
		}
		return $tags;
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
	$items          = $this->items->slice(0, $this->parameters->get('mosaic_items_per_page', 5));

	if (!$this->items->count() && !count($items)) $pages = 0; else $pages = ceil($this->items->count() / count($items));

	// ordering
	$ordering = $this->parameters->get('mosaic_ordering');

	// tagging
	$tagging = $this->tagging;

	// article details
	$this->parameters->set('mosaic_article_details', $this->parameters->get('mosaic_article_details', false));

	return $this->theme_context->load($theme_basefile, array(
		'layout'     => $this,
		'items'      => $items,
		'pages'      => $pages,
		'ordering'   => $ordering,
		'tagging'    => $tagging,
		'parameters' => $this->parameters
	));
}

/**
 * Called to render headers that should be included on a per module instance basis
 */
public
function renderInstanceHeaders()
{
	$filename = ($this->theme == 'default' ? 'mosaic' : $this->theme);
	RokCommon_Header::addStyle($this->theme_context->getUrl($filename . '.css'));
	RokCommon_Header::addScript($this->theme_context->getUrl($filename . '.js'));

	$items = $this->items->slice(0, $this->parameters->get('mosaic_items_per_page', 5));

	if (!$this->items->count() && !count($items)) $pages = 0; else $pages = ceil($this->items->count() / count($items));

	$id                   = $this->parameters->get('module_id');
	$settings             = new stdClass();
	$settings->pages      = $pages;
	$settings->animations = explode(',', implode(',', (array)$this->parameters->get('mosaic_animations', array(
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
	$js[] = "	RokSprocket.instances.mosaic.attach(" . $id . ", '" . $options . "');";
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
			$js[] = "   RokSprocket.instances.mosaic.mosaic['id-" . $id . "'].reload();";
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
		$layout_assets = RokCommon_Composite::get($this->layoutPackage . '.assets.js');
		RokCommon_Header::addScript($root_assets->getUrl('moofx.js'));
		RokCommon_Header::addScript($root_assets->getUrl('roksprocket.request.js'));
		RokCommon_Header::addScript($layout_assets->getUrl('mosaic.js'));

		$instance   = array();
		$instance[] = "window.addEvent('domready', function(){";
		$instance[] = "		RokSprocket.instances.mosaic = new RokSprocket.Mosaic();";
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

/**
 * @param RokSprocket_Item $item
 * @param bool             $per_item_field
 *
 * @return bool|null|RokSprocket_Item_Image
 */
protected
function setupTags(RokSprocket_Item &$item, $per_item_field = false)
{
	if (!$per_item_field) {
		$tags = implode(',', $item->getTags());
	} else {
		switch (trim($item->getParam($per_item_field, '-article-'))) {
			case '-none-':
				$tags = array();
				break;
			case '-article-':
				$tags = $item->getTags();
				break;
			default:
				$custom_tags = $item->getParam($per_item_field, false);
				$tags        = (!empty($custom_tags) && $custom_tags !== false) ? explode(',', $custom_tags) : array();
		}
	}
	return $this->cleanupTags($tags);
}
}
