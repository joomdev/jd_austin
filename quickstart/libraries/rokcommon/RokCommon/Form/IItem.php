<?php
/**
 * @version   $Id: IItem.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokCommon_Form_IItem
{
    /**
     * Method to get the name used for the field input tag.
     *
     * @param    string    $fieldName    The field element name.
     *
     * @return    string    The name to be used for the field input tag.
     * @since    1.6
     */
    public function getName($fieldName);



    public function getInput();

    /**
     * Method to get the field title.
     *
     * @return  string  The field title.
     * @since   11.1
     */
    public function getTitle();


    /**
     * Method to get the field label markup.
     *
     * @return    string    The field label markup.
     * @since    1.6
     */
    public function getLabel();


    /**
     * Method to attach a JForm object to the field.
     *
     * @param    object    $form    The JForm object to attach to the form field.
     *
     * @return    object    The form field object so that the method can be used in a chain.
     * @since    1.6
     */
    public function setForm(RokCommon_Form $form);



    /**
     * Method to get the id used for the field input tag.
     *
     * @param    string    $fieldId      The field element id.
     * @param    string    $fieldName    The field element name.
     *
     * @return    string    The id to be used for the field input tag.
     * @since    1.6
     */
    public function getId($fieldId, $fieldName);

    /**
     * Method to attach a JForm object to the field.
     *
     * @param    object    $element      The JXMLElement object representing the <field /> tag for the
     *                                   form field object.
     * @param    mixed     $value        The form field default value for display.
     * @param    string    $group        The field name group control value. This acts as as an array
     *                                   container for the field. For example if the field has name="foo"
     *                                   and the group value is set to "bar" then the full field name
     *                                   would end up being "bar[foo]".
     *
     * @return    boolean    True on success.
     * @since    1.6
     */
    public function setup(& $element, $value, $group = null);


    /**
     * @static
     * @return void
     */
    public static function initialize();

    /**
     * @static
     * @return void
     */
    public static function finalize();


    /**
     * @param  $callback
     *
     * @return mixed
     */
    public function render($callback);
}
