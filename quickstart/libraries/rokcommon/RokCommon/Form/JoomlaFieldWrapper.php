<?php
/**
 * @version   $Id: JoomlaFieldWrapper.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 *
 */
class RokCommon_Form_JoomlaFieldWrapper extends JFormField
{
	/** @var JFormField */
	public $field;

	/**
	 * @param null|object $joomlafield
	 * @param             $element
	 * @param             $group
	 * @param             $value
	 * @param             $name
	 * @param             $id
	 */
	public function __construct($element, $group, $value, $name, $id)
	{
		$this->field = $this->loadField($element, $group, $value);
		$this->field->id = $id;
		$this->field->name = $name;
	}

	public function setRokCommonForm(RokCommon_Form &$form){
		$this->field->form = $form;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		return $this->field->getInput();
	}

	/**
	 * Method to load, setup and return a JFormField object based on field data.
	 *
	 * @param   string  $element  The XML element object representation of the form field.
	 * @param   string  $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value    The optional value to use as the default for the field.
	 *
	 * @return  mixed  The JFormField object for the field or boolean false on error.
	 *
	 * @since   11.1
	 */
	protected function loadField($element, $group = null, $value = null)
	{

		// Get the field type.
		$type = $element['type'] ? (string)$element['type'] : 'text';

		// Load the JFormField object for the field.
		/** @var $field JFormField */
		$field =  JFormHelper::loadFieldType($type, true);;

		// If the object could not be loaded, get a text field object.
		if ($field === false) {
			$field =  JFormHelper::loadFieldType('text', true);;
		}

		// Get the value for the form field if not set.
		// Default to the translated version of the 'default' attribute
		// if 'translate_default' attribute if set to 'true' or '1'
		// else the value of the 'default' attribute for the field.
		if ($value === null) {
			$default = (string)$element['default'];
			if (($translate = $element['translate_default']) && ((string)$translate == 'true' || (string)$translate == '1')) {
				$lang = JFactory::getLanguage();
				if ($lang->hasKey($default)) {
					$debug   = $lang->setDebug(false);
					$default = JText::_($default);
					$lang->setDebug($debug);
				} else {
					$default = JText::_($default);
				}
			}
		}

		if ($field->setup($element, $value, $group)) {
			return $field;
		} else {
			return false;
		}
	}

}