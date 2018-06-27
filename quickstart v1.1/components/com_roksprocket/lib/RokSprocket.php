<?php
/**
 * @version   $Id: RokSprocket.php 19576 2014-03-10 20:32:35Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket
{
	/**
	 * @var bool
	 */
	protected static $globalHeadersRendered = false;

	/** @var \RokCommon_Registry */
	protected $params;

	/**
	 * @var \RokCommon_Service_Container
	 */
	protected $container;
	/**
	 * @var \RokCommon_Logger
	 */
	protected $logger;

	/**
	 *
	 */
	const BASE_PACKAGE_NAME = 'roksprocket_base';

	/**
	 * @var
	 */
	protected $context_base;

	/**
	 * @param RokCommon_Registry $params
	 */
	public function __construct(RokCommon_Registry $params)
	{
		$this->params = $params;
		/** @var $container RokCommon_Service_Container */
		$this->container = RokCommon_Service::getContainer();
		/** @var $logger RokCommon_Logger */
		$this->logger = $this->container->roksprocket_logger;
	}

	/**
	 * @return RokSprocket_ItemCollection
	 */
	public function getData()
	{
		$items = self::getItemsWithParams($this->params->get('module_id'), $this->params, true);
		$limit = $this->params->get('display_limit', '∞');
		if ($limit != '∞' && (int)$limit > 0) {
			$items = $items->trim($limit);
		}
		return $items;
	}

	/**
	 *
	 * @param \RokSprocket_ItemCollection $items
	 *
	 * @return string the html to be rendered
	 */
	public function render(RokSprocket_ItemCollection $items)
	{


		// get the layout
		$layout_name = $this->params->get('layout');

		$layout_service = $this->container[sprintf('roksprocket.layouts.%s.service', $layout_name)];
		// add the layout classpath
		$layout_lib_path = $this->container[sprintf('roksprocket.layouts.%s.library.paths', $layout_name)];
		foreach ($layout_lib_path as $lib_path) {
			RokCommon_ClassLoader::addPath($lib_path);

		}
		/** @var $i18n RokCommon_I18N */
		$layout_lang_paths = $this->container[sprintf('roksprocket.layouts.%s.paths', $layout_name)];
		foreach ($layout_lang_paths as $lang_path) {
			if (defined('ABS_PATH')) {
				rs_load_plugin_textdomain('wp_roksprocket_layout_' . $layout_name, $lang_path . '/language');
				$i18n->addDomain('wp_roksprocket_layout_' . $layout_name);
			}
		}

		/** @var $layout RokSprocket_Layout */
		$layout = $this->container->$layout_service;

		$layout->initialize($items, $this->params);

		$this->renderGlobalHeaders();
		$layout->renderLayoutHeaders();
		$layout->renderInstanceHeaders();

		ob_start();
		echo $layout->renderBody();
		return ob_get_clean();
	}

	/**
	 * @param \RokCommon_Registry $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * @return \RokCommon_Registry
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @param \RokCommon_Logger $logger
	 */
	public function setLogger($logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return \RokCommon_Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * @param \RokCommon_Service_Container $container
	 */
	public function setContainer($container)
	{
		$this->container = $container;
	}

	/**
	 * @return \RokCommon_Service_Container
	 */
	public function getContainer()
	{
		return $this->container;
	}


	/**
	 * @static
	 *
	 */
	public static function registerPaths()
	{

	}

	/**
	 * @param null $ajax_path
	 */
	public function renderGlobalHeaders($ajax_path = null)
	{
		if (!self::$globalHeadersRendered) {
			if(defined('_JEXEC')){ JHtml::_('behavior.framework'); }
			RokCommon_Header::addScript(RokCommon_Composite::get($this->context_base . '.assets.js')->getUrl('mootools-mobile.js'));
			RokCommon_Header::addScript(RokCommon_Composite::get($this->context_base . '.assets.js')->getUrl('rokmediaqueries.js'));
			RokCommon_Header::addScript(RokCommon_Composite::get($this->context_base . '.assets.js')->getUrl('roksprocket.js'));

			/** @var $platforminfo RokCommon_IPlatformInfo */
			$platforminfo = $this->container->getService('platforminfo');

			$ns   = array();
			$ns[] = "if (typeof RokSprocket == 'undefined') RokSprocket = {};";
			$ns[] = "Object.merge(RokSprocket, {";
			$ns[] = "	SiteURL: '" . str_replace('&', '&amp;', $platforminfo->getSEOUrl($platforminfo->getRootUrl(), true)) . "',";
			$ns[] = "	CurrentURL: '" . str_replace('&', '&amp;', $platforminfo->getSEOUrl($platforminfo->getRootUrl(), true)) . "',";
			$ns[] = "	AjaxURL: '" . str_replace('&', '&amp;', $platforminfo->getSEOUrl($platforminfo->getRootUrl() . $ajax_path)) . "'";
			$ns[] = "});";

			RokCommon_Header::addInlineScript(implode("\n", $ns) . "\n");

			self::$globalHeadersRendered = true;
		}
	}

	/**
	 * @static
	 *
	 * @param                    $moduleId
	 * @param RokCommon_Registry $parameters
	 * @param bool               $apply_random
	 *
	 * @param bool               $unpublished
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public static function getItemsWithParams($moduleId, RokCommon_Registry $parameters, $apply_random = true, $unpublished = false, $displayedIds = array())
	{
		$container = RokCommon_Service::getContainer();

		$provider_type = $parameters->get('provider', 'joomla');

		/** @var $provider RokSprocket_IProvider */
		$provider_service = $container['roksprocket.providers.registered.' . $provider_type . '.service'];
		$provider         = $container->$provider_service;
		$container->setParameter('roksprocket.current_provider', $provider);
		$provider->setParams($parameters);

		$provider_filters  = $parameters->get($provider_type . '_filters', array());
		$provider_articles = $parameters->get($provider_type . '_articles', array());

		$sort_type    = $parameters->get($provider_type . '_sort', 'automatic');
		$sort_append  = $parameters->get(sprintf('%s_sort_%s_append', $provider_type, $sort_type));
		$sort_filters = RokCommon_Utils_ArrayHelper::fromObject($parameters->get(sprintf('%s_sort_%s_filters', $provider_type, $sort_type), array()));

		$filters = array();
		if (!empty($provider_filters)) {
			$filters = array_merge($filters, RokCommon_Utils_ArrayHelper::fromObject($provider_filters));
		}
		if (!empty($provider_articles)) {
			$filters = array_merge($filters, RokCommon_Utils_ArrayHelper::fromObject($provider_articles));
		}

		return self::getItems($provider, $moduleId, $filters, $sort_filters, $sort_type, $sort_append, $apply_random, $unpublished, $displayedIds);
	}

	/**
	 * @static
	 *
	 * @param string             $moduleId
	 * @param string             $provider_type
	 * @param array              $provider_filters
	 * @param array              $provider_articles
	 * @param array              $sort_filters
	 * @param string             $sort_type
	 * @param string             $sort_append
	 * @param RokCommon_Registry $extra_parameters
	 * @param bool               $apply_random
	 *
	 * @param bool               $unpublished
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public static function getItemsWithFilters($moduleId, $provider_type, $provider_filters, $provider_articles, $sort_filters, $sort_type, $sort_append, &$extra_parameters, $apply_random = false, $unpublished = false)
	{
		$container = RokCommon_Service::getContainer();

		/** @var $provider RokSprocket_IProvider */
		$provider_service = $container['roksprocket.providers.registered.' . $provider_type . '.service'];
		$provider         = $container->$provider_service;

		$container->setParameter('roksprocket.current_provider', $provider);

		$provider->setParams($extra_parameters);

		$filters = array();
		if (!empty($provider_filters)) {
			$filters = array_merge($filters, RokCommon_Utils_ArrayHelper::fromObject($provider_filters));
		}
		if (!empty($provider_articles)) {
			$filters = array_merge($filters, RokCommon_Utils_ArrayHelper::fromObject($provider_articles));
		}

		return self::getItems($provider, $moduleId, $filters, $sort_filters, $sort_type, $sort_append, $apply_random, $unpublished);
	}

	/**
	 * @static
	 *
	 * @param RokSprocket_IProvider $provider
	 * @param                       $moduleId
	 * @param                       $filters
	 * @param                       $sort_filters
	 * @param                       $sort_type
	 * @param                       $sort_append
	 * @param                       $apply_random
	 *
	 * @param bool                  $unpublished
	 *
	 * @return RokSprocket_ItemCollection
	 */
	protected static function getItems(RokSprocket_IProvider &$provider, $moduleId, $filters, $sort_filters, $sort_type, $sort_append, $apply_random, $unpublished = false, $displayedIds = array())
	{
		$provider->setModuleId($moduleId);
		$provider->setDisplayedIds($displayedIds);
		$provider->setShowUnpublished($unpublished);
		$provider->setFilterChoices($filters, $sort_filters);
		$items = $provider->getItems();

		if (!empty($items)) {
			$sort_options                = array();
			$sort_options['append']      = $sort_append;
			$sort_options['applyrandom'] = $apply_random;
			$items->sort($sort_type, $sort_options);
		}

		return $items;
	}

}
