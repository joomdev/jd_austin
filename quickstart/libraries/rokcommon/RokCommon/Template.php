<?php
/**
 * @version   $Id: Template.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;

class RokCommon_Template
{
    public static function replace($token, $replacement, $string)
    {
        return str_replace('[%'.$token.'%]', $replacement, $string);
    }
}
