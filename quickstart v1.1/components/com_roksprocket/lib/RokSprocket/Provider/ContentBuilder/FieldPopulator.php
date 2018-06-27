<?php
/**
 * @version   $Id: FieldPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_ContentBuilder_FieldPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        $options = array();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id AS value, title AS text');
        $query->from('#__contentbuilder_storage_fields');
        $query->order('name ASC');

        // Get the options.
        $db->setQuery($query);
        $items = $db->loadObjectList('value');


        // Check for a database error.
        if ($db->getErrorNum())
        {
            JError::raiseWarning(500, $db->getErrorMsg());
            return null;
        }

        foreach ($items as $item) {
            $options[$item->value] = $item->text;

        }
        return $options;
    }
}
