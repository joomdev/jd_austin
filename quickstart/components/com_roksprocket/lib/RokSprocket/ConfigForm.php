<?php
/**
 * @version   $Id: ConfigForm.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_ConfigForm extends RokCommon_Form
{

    static $instances = array();

    protected $original_params;

//    public static function getInstance(JForm $form)
//    {
//        if (!array_key_exists($form->getName(), self::$instances)) {
//            self::$instances[$form->getName()] = new RokSubfieldForm($form);
//        }
//        self::$instances[$form->getName()]->updateDataParams();
//        return self::$instances[$form->getName()];
//    }

    public function __construct(RokCommon_Form &$form)
    {
        $form_vars = get_object_vars($form);
        foreach ($form_vars as $form_var_name => $form_var_value) {
            $this->$form_var_name = $form_var_value;
        }
    }

	public function setFormControl($control)
	{
		$this->options['control'] = $control;
	}

	public function setGroup($control)
	{
		$this->options['control'] = $control;
	}

	/**
	 * Method to get an array of JFormField objects in a given fieldset by name.  If no name is
	 * given then all fields are returned.
	 *
	 * @param null|string $set The optional name of the fieldset.
	 *
	 * @param null        $group
	 *
	 * @return array The array of JFormField objects in the fieldset.
	 */
	public function getFieldsetWithGroup($set = null, $group=null)
	{
		// Initialise variables.
		$fields = array();

		// Get all of the field elements in the fieldset.
		if ($set)
		{
			$elements = $this->findFieldsByFieldset($set);
		}
		// Get all fields.
		else
		{
			$elements = $this->findFieldsByGroup();
		}

		// If no field elements were found return empty.
		if (empty($elements))
		{
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element)
		{
			// Get the field groups for the element.
//			$attrs = $element->xpath('ancestor::fields[@name]/@name');
//			$groups = array_map('strval', $attrs ? $attrs : array());
//			$group = implode('.', $groups);

			// If the field is successfully loaded add it to the result array.
			if ($field = $this->loadField($element, $group))
			{
				$fields[$field->id] = $field;
			}
		}

		return $fields;
	}
}
