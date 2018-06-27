<?php
/**
 * @version   $Id: TagPopulator.php 19247 2014-02-27 18:27:46Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_TagPopulator implements RokCommon_Filter_IPicklistPopulator
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

        $query->select('a.name AS value, a.name AS text');
        $query->from('#__zoo_tag AS a');
        $query->group('a.name');
        $query->order($query->qn('name') . ' ASC');

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