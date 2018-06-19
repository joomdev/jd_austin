<?php
/**
 * @version   $Id: Joomla17.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Registry_Converter_Joomla17 implements RokCommon_Registry_IConverter
{
    /**
     * Convert a registry type object to a RokCommon_Registry
     * @static
     *
     * @param JRegistry $original The original registry type object to convert to a RokCommon_Registry
     * @return \RokCommon_Registry
     */
    public function convert($original)
    {
        $registry = new RokCommon_Registry();
        $original_values = $original->toArray();
        self::copyData($registry, $original_values);
        return $registry;
    }


    /**
     * @param RokCommon_Registry $registry
     * @param                    $value
     * @param array              $parent
     *
     * @return mixed
     */
    protected function copyData(RokCommon_Registry &$registry, &$value, $parent = array())
    {
        foreach($value as $key => $subvalue)
        {
            $new_parent = $parent;
            array_push($new_parent, $key);
            if (is_array($subvalue))
            {
                $retdata = self::copyData($registry, $subvalue, $new_parent);
            } else {
                $registry->set(implode('.',$new_parent), $subvalue);
            }
        }
        return;
    }
}
