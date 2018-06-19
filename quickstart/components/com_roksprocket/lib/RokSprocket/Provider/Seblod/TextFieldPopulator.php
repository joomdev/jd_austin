<?php
/**
 * @version   $Id: TextFieldPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Seblod_TextFieldPopulator implements RokCommon_Filter_IPicklistPopulator
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

        $query->select('cf.name AS value, cf.options, cf.options2, CONCAT(ct.title, " - ", IF(ctf.label!="",ctf.label,cf.label)) AS title');
        $query->from('#__cck_core_fields AS cf');
        $query->join('LEFT', '#__cck_core_type_field AS ctf ON ctf.fieldid = cf.id');
        $query->join('LEFT', '#__cck_core_types AS ct ON ct.id = ctf.typeid');
        $query->join('LEFT', '#__cck_core AS c ON c.cck = ct.name');
        $query->where('c.storage_location = "joomla_article"');
        $query->where('cf.storage_table = "#__cck_core"');
        $query->where('cf.type = "textarea"');
        $query->group('cf.id');
        $query->order('cf.title ASC');


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
