<?php
/**
 * @version   $Id: Types.php 21657 2014-06-19 18:02:32Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Types extends RokSprocket_Provider_AbstarctWordpressBasedProvider
{
    /**
     * @static
     * @return bool
     */
    public static function isAvailable()
    {
        if (!class_exists('WP_Widget')) {
            return false;
        } else {
            if (is_plugin_active('types/wpcf.php')) {
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
        parent::__construct('types');
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
	    $text = apply_filters( 'widget_text', empty( $raw_item->post_content) ? '' :$raw_item->post_content);
        $item->setText(strip_shortcodes($text));
        $item->setCategory($raw_item->category_title);
        $item->setHits(null);
        $item->setRating(null);
        $item->setMetaKey(null);
        $item->setMetaDesc(null);
        $item->setMetaData(null);

        //Set up texts array
        $texts = array();
        $text_fields = self::getFieldTypes(array("textfield", "textarea", "wysiwyg", "checkbox", "numeric", "colorpicker", "email", "phone", "radio", "select"));
        if (count($text_fields)) {
            $text = '';
            foreach ($text_fields as $key => $val) {
                $texts['text_' . $key] = get_post_meta($raw_item->post_id, $val['meta_key'], true);
            }
        }
        $texts['text_post_content'] = $raw_item->post_content;
        $texts['text_post_excerpt'] = $raw_item->post_excerpt;
        $texts['text_post_title'] = $raw_item->post_title;
        $texts = $this->processPlugins($texts);
        $item->setTextFields($texts);

        //set up images array
        $images = array();
        $image_fields = self::getFieldTypes("image");
        if (count($image_fields)) {
            $image = '';
            foreach ($image_fields as $key => $val) {
                $image = new RokSprocket_Item_Image();
                $image->setSource(get_post_meta($raw_item->post_id, $val['meta_key'], true));
                $image->setIdentifier('image_' . $key);
                $image->setCaption('');
                $image->setAlttext('');
                $images[$image->getIdentifier()] = $image;
            }
        }

        if (isset($raw_item->thumbnail_id) && !empty($raw_item->thumbnail_id)) {
            $image = new RokSprocket_Item_Image();
            $image->setSource(wp_get_attachment_url($raw_item->thumbnail_id));
            $image->setIdentifier('image_thumbnail');
            $image->setCaption($raw_item->image_intro_caption);
            $image->setAlttext($raw_item->image_intro_alt);
            $images[$image->getIdentifier()] = $image;
        }
        $item->setImages($images);
        $item->setPrimaryImage($images['image_thumbnail']);

        //set up links array
        $links = array();
        $link_fields = self::getFieldTypes(array("url", "audio", "video", "file", "embed"));
        if (count($text_fields)) {
            $link = '';
            foreach ($link_fields as $key => $val) {
                $link_field = new RokSprocket_Item_Link();
                $link_field->setUrl(get_post_meta($raw_item->post_id, $val['meta_key'], true));
                $link_field->setText('');
                $links['url_' . $key] = $link_field;
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
        $fields = self::getFieldTypes(array("image"));
        $list = array();
        foreach ($fields as $key => $val) {
            $list['image_' . $key]            = array();
            $list['image_' . $key]['group']   = $val['id'];
            $list['image_' . $key]['display'] = $val['name'];
        }
        return $list;
    }

    /**
     * @return array the array of link types and label
     */
    public static function getLinkTypes()
    {
        $fields = self::getFieldTypes(array("url", "audio", "video", "file", "embed"));
        $list = array();
        foreach ($fields as $key => $val) {
            $list['url_' . $key]            = array();
            $list['url_' . $key]['group']   = $val['id'];
            $list['url_' . $key]['display'] = $val['name'];
        }
        return $list;
    }

    /**
     * @return array the array of link types and label
     */
    public static function getTextTypes()
    {
        $fields = self::getFieldTypes(array("textfield", "textarea", "wysiwyg", "checkbox", "numeric", "colorpicker", "email", "phone", "radio", "select"));

        $list = array();
        foreach ($fields as $key => $val) {
            $list['text_' . $key]            = array();
            $list['text_' . $key]['group']   = $val['id'];
            $list['text_' . $key]['display'] = $val['name'];
        }
        $static = array(
            'text_post_content' => array('group' => null, 'display' => 'Post Content'),
            'text_post_excerpt' => array('group' => null, 'display' => 'Post Excerpt'),
            'text_post_title' => array('group' => null, 'display' => 'Post Title'),
        );
        $list = array_merge($static, $list);
        return $list;
    }

    private static function getFieldTypes($fields = false)
    {
        $list = get_option('wpcf-fields', array());

        if ($fields && is_array($fields)) {
            foreach($list as $key => $val){
                if(!in_array($val['type'], $fields)){
                    unset($list[$key]);
                }
            }
        } else if ($list && is_string($fields)) {
            foreach($list as $key => $val){
                if($val['type'] != $fields){
                    unset($list[$key]);
                }
            }
        }

        return $list;
    }
}
