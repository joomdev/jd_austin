<?php
/**
 * @version   $Id: AbstarctZooBasedProvider.php 30374 2016-08-05 09:46:18Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider_AbstarctZooBasedProvider extends RokSprocket_Provider_AbstarctJoomlaBasedProvider
{
	protected static $zoo_applications;
	protected static $text_types;
	protected static $image_types;
	protected static $link_types;

	/**
	 * @return array the array of image type and label
	 */
	public static function getImageTypes()
	{
		if (!isset(self::$image_types)) {
			require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

			$applications = self::getZooApplications();
			$list         = array();
			foreach ($applications as $application) {
				$types = $application->getTypes();
				foreach ($types as $type) {
					$elements = $type->getElements();
					foreach ($elements as $element) {
						$sprocket_type = RokSprocket_Provider_Zoo_FieldProcessorFactory::getSprocketType($element->getElementType());
						if ($sprocket_type == 'image') {
							$key                   = 'image_field_' . $element->identifier;
							$list[$key]            = array();
							$list[$key]['group']   = $application->id . '_' . $type->id;
							$list[$key]['display'] = $element->config->name;
						}
					}
				}
			}
			self::sortTypes($list);
			self::$image_types = $list;
		}
		return self::$image_types;
	}

	/**
	 * @return Application[]
	 */
	protected static function getZooApplications()
	{
		if (!isset(self::$zoo_applications)) {
			$app                    = App::getInstance('zoo');
			self::$zoo_applications = $app->application->getApplications();
		}
		return self::$zoo_applications;
	}

	protected static function sortTypes(&$list)
	{
		uasort($list, array('RokSprocket_Provider_AbstarctZooBasedProvider','_sortTypes'));
	}

	/**
	 * @return array the array of link types and label
	 */
	public static function getLinkTypes()
	{
		if (!isset(self::$link_types)) {
			require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

			$applications = self::getZooApplications();
			$list         = array();
			foreach ($applications as $application) {
				$types = $application->getTypes();
				foreach ($types as $type) {
					$elements = $type->getElements();
					foreach ($elements as $element) {
						$sprocket_type = RokSprocket_Provider_Zoo_FieldProcessorFactory::getSprocketType($element->getElementType());
						if ($sprocket_type == 'link') {
							$key                   = 'link_field_' . $element->identifier;
							$list[$key]            = array();
							$list[$key]['group']   = $application->id . '_' . $type->id;
							$list[$key]['display'] = $element->config->name;
						}
					}
				}
			}
			self::sortTypes($list);
			self::$link_types = $list;
		}
		return self::$link_types;
	}

	/**
	 * @return array the array of link types and label
	 */
	public static function getTextTypes()
	{
		if (!isset(self::$text_types)) {
			require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');
			$applications = self::getZooApplications();
			$list         = array();
			foreach ($applications as $application) {
				$types = $application->getTypes();
				foreach ($types as $type) {
					$elements = $type->getElements();
					foreach ($elements as $element) {
						$sprocket_type = RokSprocket_Provider_Zoo_FieldProcessorFactory::getSprocketType($element->getElementType());
						if ($sprocket_type == 'text') {
							$key                   = 'text_field_' . $element->identifier;
							$list[$key]            = array();
							$list[$key]['group']   = $application->id . '_' . $type->id;
							$list[$key]['display'] = $element->config->name;
						}
					}
				}
			}
			$static = array(
				'text_field_metadesc' => array('group' => null, 'display' => 'Meta Description Text'),
				'text_field_name'     => array('group' => null, 'display' => 'Item Name'),
			);
			$list   = array_merge($static, $list);
			self::sortTypes($list);
			self::$text_types = $list;
		}
		return self::$text_types;
	}

	/**
	 * @static
	 * @return array
	 */
	public static function getCCKGroups()
	{
		require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');
		$applications = self::getZooApplications();
		$list         = array();
		foreach ($applications as $application) {
			$types = $application->getTypes();
			foreach ($types as $type) {
				$list[$application->id . '_' . $type->id] = $application->name . ' - ' . $type->name;
			}
		}
		return $list;
	}

	public static function _sortTypes($a, $b)
	{
		//Convert to lowercase to ensure consistent behaviour
		$sortable = array(strtolower($a['display']), strtolower($b['display']));
		$sorted   = $sortable;
		sort($sorted);
		//If the names have switched position, return -1. Otherwise, return 1.
		return ($sorted[0] == $sortable[0]) ? -1 : 1;
	}

	/**
	 * @param      $id
	 *
	 * @param bool $raw return the raw object not the RokSprocket_Item
	 *
	 * @return stdClass|RokSprocket_Item
	 * @throws RokSprocket_Exception
	 */
	public function getArticleInfo($id, $raw = false)
	{
		/** @var $filer_processor RokCommon_Filter_IProcessor */
		$filer_processor = $this->getFilterProcessor();
		$filer_processor->process(array('id' => array($id)));
		$query = $filer_processor->getQuery();
		$db    = JFactory::getDbo();
		$db->setQuery($query);
		$ret = $db->loadObject();
		if ($error = $db->getErrorMsg()) {
			throw new RokSprocket_Exception($error);
		}
		if ($raw) {
			$ret->preview = $this->_cleanPreview($ret->articletext);
			$ret->editUrl = $this->getArticleEditUrl($id);
			return $ret;
		} else {
			$item          = $this->convertRawToItem($ret);
			$item->editUrl = $this->getArticleEditUrl($id);
			$item->preview = $this->_cleanPreview($item->getText());
			return $item;
		}
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	protected function getArticleEditUrl($id)
	{
		return JURI::root(true) . '/administrator/index.php?option=com_zoo&controller=item&changeapp=1&task=edit&cid[]=' . $id;
	}

	/**
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems()
	{
		if ($this->params->exists(strtolower($this->provider_name).'_application_type')) {
			$this->filters[strtolower($this->provider_name).'_application_type'][] = $this->params->get(strtolower($this->provider_name).'_application_type');
		}

		if (empty($this->filters)) return new RokSprocket_ItemCollection();


		// setup active menu item if not there
		// TODO: remove Joomla 2.5 fallback
		$site_app = class_exists('JApplicationCms') ? JApplicationCms::getInstance('site') : JApplication::getInstance('site');
		$lang     = JFactory::getLanguage();
		$tag      = JLanguageMultilang::isEnabled() ? $lang->getTag() : '*';
		$menus    = $site_app->getMenu();
		$default  = $menus->getDefault($tag)->id;
		$active   = $menus->getActive();

		$app       = JFactory::getApplication();
		$input    = $app->input;
		if ($active == null && $passed_item_id = $input->get('ItemId', $default, 'int')) {
			$menus->setActive($passed_item_id);
		}


		/** @var $filer_processor RokSprocket_Provider_AbstractJoomlaPlatformFilter */
		$filer_processor = $this->getFilterProcessor();

		$filer_processor->setModuleId($this->module_id);
		$filer_processor->setDisplayedIds($this->displayed_ids);
		$provider   = $this->params->get('provider', 'joomla');
		$manualsort = ($this->params->get($provider . '_sort', 'automatic') == 'manual') ? true : false;
		if ($manualsort) {
			$filer_processor->setManualSort($manualsort);
			$filer_processor->setManualAppend($this->params->get($provider . '_sort_manual_append', 'after'));
		}

		$filer_processor->process($this->filters, $this->sort_filters, $this->showUnpublished);

		/** @var $query JDatabaseQuery */
		$query         = $filer_processor->getQuery();
		$display_limit = (int)$this->params->get('display_limit', 0);
		$string_query  = (string)$query;
		if ($app->isSite() && is_int($display_limit) && $display_limit > 0) {
			$query = (string)$query . ' LIMIT ' . $display_limit;
		}

		$db = JFactory::getDbo();
		$db->setQuery($query);
		$raw_results = $db->loadColumn();
		if ($error = $db->getErrorMsg()) {
			throw new RokSprocket_Exception($error);
		}


		$zooapp = App::getInstance('zoo');
		$items  = $zooapp->table->item->getByIds($raw_results);
		// sort the items by the order in the raw results
		uasort($items, create_function('$a,$b',
			'$order = '.var_export($raw_results,true) .';'
			.'$aloc = array_search((string)$a->id, $order);'
			.'$bloc = array_search((string)$b->id, $order);'
			.'if ($aloc == $bloc) return 0;'
		    .'return ($aloc < $bloc) ? -1 : 1;'
		));

		$converted = $this->convertRawToItems($items);
		$this->mapPerItemData($converted);
		return $converted;
	}
}
