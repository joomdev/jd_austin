<?php
/**
 * @version   $Id: Articles.php 30359 2016-07-01 09:07:33Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocketAdminAjaxModelArticles extends RokCommon_Ajax_AbstractModel
{
	/**
	 * Get the items for the module and provider based on the filters passed and paginated  adding a new item first
	 * $params object should be a json like
	 * <code>
	 * {
	 *  "page": 3,
	 *  "items_per_page":6,
	 *  "module_id": 5,
	 *  "provider":"joomla",
	 *  "filters":  {"1":{"root":{"access":"1"}},"2":{"root":{"author":"43"}}},
	 *  "sortby":"date",
	 *  "get_remaining": true
	 * }
	 * </code>
	 *
	 * @param $params
	 *
	 * @throws Exception
	 * @throws RokCommon_Ajax_Exception
	 * @return \RokCommon_Ajax_Result
	 */
	public function getItemsWithNew($params)
	{
		$container = RokCommon_Service::getContainer();
		// add a new item
		$provider_class = $container->getParameter(sprintf('roksprocket.providers.registered.%s.class', strtolower($params->provider)));
		if ($params->uuid != '0')
		{
			$params->module_id = $params->uuid;
		}
		call_user_func_array(array($provider_class, 'addNewItem'), array($params->module_id));
		return $this->getItems($params);
	}

	/**
	 * Get the items for the module and provider based on the filters passed and paginated
	 * $params object should be a json like
	 * <code>
	 * {
	 *  "page": 3,
	 *  "items_per_page":6,
	 *  "module_id": 5,
	 *  "provider":"joomla",
	 *  "filters":  {"1":{"root":{"access":"1"}},"2":{"root":{"author":"43"}}},
	 *  "sortby":"date",
	 *  "get_remaining": true
	 * }
	 * </code>
	 *
	 * @param $params
	 *
	 * @throws Exception
	 * @throws RokCommon_Ajax_Exception
	 * @return \RokCommon_Ajax_Result
	 */
	public function getItems($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$html              = '';
			$provider_filters  = null;
			$provider_articles = null;

			if (isset($params->filters)) {
				$provider_filters = RokCommon_JSON::decode($params->filters);
			}
			if (isset($params->articles)) {
				$provider_articles = RokCommon_JSON::decode($params->articles);
			}

			$decoded_sort_parameters = array();
			try {
				$decoded_sort_parameters = RokCommon_Utils_ArrayHelper::fromObject(RokCommon_JSON::decode($params->sort));
			} catch (RokCommon_JSON_Exception $jse) {
				throw new RokCommon_Ajax_Exception('Invalid Sort Parameters passed in.');
			}
			$sort_params  = new RokCommon_Registry($decoded_sort_parameters);
			$sort_filters = RokCommon_Utils_ArrayHelper::fromObject($sort_params->get('rules'));
			$sort_append  = $sort_params->get('append', 'after');
			$sort_type    = $sort_params->get('type');

			$extras = array();
			if (isset($params->extras)) {
				$extras = $params->extras;
			}
			if ($params->uuid != '0')
			{
				$params->module_id = $params->uuid;
			}
			$items = RokSprocket::getItemsWithFilters($params->module_id, $params->provider, $provider_filters, $provider_articles, $sort_filters, $sort_type, $sort_append, new RokCommon_Registry($extras), false, true);

			$container           = RokCommon_Service::getContainer();
			$template_path_param = sprintf('roksprocket.providers.registered.%s.templatepath', strtolower($params->provider));
			if ($container->hasParameter($template_path_param)) {
				RokCommon_Composite::addPackagePath('roksprocket', $container->getParameter($template_path_param), 30);
			}
			$total_items_count = $items->count();
			$page              = $params->page;
			$more              = false;
			$limit             = 10;

			$offset = ($page - 1) * $limit;
			if ($params->load_all) {
				$limit = $total_items_count - $offset;
			}
			$items     = $items->slice($offset, $limit);
			$page      = ((int)$page == 0) ? 1 : $page;
			$next_page = $page;
			if ($total_items_count > $offset + $limit) {
				$more      = true;
				$next_page = $page + 1;
			}
			$order = 0;

			$this->loadLayoutLanguage($params->layout);
			ob_start();
			foreach ($items as $article):
				$per_item_form = $this->getPerItemsForm($params->layout);
				$per_item_form->setFormControl(sprintf('items[%s]', $article->getArticleId()));
				$per_item_form->bind(array('params' => $article->getParams()));
				echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_article.php', array(
				                                                                                        'itemform' => $per_item_form,
				                                                                                        'article'  => $article,
				                                                                                        'order'    => $order
				                                                                                   ));
				$order++;
			endforeach;
			$html .= ob_get_clean();


			$result->setPayload(array(
			                         'more'      => $more,
			                         'page'      => $page,
			                         'next_page' => $next_page,
			                         'amount'    => $total_items_count,
			                         'html'      => $html
			                    ));
		} catch (Exception $e) {

			throw $e;
		}
		return $result;
	}

	protected function loadLayoutLanguage($layout)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $i18n RokCommon_I18N */
		$i18n              = $container->i18n;
		$layout_lang_paths = $container[sprintf('roksprocket.layouts.%s.paths', $layout)];
		foreach ($layout_lang_paths as $lang_path) {
			@$i18n->loadLanguageFiles('roksprocket_layout_' . $layout, $lang_path);
		}
	}

	/**
	 * @param $type
	 *
	 * @return RokCommon_Config_Form
	 */
	protected function getPerItemsForm($type)
	{
		JForm::addFieldPath(JPATH_SITE . '/components/com_roksprocket/fields');
		$options   = new RokCommon_Options();
		$container = RokCommon_Service::getContainer();
		// load up the layouts
		$layoutinfo = $container['roksprocket.layouts.' . $type];
		if (isset($layoutinfo->options->peritem)) {
			$section = new RokCommon_Options_Section('peritem_' . $type, $layoutinfo->options->peritem);
			foreach ($layoutinfo->paths as $layoutpath) {
				$section->addPath($layoutpath);
			}
			$options->addSection($section);
		}

		$form   = new JForm('roksprocket_peritem');
		$rcform = new RokCommon_Config_Form($form);
		$xml    = $options->getJoinedXml();

		$version = new JVersion();
		if (version_compare($version->getShortVersion(), '3.0', '>=')) {
			$jxml = new SimpleXMLElement($xml->asXML());
		} elseif (version_compare($version->getShortVersion(), '3.0', '<')) {
			$jxml = new JXMLElement($xml->asXML());
		}

		$fieldsets = $jxml->xpath('/config/fields[@name = "params"]/fieldset');
		foreach ($fieldsets as $fieldset) {
			$overwrite = ((string)$fieldset['overwrite'] == 'true') ? true : false;
			$rcform->load($fieldset, $overwrite, '/config');
		}
		return $rcform;
	}

	/**
	 * Remove an item
	 * $params object should be a json like
	 * <code>
	 * {
	 *  "module_id": 5,
	 *  "provider":"simple",
	 *  "item_id":  123,
	 * }
	 * </code>
	 *
	 * @param $params
	 *
	 * @throws Exception
	 * @throws RokCommon_Ajax_Exception
	 * @return \RokCommon_Ajax_Result
	 */
	public function removeItem($params)
	{
		$result = new RokCommon_Ajax_Result();

		//get the provider and id values

		list($provider, $item_id) = explode('-',$params->item_id);
		if ($params->uuid != '0')
		{
			$params->module_id = $params->uuid;
		}
		// get the provider
		$container      = RokCommon_Service::getContainer();
		$provider_class = $container->getParameter(sprintf('roksprocket.providers.registered.%s.class', strtolower($provider)));
		// have the provider remove the item
		if (call_user_func_array(array($provider_class, 'removeItem'), array($item_id, $params->module_id)))
		{
			$result->setPayload(RokCommon_JSON::encode(array('removed_item'=>$params->item_id)));
		}
		return $this->getItems($params);
	}

	/**
	 * Returns the informations related to an article
	 * $params object should be a json like
	 * <code>
	 * {
	 *  "id":"joomla-71"
	 * }
	 * </code>
	 *
	 * @param $params
	 *
	 * @throws Exception
	 * @return RokCommon_Ajax_Result
	 */
	public function getInfo($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$html = '';

			list($provider_type, $id) = explode('-', $params->id);

			$container = RokCommon_Service::getContainer();
			//$provider_type = $params->provider;

			/** @var $provider RokSprocket_IProvider */
			$provider_service = $container['roksprocket.providers.registered.' . $provider_type . '.service'];
			$provider         = $container->$provider_service;


			$article = $provider->getArticleInfo($id, true);

			ob_start();
			echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_article_info_' . $provider_type . '.php', array('article' => $article));
			$html .= ob_get_clean();

			$result->setPayload(array('html' => $html));
		} catch (Exception $e) {
			throw $e;
		}
		return $result;
	}

	/**
	 * Returns the preview of an article
	 * $params object should be a json like
	 * <code>
	 * {
	 *  "id":"joomla-71"
	 * }
	 * </code>
	 *
	 * @param $params
	 *
	 * @throws Exception
	 * @return \RokCommon_Ajax_Result
	 */
	public function getPreview($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$html = '';

			list($provider_type, $id) = explode('-', $params->id);

			$container = RokCommon_Service::getContainer();
			//$provider_type = $params->provider;

			/** @var $provider RokSprocket_IProvider */
			$provider_service = $container['roksprocket.providers.registered.' . $provider_type . '.service'];
			$provider         = $container->$provider_service;

			if (isset($params->extras)) {
				$extras = new RokCommon_Registry($params->extras);
				$provider->setParams($extras);
			}
			$article = $provider->getArticlePreview($id);

			ob_start();
			echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_article_preview.php', array('article' => $article));
			$html .= ob_get_clean();

			$result->setPayload(array('html' => $html));
		} catch (Exception $e) {
			throw $e;
		}
		return $result;
	}
}
