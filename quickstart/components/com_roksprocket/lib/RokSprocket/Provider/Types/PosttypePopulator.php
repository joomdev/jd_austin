<?php
/**
 * @version   $Id: PosttypePopulator.php 20646 2014-04-28 10:53:53Z jakub $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Types_PosttypePopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
	    $types_custom_types = get_option( 'wpcf-custom-types' );
	    $options = array();

	    if( is_array( $types_custom_types ) && !empty( $types_custom_types ) ) {
			foreach( $types_custom_types as $custom_type ) {
				$options[$custom_type['slug']] = $custom_type['labels']['name'];
			}
	    }

	    return $options;
    }
}
