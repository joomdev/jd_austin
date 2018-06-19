<?php
/**
 * @version   $Id: PodtypePopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Pods_PodtypePopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        global $wpdb;

        $result = $wpdb->get_results('SELECT id, name FROM '.$wpdb->prefix.'pod_types', OBJECT_K);

        foreach($result as $pod){
            $options[$pod->id] = $pod->name;
        }
        return $options;
    }
}
