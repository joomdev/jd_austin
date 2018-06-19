<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of HelloWorld component
 */
class com_roksprocketInstallerScript
{
    /**
     * method to run after an install/update/uninstall method
     *
     * @param $type
     * @param $parent
     *
     * @return void
     */
    public function postflight($type, $parent)
    {
        $db = JFactory::getDbo();
        // $parent is the class calling this method

        //Remove any old config entries
        $query = $db->getQuery(true);
        $query->delete('#__rokcommon_configs')->where($db->quoteName('extension') . ' = ' . $db->quote('roksprocket'));
        // query extension id and client id
        $db->setQuery($query);
        $db->query();

        $query = $db->getQuery(true);
        $query->insert('#__rokcommon_configs')->columns('extension, type, file, priority');
        $query->values("'roksprocket', 'container', '/components/com_roksprocket/container.xml', 10");
        $query->values("'roksprocket', 'library', '/components/com_roksprocket/lib', 10");
        $db->setQuery($query);
        $db->query();
    }
}