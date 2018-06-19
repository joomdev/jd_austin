<?php
/**
 * @version   $Id: PosttypePopulator.php 20645 2014-04-28 10:50:25Z jakub $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Wordpress_PosttypePopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
	    $post_types = get_post_types( array( 'public' => true ) );

	    foreach( $post_types as $post_type => $name ) {
		    $options[$post_type] = ucwords( $name );
	    }

	    return $options;
    }
}
