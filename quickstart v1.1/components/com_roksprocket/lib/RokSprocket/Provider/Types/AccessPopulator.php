<?php
/**
 * @version   $Id: AccessPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Types_AccessPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        $editable_roles = get_editable_roles();

        foreach ( $editable_roles as $role => $details ) {
            $name = translate_user_role($details['name'] );
            $options[esc_attr($role)] = $name;
        }
        return $options;
    }
}
