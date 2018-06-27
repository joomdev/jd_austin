<?php

/**
 * @version   $Id: K2.php 19225 2014-02-27 00:15:10Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokSprocket_Provider_K2 extends RokSprocket_Provider_AbstarctJoomlaBasedProvider
{

	protected static $available;
	protected static $extra_fields;

	/**
	 * @static
	 * @return bool
	 */
	public static function isAvailable()
	{
		if (isset(self::$available)) {
			return self::$available;
		}


		if (!class_exists('JFactory')) {
			self::$available = false;
		} else {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.extension_id');
			$query->from('#__extensions AS a');
			$query->where('a.type = "component"');
			$query->where('a.element = "com_k2"');
			$query->where('a.enabled = 1');

			$db->setQuery($query);

			if ($db->loadResult()) {
				self::$available = true;
			} else {
				self::$available = false;
			}
		}
		return self::$available;
	}

	/**
	 * @param array $filters
	 * @param array $sort_filters
	 */
	public function __construct($filters = array(), $sort_filters = array())
	{
		parent::__construct('k2');
		$this->setFilterChoices($filters, $sort_filters);
	}

	/**
	 * @param     $raw_item
	 * @param int $dborder
	 *
	 * @return \RokSprocket_Item
	 */
	protected function convertRawToItem($raw_item, $dborder = 0)
	{
		//$textfield = $this->params->get('k2_articletext_field', '');

		$item = new RokSprocket_Item();

		$item->setProvider($this->provider_name);
		$item->setId($raw_item->id);
		$item->setAlias($raw_item->alias);
		$item->setAuthor(($raw_item->created_by_alias) ? $raw_item->created_by_alias : $raw_item->author_name);
		$item->setTitle($raw_item->title);
		$item->setDate($raw_item->created);
		$item->setPublished(($raw_item->published == 1) ? true : false);
		$item->setCategory($raw_item->category_title);
		$item->setHits($raw_item->hits);
		$item->setRating($raw_item->rating);
		$item->setMetaKey($raw_item->metakey);
		$item->setMetaDesc($raw_item->metadesc);
		$item->setMetaData($raw_item->metadata);
		$item->setPublishUp($raw_item->publish_up);
		$item->setPublishDown($raw_item->publish_down);


		$images = array();
		$links  = array();
		$texts  = array();

		// Get the default images for item and category
		$image_sizes = array('_XS', '_S', '_M', '_L', '_XL', '_Generic');

		foreach ($image_sizes as $image_size) {
			$image_uri = 'media/k2/items/cache/' . md5("Image" . $raw_item->id) . $image_size . '.jpg';
			if (JFile::exists(JPATH_SITE . '/' . $image_uri)) {
				$image = new RokSprocket_Item_Image();
				$image->setSource($image_uri);
				$image->setIdentifier('item_image' . $image_size);
				$image->setCaption('');
				$image->setAlttext('');
				$images[$image->getIdentifier()] = $image;
			}
			if (isset($images['item_image_S'])) {
				$item->setPrimaryImage($images['item_image_S']);
			}
		}
		if (isset($raw_item->category_image) && !empty($raw_item->category_image)) {
			$image = new RokSprocket_Item_Image();
			$image->setSource('media/k2/categories/' . $raw_item->category_image);
			$image->setIdentifier('item_image_category');
			$image->setCaption('');
			$image->setAlttext('');
			$images[$image->getIdentifier()] = $image;
		}

		// Get default Text fields for an item
		$texts['text_introtext'] = $raw_item->introtext;
		$texts['text_fulltext']  = $raw_item->fulltext;
		$texts['text_title']     = $raw_item->title;
		$texts['text_metadesc']  = $raw_item->metadesc;


		// get all extra fields for an item
		$item_extra_field_values = json_decode($raw_item->extra_fields);

		if (!empty($item_extra_field_values)) {
			foreach ($item_extra_field_values as $item_extra_field) {
				$field_info = self::getExtraFieldInfo($item_extra_field->id);
				if ($field_info !== false && isset($item_extra_field->value)) {
					switch ($field_info->type) {
						case 'image':
							$image = new RokSprocket_Item_Image();
							$image->setSource($item_extra_field->value);
							$image->setIdentifier('item_image_' . $field_info->field_name);
							$image->setCaption('');
							$image->setAlttext('');
							$images[$image->getIdentifier()] = $image;
							break;
						case 'link':
							$link = new RokSprocket_Item_Link();
							$link->setUrl($item_extra_field->value[1]);
							$link->setText($item_extra_field->value[0]);
							$link->setIdentifier('item_link_' . $item_extra_field->id);
							$links[$link->getIdentifier()] = $link;
							break;
						case 'textarea':
						case 'textfield':
							$texts['text_' . $item_extra_field->id] = $item_extra_field->value;
							break;
					}
				}
			}
		}

		// set the item fields
		$item->setImages($images);
		$item->setLinks($links);

		$texts = $this->processPlugins($texts);
		$item->setTextFields($texts);
		$item->setText($texts['text_introtext']);

		$item->setDbOrder($dborder);

		require_once(JPATH_SITE . '/components/com_k2/helpers/route.php');
		$primary_link = new RokSprocket_Item_Link();
		$primary_link->setUrl(JRoute::_(K2HelperRoute::getItemRoute($raw_item->id . ':' . $raw_item->alias, $raw_item->catid . ':' . $raw_item->category_alias), true));
		$primary_link->getIdentifier('article_link');
		$item->setPrimaryLink($primary_link);

		// unknown joomla items
		$item->setCommentCount($raw_item->comment_count);
		if (isset($raw_item->tags)) {
			$tags = (explode(',', $raw_item->tags)) ? explode(',', $raw_item->tags) : array();
			$item->setTags($tags);
		}
		return $item;
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	protected function getArticleEditUrl($id)
	{
		return JURI::root(true) . '/administrator/index.php?option=com_k2&view=item&cid=' . $id;
	}

	/**
	 * @return array the array of image type and label
	 */
	public static function getImageTypes()
	{
		$list = array();

		if ($fields = self::getFieldTypes("image", false)) {

			if (!empty($fields)) {
				foreach ($fields as $field) {
					$list[$field->id]            = array();
					$list[$field->id]['group']   = $field->group_id;
					$list[$field->id]['display'] = $field->field_name;
				}
			}
		}

		$static = array(
			'item_image_XS'       => array('group' => null, 'display' => 'Extra Small Item Image'),
			'item_image_S'        => array('group' => null, 'display' => 'Small Item Image'),
			'item_image_M'        => array('group' => null, 'display' => 'Medium Item Image'),
			'item_image_L'        => array('group' => null, 'display' => 'Large Item Image'),
			'item_image_XL'       => array('group' => null, 'display' => 'Extra Large Item Image'),
			'item_image_category' => array('group' => null, 'display' => 'Category Image')
		);
		$list   = array_merge($static, $list);
		return $list;
	}

	/**
	 * @return array the array of link types and label
	 */
	public static function getLinkTypes()
	{
		$list = array();

		if ($fields = self::getFieldTypes("link", false)) {

			foreach ($fields as $field) {
				$list[$field->id]            = array();
				$list[$field->id]['group']   = $field->catid;
				$list[$field->id]['display'] = $field->field_name;
			}
		}
		return $list;
	}

	/**
	 * @return array the array of link types and label
	 */
	public static function getTextTypes()
	{
		$list = array();

		if ($fields = self::getFieldTypes(array("textarea", "textfield"), false)) {

			foreach ($fields as $field) {
				$list['text_' . $field->id]            = array();
				$list['text_' . $field->id]['group']   = $field->catid;
				$list['text_' . $field->id]['display'] = ($field->category) ? $field->category . ' - ' : '' . $field->field_name;
			}
		}
		$static = array(
			'text_introtext' => array('group' => null, 'display' => 'Intro Text'),
			'text_title'     => array('group' => null, 'display' => 'Article Title'),
			'text_fulltext'  => array('group' => null, 'display' => 'Full Text'),
			'text_metadesc'  => array('group' => null, 'display' => 'Meta Description Text'),
		);
		$list   = array_merge($static, $list);
		return $list;
	}

	/**
	 * @static
	 * @return array
	 */
	public static function getCCKGroups()
	{
		$populator = new RokSprocket_Provider_K2_CategoryPopulator();
		$options   = $populator->getPicklistOptions();
		return $options;
	}

	public static function getFieldTypes($needed_field_types = false, $id_only = true)
	{
		self::loadExtraFieldTypes();
		if (!is_array($needed_field_types)) {
			$needed_field_types = array($needed_field_types);
		}

		$types = array();
		foreach (self::$extra_fields as $extra_field) {
			foreach ($needed_field_types as $needed_field_type) {
				if (($extra_field->type == $needed_field_type) || ($needed_field_type == 'all')) {
					if ($id_only) {
						$idclass     = new stdClass();
						$idclass->id = $extra_field->id;
						$types[]     = $idclass;
					} else {
						$types[] = $extra_field;
					}
				}
			}
		}
		return $types;
	}

	protected static function getExtraFieldInfo($id)
	{
		self::loadExtraFieldTypes();
		foreach (self::$extra_fields as $extra_field) {
			if ($extra_field->id == $id) {
				return $extra_field;
			}
		}
		return false;
	}

	protected static function loadExtraFieldTypes()
	{
		if (!isset(self::$extra_fields)) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('f.id, f.type, f.name as field_name, fg.id as group_id, fg.name as group_name, cat.id as catid, cat.name as category');
			$query->from('#__k2_extra_fields AS f');
			$query->join('LEFT', '#__k2_extra_fields_groups AS fg ON fg.id = f.group');
			$query->join('LEFT', '#__k2_categories AS cat ON cat.extraFieldsGroup = fg.id');
			$query->group('f.id');
			$query->order('fg.name, f.name');

			$db->setQuery($query);

			self::$extra_fields = $db->loadObjectList();
			if (self::$extra_fields == null) {
				self::$extra_fields = array();
			}
		}
	}
}

