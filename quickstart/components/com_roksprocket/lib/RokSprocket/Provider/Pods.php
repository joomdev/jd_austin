<?php
/**
 * @version   $Id: Pods.php 21657 2014-06-19 18:02:32Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Pods extends RokSprocket_Provider_AbstarctWordpressBasedProvider
{
	/**
	 * @static
	 * @return bool
	 */
    public static function isAvailable()
   	{
           return false;

       if (!class_exists('WP_Widget')) {
           return false;
       } else {
           if (is_plugin_active('pods/init.php')) {
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
		parent::__construct('pods');
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
        $item->setAuthor(($raw_item->display_name) ? $raw_item->display_name : $raw_item->user_nicename);
        $item->setAuthor($raw_item->user_nicename);
        $item->setTitle($raw_item->post_title);
        $item->setDate($raw_item->post_date);
        $item->setPublished(($raw_item->post_status == "publish") ? true : false);
        $item->setText(strip_shortcodes($raw_item->post_content));
        $item->setCategory($raw_item->category_title);
        $item->setHits(null);
        $item->setRating(null);
        $item->setMetaKey(null);
        $item->setMetaDesc(null);
        $item->setMetaData(null);

        //Set up texts array
        $texts = array();
        $text_fields = self::getFieldTypes(array("txt", "desc"));
        if (count($text_fields)) {
            $text = '';
            foreach ($text_fields as $field) {
                $texts['text_' . $field->name] = getFieldValue($raw_item->post_id, $field->name, $field->table);
            }
        }
        $texts = $this->processPlugins($texts);
        $item->setTextFields($texts);

        //set up images array
        $images = array();
        $image_fields = self::getFieldTypes("file");
        if (count($image_fields)) {
            $image = '';
            foreach ($image_fields as $field) {
                $image = new RokSprocket_Item_Image();
                $image->setSource(getFieldValue($raw_item->post_id, $field->name, $field->table));
                $image->setIdentifier('image_' . $field->name);
                $image->setCaption('');
                $image->setAlttext('');
                $images[$image->getIdentifier()] = $image;
            }
        }
        $item->setImages($images);
        $item->setPrimaryImage($images['image_thumbnail']);

        //set up links array
        $links = array();
        $link_fields = self::getFieldTypes("txt");
        if (count($text_fields)) {
            $link = '';
            foreach ($link_fields as $field) {
                $link_field = new RokSprocket_Item_Link();
                $link_field->setUrl(getFieldValue($raw_item->post_id, $field->name, $field->table));
                $link_field->setText('');
                $links['url_' . $field->name] = $link_field;
            }
        }
        $item->setLinks($links);

        $primary_link = null;


//        $primary_link = new RokSprocket_Item_Link();
//        $primary_link->setUrl(get_permalink($raw_item->post_id));
//        $primary_link->getIdentifier('article_link');
//        $item->setPrimaryLink($primary_link);

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
        $fields = self::getFieldTypes(array("image"));
        $list = array();
        foreach ($fields as $field) {
            $list['image_' . $field->name]            = array();
            $list['image_' . $field->name]['group']   = $field->name;
            $list['image_' . $field->name]['display'] = $field->label;
        }
        return $list;
    }

    /**
     * @return array the array of link types and label
     */
    public static function getLinkTypes()
    {
        $fields = self::getFieldTypes(array("url"));
        $list = array();
        foreach ($fields as $field) {
            $list['url_' . $field->name]            = array();
            $list['url_' . $field->name]['group']   = $field->name;
            $list['url_' . $field->name]['display'] = $field->label;
        }
        return $list;
    }

    /**
     * @return array the array of link types and label
     */
    public static function getTextTypes()
    {
        $fields = self::getFieldTypes(array("textfield", "wysiwyg"));

        $list = array();
        foreach ($fields as $field) {
            $list['text_' . $field->name]            = array();
            $list['text_' . $field->name]['group']   = $field->name;
            $list['text_' . $field->name]['display'] = $field->label;
        }
        return $list;
    }

    private static function getFieldTypes($fields = false)
    {
        global $wpdb;

        $query = 'SELECT id, pf.name, pt.name AS table FROM '.$wpdb->pod_fields;
        $query .= ' LEFT JOIN '.$wpdb->pod_types.' AS pt ON pt. = pf.datatype';

        if ($fields && is_array($fields)) {
            $query .= ' WHERE pf.coltype IN (' . implode(',', $fields) . ')';
        } else if ($fields && is_string($fields)) {
            $query .= ' WHERE pf.coltype = "'.$fields.'"';
        }
        $list = $wpdb->get_results($query, OBJECT_K);

        return $list;
    }

    private static function getFieldValue($post_id = false, $field_name = false, $field_table = false)
    {
        if(!$field_name || !$post_id || $field_table) return '';

        global $wpdb;

        $field_table = 'pod_tbl_'.$field_table;

        $query = 'SELECT '.$wpdb->$field_name;
        $query .= ' FROM '.$wpdb->$field_table;
        $query .= ' WHERE id = "'.$post_id.'"';

        $result = $wpdb->get_results($query);

        return $result;
    }
}
