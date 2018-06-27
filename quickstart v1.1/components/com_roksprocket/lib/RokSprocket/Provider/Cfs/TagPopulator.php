<?php
/**
 * @version   $Id: TagPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Cfs_TagPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        global $wpdb;
        $query = 'SELECT t.name FROM '.$wpdb->terms.' AS t LEFT JOIN '.$wpdb->term_taxonomy.' AS tx ON tx.term_id = t.term_id WHERE taxonomy = "post_tag"';
        $tags = $wpdb->get_results($query, OBJECT_K);

        $options = array();
        foreach ( $tags as $tag) {
            $options[$tag->name] = $tag->name;
        }
        return $options;
    }
}
