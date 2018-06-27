<?php
/**
 * @version   $Id: IItemNameHandler.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokCommon_Form_IItemNameHandler
{
	/**
	 * @abstract
	 *
	 * @param string       $name
	 * @param string       $group
	 * @param string|null  $formcontrol
	 * @param bool         $multiple
	 *
	 * @return string the name to use for the html tag
	 */
    public function getName($name, $group = null, $formcontrol = null, $multiple = false);

    /**
     * @abstract
     *
     * @param string       $name
     * @param string|null  $id
     * @param string       $group
     * @param string|null  $formcontrol
     * @param bool         $multiple
     *
     * @return string the id to use for the html tag
     */
    public function getId($name, $id = null, $group = null, $formcontrol = null, $multiple = false);
}
