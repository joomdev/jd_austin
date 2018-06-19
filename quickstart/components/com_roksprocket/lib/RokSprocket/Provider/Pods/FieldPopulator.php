<?php
/**
 * @version   $Id: FieldPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Pods_FieldPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        global $wpdb;

        $query = 'SELECT pf.id, pf.name AS field_name, pt.name AS pod_name FROM '.$wpdb->prefix.'pod_fields AS pf';
        $query .= ' LEFT JOIN '.$wpdb->prefix.'pod_types AS pt ON pt.id = pf.datatype';

        $result = $wpdb->get_results($query, OBJECT_K);

        foreach($result as $field){
            $options[$field->id] = $field->pod_name. ' - '. $field->field_name;
        }
        return $options;
    }
}
