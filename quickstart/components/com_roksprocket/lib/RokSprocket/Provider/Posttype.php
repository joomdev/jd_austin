<?php
/**
 * @version   $Id: Posttype.php 21657 2014-06-19 18:02:32Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Posttype extends RokSprocket_Provider_AbstarctWordpressBasedProvider
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
           if (is_plugin_active('wp-post-type-ui/wp-post-type-ui.php')) {
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
		parent::__construct('cpt');
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
        $texts['text_post_content'] = $raw_item->post_content;
        $texts['text_post_excerpt'] = $raw_item->post_excerpt;
        $texts['text_post_title'] = $raw_item->post_title;
        $texts = $this->processPlugins($texts);
        $item->setTextFields($texts);

        //set up images array
        $images = array();
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
        return admin_url( 'post.php?post='.$id.'&action=edit');
    }

    /**
     * @return array the array of image type and label
     */
    public static function getImageTypes()
    {
        return array();
    }

    /**
     * @return array the array of link types and label
     */
    public static function getLinkTypes()
    {
        return array();
    }

    /**
     * @return array the array of link types and label
     */
    public static function getTextTypes()
    {
        $list = array(
            'text_post_content' => array('group' => null, 'display' => 'Post Content'),
            'text_post_title' => array('group' => null, 'display' => 'Post Title'),
            'text_post_excerpt' => array('group' => null, 'display' => 'Post Excerpt'),
        );
        return $list;
    }
}
