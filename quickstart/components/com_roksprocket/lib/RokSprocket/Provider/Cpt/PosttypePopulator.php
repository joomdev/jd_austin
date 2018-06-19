<?php
/**
 * @version   $Id: PosttypePopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Cpt_PosttypePopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        $options['post'] = 'post';
        $options['page'] = 'page';

        $post_types = get_option('cpt_custom_post_types', array());
        foreach($post_types as $post_type){
            $options[$post_type['name']] = $post_type['name'];
        }
        return $options;
    }
}
