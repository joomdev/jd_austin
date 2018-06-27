<?php
/**
 * @version   $Id: AbstractLayout.php 13721 2013-09-24 16:46:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_AbstractLayout implements RokSprocket_Layout
{
	/**
	 * @var RokCommon_Dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;

	/**
	 * @var RokSprocket_Item[]
	 */
	protected $items;

	/**
	 * @var RokCommon_Registry
	 */
	protected $parameters;


	/**
	 * @var string
	 */
	protected $basePackage = RokSprocket::BASE_PACKAGE_NAME;

	/**
	 * @var string
	 */
	protected $layoutPackage;

	/**
	 * @var string
	 */
	protected $themePackage;

	/**
	 * @var RokCommon_Composite_Context
	 */
	protected $theme_context;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $theme;

	/**
	 * @param RokCommon_Dispatcher $dispatcher
	 */
	public function __construct(RokCommon_Dispatcher $dispatcher)
	{
		$this->container  = RokCommon_Service::getContainer();
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param RokSprocket_ItemCollection $items
	 * @param RokCommon_Registry         $parameters
	 */
	public function initialize(RokSprocket_ItemCollection $items, RokCommon_Registry $parameters)
	{
		$this->setItems($items);
		$this->setParameters($parameters);

		$this->layoutPackage = sprintf('roksprocket_layout_%s', $this->name);

		// setup the theme packages and content and info
		$this->theme        = $this->parameters->get($this->name . '_themes', 'default');
		$this->themePackage = sprintf('%s_%s', $this->layoutPackage, $this->theme);
		$paths = $this->container[sprintf('roksprocket.layouts.%s.paths', $this->name)];
		$paths[] = $this->container->platforminfo->getPathForTemplate($this->container->roksprocket_platformhelper->getCurrentTemplate()).'/'.$this->container->getParameter('roksprocket.template.subdir').'/layouts/'.$this->name;
		foreach ($paths as $order => $path) {
			RokCommon_Composite::addPackagePath($this->layoutPackage, $path, $order);
			RokCommon_Composite::addPackagePath($this->themePackage, $path . '/themes/' . $this->theme, $order);
		}
		$this->theme_context = RokCommon_Composite::get($this->themePackage);
		$this->cleanItemParams();
	}


	/**
	 * @abstract
	 *
	 */
	abstract protected function cleanItemParams();


	/**
	 * @param RokSprocket_ItemCollection $items
	 */
	public function setItems(RokSprocket_ItemCollection $items)
	{
		$this->items = $items;
	}

	/**
	 * @param \RokCommon_Registry $parameters
	 */
	public function setParameters(RokCommon_Registry $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getLayoutPackage()
	{
		return $this->layoutPackage;
	}

	/**
	 * @return string
	 */
	public function getThemePackage()
	{
		return $this->themePackage;
	}

	/**
	 * @return string
	 */
	public function getTheme()
	{
		return $this->theme;
	}

	/**
	 * @return \RokCommon_Composite_Context
	 */
	public function getThemeContext()
	{
		return $this->theme_context;
	}

	/**
	 * @return string
	 */
	public function getBasePackage()
	{
		return $this->basePackage;
	}


	/**
	 * @param RokSprocket_Item $item
	 * @param bool|string      $default_field
	 * @param bool|string      $defaults_custom_field
	 * @param bool|string      $per_item_field
	 *
	 * @return bool|null|RokSprocket_Item_Link
	 */
	protected function setupLink(RokSprocket_Item &$item, $default_field = false, $defaults_custom_field = false, $per_item_field = false)
	{
		$link = false;
		$deflink = false;
		if (!$default_field) {
			$deflink = false;
		} else {
			$default_field_value = str_replace($item->getProvider().'_','',$this->parameters->get($default_field, 'primary'));
			switch ($default_field_value) {
				case 'none':
					$deflink = false;
					break;
				case 'primary':
					$deflink = $item->getPrimaryLink();
					break;
				case 'custom':
					if ($defaults_custom_field) {
						$deflink = ($this->parameters->get($defaults_custom_field, false)) ? new RokSprocket_Item_Link(htmlspecialchars(htmlspecialchars_decode($this->parameters->get($defaults_custom_field)))) : false;
						break;
					}
				default:
					$deflink = $item->getLink($default_field_value);
			}
		}
		if (!$per_item_field) {
			$link = $deflink;
		} else {
			switch (trim($item->getParam($per_item_field, '-default-'))) {
				case '-none-':
					$link = false;
					break;
				case '-article-':
					$link = $item->getPrimaryLink();
					break;
				case '-default-':
					$link = $deflink;
					break;
				default:
					$link = $item->getParam($per_item_field, '');
					if (!empty($link)){
						$link =  new RokSprocket_Item_Link(htmlspecialchars(htmlspecialchars_decode($item->getParam($per_item_field,''))));
					} else {
						$link = $deflink;
					}

			}
		}
		return $link;
	}


	/**
	 * @param RokSprocket_Item $item
	 * @param bool             $default_field
	 * @param bool             $defaults_custom_field
	 * @param bool             $per_item_field
	 *
	 * @return bool|null|RokSprocket_Item_Image
	 */
	protected function setupImage(RokSprocket_Item &$item, $default_field = false, $defaults_custom_field = false, $per_item_field = false)
	{
		$image = false;
		$defimage = false;
		if (!$default_field) {
			$defimage = false;
		} else {
			$default_field_value = str_replace($item->getProvider().'_','',$this->parameters->get($default_field, 'primary'));
			switch ($default_field_value) {
				case 'none':
					$defimage = false;
					break;
				case 'primary':
					$defimage = $item->getPrimaryImage();
					break;
				case 'custom':
					if ($defaults_custom_field) {
						$defimage = ($this->parameters->get($defaults_custom_field, false)) ? RokSprocket_Item_Image::createFromJSON($this->parameters->get($defaults_custom_field, false)) : false;
						break;
					}
				default:
					$defimage = $item->getImage($default_field_value);
			}
		}
		if (!$per_item_field) {
			$image = $defimage;
		} else {
			switch (trim($item->getParam($per_item_field, '-default-'))) {
				case '-none-':
					$image = false;
					break;
				case '-primary-': // backward compatibility for introduced issue
				case '-article-':
					$image = $item->getPrimaryImage();
					break;
				case '-default-':
					$image = $defimage;
					break;
				default:
					$image = $item->getParam($per_item_field);
					if (!empty($image)){
						$image = RokSprocket_Item_Image::createFromJSON($item->getParam($per_item_field,''));
					}
					else{
						$image = $defimage;
					}

			}
		}
		return $image;
	}

	/**
	 * @param RokSprocket_Item $item
	 * @param bool             $default_field
	 * @param bool             $defaults_custom_field
	 * @param bool             $per_item_field
	 *
	 * @return bool|null|RokSprocket_Item_Image
	 */
	protected function setupText(RokSprocket_Item &$item, $default_field = false, $defaults_custom_field = false, $per_item_field = false)
	{
		$text = false;
		$deftext = false;
		if (!$default_field) {
			$deftext = false;
		} else {
			$default_field_value = str_replace($item->getProvider().'_','',$this->parameters->get($default_field, 'primary'));
			switch ($default_field_value) {
				case 'none':
					$deftext = false;
					break;
				case 'title':
					$deftext = $item->getTitle();
					break;
				case 'primary':
					$deftext = $item->getText();
					break;
				case 'custom':
					if ($defaults_custom_field) {
						$deftext = ($this->parameters->get($defaults_custom_field, false)) ? $this->parameters->get($defaults_custom_field, false) : false;
						break;
					}
				default:
					$deftext = $item->getTextField($default_field_value);
			}
		}
		if (!$per_item_field) {
			$text = $deftext;
		} else {
			switch (trim($item->getParam($per_item_field, '-default-'))) {
				case '-none-':
					$text = false;
					break;
				case '-title-':
					$text = $item->getTitle();
					break;
				case '-article-':
					$text = $item->getText();
					break;
				case '-default-':
					$text = $deftext;
					break;
				default:
					$text = ($item->getParam($per_item_field, $deftext)) ? $item->getParam($per_item_field, $deftext) : $deftext;
			}
		}
		return $text;
	}
}
