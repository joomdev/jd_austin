<?php
/**
 * @version   $Id: CategoryPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_ContentBuilder_CategoryPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        //Initialise variables.
        $options = $this->categories('com_k2');
        return $options;
    }

    /**
     * Returns an array of categories for the given extension.
     *
     * @param   string  $extension  The extension option.
     * @param   array   $config     An array of configuration options. By default, only published and unpublished categories are returned.
     *
     * @return  array   Categories for the extension
     *
     * @since   11.1
     */
    public function categories($extension, $config = array('filter.published' => array(0, 1)))
    {
        $config = (array)$config;
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('a.id, a.name, a.parent');
        $query->from('#__k2_categories AS a');
        $query->where('a.id > 0');

        // Filter on extension.
        //$query->where('extension = ' . $db->quote($extension));

        // Filter on the published state
        if (isset($config['filter.published'])) {
            if (is_numeric($config['filter.published'])) {
                $query->where('a.published = ' . (int)$config['filter.published']);
            } elseif (is_array($config['filter.published'])) {
                JArrayHelper::toInteger($config['filter.published']);
                $query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
            }
        }

        $query->order('a.parent, a.ordering');

        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array ();
        if ($mitems) {
            foreach ($mitems as $v) {
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                $pt = $v->parent;
                $list = @$children[$pt]?$children[$pt]: array ();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHtml::_('menu.treerecurse', 0, '', array (), $children, 9999, 0, 0);

        $mitems = array();
        foreach ($list as $item) {
            $item->treename = JString::str_ireplace('&#160;&#160;-', ' -', $item->treename);
            $item->treename = JString::str_ireplace('&#160;&#160;', ' -', $item->treename);
            $mitems[$item->id] = $item->treename;
        }
        return $mitems;
    }
}
