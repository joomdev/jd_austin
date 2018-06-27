<?php
/**
 * @version   $Id: StatusPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Posttype_StatusPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        $options['publish'] = 'publish';
        $options['private'] = 'private';
        $options['draft'] = 'draft';
        $options['pending'] = 'pending';
        return $options;
    }
}
