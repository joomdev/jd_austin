<?php

/**
 * @version   $Id: Cfs.php 21657 2014-06-19 18:02:32Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokSprocket_Provider_Cfs extends RokSprocket_Provider_AbstarctWordpressBasedProvider
{
	protected static $extra_fields;

	/**
	 * @static
	 * @return bool
	 */
	public static function isAvailable()
	{
		if (!class_exists('WP_Widget')) {
			return false;
		} else {
			if (is_plugin_active('custom-field-suite/cfs.php')) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * @param array $filters
	 * @param array $sort_filters
	 */
	public function __construct($filters = array(), $sort_filters = array())
	{
		parent::__construct('cfs');
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

		$item = new RokSprocket_Item();

		$item->setProvider($this->provider_name);
		$item->setId($raw_item->post_id);
		$item->setAlias($raw_item->post_name);
		$item->setAuthor(($raw_item->display_name) ? $raw_item->display_name : $raw_item->user_nicename);
		$item->setTitle($raw_item->post_title);
		$item->setDate($raw_item->post_date);
		$item->setPublished(($raw_item->post_status == "publish") ? true : false);
		$text = apply_filters('widget_text', empty($raw_item->post_content) ? '' : $raw_item->post_content);
		$item->setText(strip_shortcodes($text));
		$item->setCategory($raw_item->category_title);
		$item->setHits(null);
		$item->setRating(null);
		$item->setMetaKey(null);
		$item->setMetaDesc(null);
		$item->setMetaData(null);

		//Set up texts array
		$texts = array();
		if ($text_fields = self::getFieldTypes(array("text", "wysiwyg"))) {
			$text = '';
			foreach ($text_fields as $field) {
				$texts['text_' . $field['id']] = self::getFieldValue($raw_item->post_id, $field['id']);
			}
		}
		$texts['text_post_content'] = $raw_item->post_content;
		$texts['text_post_excerpt'] = $raw_item->post_excerpt;
		$texts['text_post_title']   = $raw_item->post_title;
		$texts                      = $this->processPlugins($texts);
		$item->setTextFields($texts);

		//set up images array
		$images = array();
		if ($image_fields = self::getFieldTypes("file")) {
			$image = '';
			foreach ($image_fields as $field) {
				$image = new RokSprocket_Item_Image();
				$image->setSource(wp_get_attachment_url(self::getFieldValue($raw_item->post_id, $field['id'])));
				$image->setIdentifier('image_' . $field['id']);
				$image->setCaption('');
				$image->setAlttext('');
				$images[$image->getIdentifier()] = $image;
			}
		}

		if (isset($raw_item->thumbnail_id) && !empty($raw_item->thumbnail_id)) {
			$image = new RokSprocket_Item_Image();
			$image->setSource(wp_get_attachment_url($raw_item->thumbnail_id));
			$image->setIdentifier('image_thumbnail');
			$image->setCaption('');
			$image->setAlttext('');
			$images[$image->getIdentifier()] = $image;
		}
		$item->setImages($images);
		$item->setPrimaryImage($images['image_thumbnail']);

		//set up links array
		$links = array();
		if ($link_fields = self::getFieldTypes("url")) {
			$link = '';
			foreach ($link_fields as $field) {
				$link_field = new RokSprocket_Item_Link();
				$link_field->setUrl(self::getFieldValue($raw_item->post_id, $field['id']));
				$link_field->setText('');
				$links['url_' . $field['id']] = $link_field;
			}
		}
		$item->setLinks($links);

		$primary_link = new RokSprocket_Item_Link();
		$primary_link->setUrl(get_permalink($raw_item->post_id));
		$primary_link->getIdentifier('article_link');
		$item->setPrimaryLink($primary_link);

		$item->setCommentCount($raw_item->comment_count);
        if (!empty($raw_item->tags)) {
            $item->setTags($raw_item->tags);
        }

		$item->setDbOrder($dborder);

		return $item;
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	protected function getArticleEditUrl($id)
	{
		return admin_url('post.php?post=' . $id . '&action=edit');
	}

	/**
	 * @return array the array of image type and label
	 */
	public static function getImageTypes()
	{
		$list = array();

		if ($fields = self::getFieldTypes(array("file"))) {
			foreach ($fields as $field) {
				$list['image_' . $field['id']]            = array();
				$list['image_' . $field['id']]['group']   = $field['id'];
				$list['image_' . $field['id']]['display'] = $field['label'];
			}
		}
		return $list;
	}

	/**
	 * @return array the array of link types and label
	 */
	public static function getLinkTypes()
	{
		$list = array();

		if ($fields = self::getFieldTypes(array("url"))) {
			foreach ($fields as $field) {
				$list['url_' . $field['id']]            = array();
				$list['url_' . $field['id']]['group']   = $field['id'];
				$list['url_' . $field['id']]['display'] = $field['label'];
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

		if ($fields = self::getFieldTypes(array("text", "textarea"))) {

			foreach ($fields as $field) {
				$list['text_' . $field['id']]            = array();
				$list['text_' . $field['id']]['group']   = $field['id'];
				$list['text_' . $field['id']]['display'] = $field['label'];
			}
		}
		$static = array(
			'text_post_content' => array('group' => null, 'display' => 'Post Content'),
			'text_post_excerpt' => array('group' => null, 'display' => 'Post Excerpt'),
			'text_post_title'   => array('group' => null, 'display' => 'Post Title'),
		);
		$list   = array_merge($static, $list);
		return $list;
	}

	private static function getFieldTypes($needed_field_types = false)
	{
		if (!isset(self::$extra_fields)) {

			$fields       = array();
			$field_groups = get_posts(array('post_type' => 'cfs', 'post_status' => 'publish'));
			foreach ($field_groups as $field_group) {
				$group = get_post_meta($field_group->ID, 'cfs_fields', true);
				foreach ($group as $field) {
					$fields[] = $field;
				}
			}
//            $fields = array_combine($fields, $next_fields);

			self::$extra_fields = $fields;
			if (self::$extra_fields == null) {
				self::$extra_fields = array();
			}
		}

		if (!is_array($needed_field_types)) {
			$needed_field_types = array($needed_field_types);
		}
		foreach (self::$extra_fields as $key => $val) {
			if (in_array($val['type'], $needed_field_types)) {
				$types[] = $val;
			}
		}

		return $types;
	}

	private static function getFieldValue($post_id = false, $field_id = false)
	{
		if (!$field_id || !$post_id) return '';

		global $wpdb;

		$query = 'SELECT pm.meta_value';
		$query .= ' FROM ' . $wpdb->prefix . 'cfs_values AS cv';
		$query .= ' LEFT JOIN ' . $wpdb->prefix . 'postmeta AS pm ON pm.meta_id = cv.meta_id';
		$query .= ' WHERE cv.field_id = "' . $field_id . '"';
		$query .= ' AND cv.post_id = "' . $post_id . '"';

		$result = $wpdb->get_results($query);

		return $result[0]->meta_value;
	}
}
