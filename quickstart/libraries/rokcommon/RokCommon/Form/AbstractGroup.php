<?php
/**
 * @version        3.2.5 August 4, 2016
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * original copyright
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('ROKCOMMON') or die;

/**
 *
 */
abstract class RokCommon_Form_AbstractGroup extends RokCommon_Form_AbstractItem implements RokCommon_Form_IGroup
{
    /**
     * @var array
     */
    protected $fields = array();

    protected $prelabel_function = null;

    protected $postlabel_function = null;

	public function __construct(RokCommon_Form $form = null)
	{
		parent::__construct($form);
		$this->assets_content = $this->container->getParameter('form.group.assets.context');
	}


	/**
     * Method to attach a JForm object to the field.
     *
     * @param    object    $element      The RokCommon_XMLElement object representing the <field /> tag for the
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
    public function setup(& $element, $value, $group = null)
    {
        // Make sure there is a valid RokCommon_XMLElement XML element.
        if (!($element instanceof RokCommon_XMLElement) || (string)$element->getName() != 'fields') {
            return false;
        }

        if (!parent::setup($element, $value, $group)) return false;

        $this->fields = $this->form->getSubFields($this->element);

        foreach ($this->fields as $field) {
            if ($field->variance) $this->customized = true;
        }
        return true;
    }

    public function setLabelWrapperFunctions($prelabel_function = null, $postlabel_function = null)
    {
        $this->prelabel_function  = $prelabel_function;
        $this->postlabel_function = $postlabel_function;
    }

    public function preLabel($field)
    {
        if ($this->prelabel_function == null || !function_exists($this->prelabel_function)) return '';
        return call_user_func_array($this->prelabel_function, array($field));
    }

    public function postLabel($field)
    {
        if ($this->postlabel_function == null) return '';
        return call_user_func_array($this->postlabel_function, array($field));
    }
}