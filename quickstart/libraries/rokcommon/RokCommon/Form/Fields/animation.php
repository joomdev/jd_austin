<?php
/**
 * @version   3.2.5 August 4, 2016
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


/**
 * Renders an animation element
 *
 * @package gantry
 * @subpackage admin.elements
 */
class RokCommon_Form_Field_Animation extends RokCommon_Form_Field_List {

    protected $type = 'animation';
    protected $basetype = 'select';

    /**
     * Method to get the field options.
     *
     * @return    array    The field option objects.
     * @since    1.6
     */
    protected function getOptions() {
        $options = parent::getOptions();

        $choices = array("linear",
            "Quad.easeOut",
            "Quad.easeIn",
            "Quad.easeInOut",
            "Cubic.easeOut",
            "Cubic.easeIn",
            "Cubic.easeInOut",
            "Quart.easeOut",
            "Quart.easeIn",
            "Quart.easeInOut",
            "Quint.easeOut",
            "Quint.easeIn",
            "Quint.easeInOut",
            "Expo.easeOut",
            "Expo.easeIn",
            "Expo.easeInOut",
            "Circ.easeOut",
            "Circ.easeIn",
            "Circ.easeInOut",
            "Sine.easeOut",
            "Sine.easeIn",
            "Sine.easeInOut",
            "Back.easeOut",
            "Back.easeIn",
            "Back.easeInOut",
            "Bounce.easeOut",
            "Bounce.easeIn",
            "Bounce.easeInOut",
            "Elastic.easeOut",
            "Elastic.easeIn",
            "Elastic.easeInOut");

        foreach ($choices as $choice) {
            // Create a new option object based on the <option /> element.
            $tmp = RokCommon_HTML_SelectList::option($choice, $choice, 'value', 'text', false);
			$options[] = $tmp;
        }
        return $options;
    }
}
