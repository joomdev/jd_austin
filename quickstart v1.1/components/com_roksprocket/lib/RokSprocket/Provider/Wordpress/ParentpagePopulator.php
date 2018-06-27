<?php
/**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Wordpress_ParentpagePopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        global $wpdb;
        $query = 'SELECT ID, post_title FROM '.$wpdb->posts.'
                    WHERE ID IN (
        	            SELECT DISTINCT CONCAT_WS(",", post_parent) FROM '.$wpdb->posts.' WHERE (post_type = "page" AND post_parent != 0)
        	        )';
        $parent_posts = $wpdb->get_results($query, OBJECT_K);

        $options = array();
        foreach ( $parent_posts as $parent_post) {
            $options[$parent_post->ID] = $parent_post->post_title;
        }
        return $options;
    }
}