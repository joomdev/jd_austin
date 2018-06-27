<?php
/**
 * @version   $Id: IValidator.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokCommon_Form_IValidator
{
    /**
     * Method to test the value.
     *
     * @param   object  &$element  The JXmlElement object representing the <field /> tag for the form field object.
     * @param   mixed   $value     The form field value to validate.
     * @param   string  $group     The field name group control value. This acts as as an array container for the field.
     *                             For example if the field has name="foo" and the group value is set to "bar" then the
     *                             full field name would end up being "bar[foo]".
     * @param   object  &$input    An optional JRegistry object with the entire data set to validate against the entire form.
     * @param   object  &$form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   11.1
     * @throws  JException on invalid rule.
     */
    public function test(&$element, $value, $group = null, &$input = null, &$form = null);
}
