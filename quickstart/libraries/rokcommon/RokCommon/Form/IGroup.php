<?php
/**
 * @version   $Id: IGroup.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokCommon_Form_IGroup extends RokCommon_Form_IItem
{
    /**
     * @abstract
     *
     * @param string $prelabel_function
     * @param string $postlabel_function
     */
    public function setLabelWrapperFunctions($prelabel_function = null, $postlabel_function = null);

    /**
     * @abstract
     *
     * @param string $field
     */
    public function preLabel($field);

    /**
     * @abstract
     *
     * @param string $field
     */
    public function postLabel($field);
}
