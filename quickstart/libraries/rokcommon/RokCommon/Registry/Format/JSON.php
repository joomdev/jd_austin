<?php
/**
 * @version   3.2.5 August 4, 2016
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * derived from JoomlaRTCacheDriver with original copyright and license
 * @copyright    Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('ROKCOMMON') or die;

/**
 * JSON format handler for RokCommon_Registry.
 *
 * @package        JoomlaRTCacheDriver.Framework
 * @subpackage    Registry
 * @since        1.6
 */
class RokCommon_Registry_Format_JSON extends RokCommon_Registry_Format
{
    /**
     * Converts an object into a JSON formatted string.
     *
     * @param    object    Data source object.
     * @param    array    Options used by the formatter.
     * @return    string    JSON formatted string.
     */
    public function objectToString($object, $options = array())
    {
        return json_encode($object);
    }

    /**
     * Parse a JSON formatted string and convert it into an object.
     *
     * If the string is not in JSON format, this method will attempt to parse it as INI format.
     *
     * @param    string    JSON formatted string to convert.
     * @param    array    Options used by the formatter.
     * @return    object    Data object.
     */
    public function stringToObject($data, $process_sections = false)
    {
        $data = trim($data);
        if ((substr($data, 0, 1) != '{') && (substr($data, -1, 1) != '}'))
        {
            $ini = & RokCommon_Registry_Format::getInstance('INI');
            $obj = $ini->stringToObject($data, $process_sections);
        } else
        {
            $obj = json_decode($data);
        }
        return $obj;
    }
}
