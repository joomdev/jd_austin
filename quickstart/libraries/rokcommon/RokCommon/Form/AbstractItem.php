<?php
/**
 * @version   $Id: AbstractItem.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokCommon_Form_AbstractItem implements RokCommon_Form_IItem
{
	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;

	/**
	 * The JForm object of the form attached to the form field.
	 *
	 * @var        object
	 * @since    1.6
	 */
	protected $form;

	/**
	 * The form control prefix for field names from the JForm object attached to the form field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $formControl;

	/**
	 * The hidden state for the form field.
	 *
	 * @var        boolean
	 * @since    1.6
	 */
	protected $hidden = false;

	/**
	 * True to translate the field label string.
	 * True to translate the field label string.
	 *
	 * @var        boolean
	 * @since    1.6
	 */
	protected $translateLabel = true;

	/**
	 * True to translate the field description string.
	 *
	 * @var        boolean
	 * @since    1.6
	 */
	protected $translateDescription = true;

	/**
	 * The JXMLElement object of the <field /> XML element that describes the form field.
	 *
	 * @var        object
	 * @since    1.6
	 */
	protected $element;

	/**
	 * The document id for the form field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $id;

	/**
	 * The input for the form field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $input;

	/**
	 * The label for the form field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $label;

	/**
	 * The name of the form field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $name;

	/**
	 * The name of the field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $fieldname;

	/**
	 * The group of the field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $group;

	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type;

	protected $basetype;

	/**
	 * The value of the form field.
	 *
	 * @var        mixed
	 * @since    1.6
	 */
	protected $value;

	/**
	 * @var string
	 */
	protected $panel_position = 'left';

	/**
	 * @var bool
	 */
	protected $show_label = true;

	/**
	 * @var mixed
	 */
	protected $base_value;

	/**
	 * @var bool
	 */
	protected $customized = false;


	/**
	 * @var bool
	 */
	protected $setinoverride = true;

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @var bool
	 */
	protected $detached;

	/**
	 * @var mixed
	 */
	protected $default;

	protected $assets_content;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param    object    $form    The form to attach to the form field object.
	 *
	 * @return    void
	 * @since    1.6
	 */
	public function __construct(RokCommon_Form $form = null)
	{
		$this->container = RokCommon_Service::getContainer();

		// If there is a form passed into the constructor set the form and form control properties.
		if ($form instanceof RokCommon_Form) {
			$this->form        = $form;
			$this->formControl = $form->getFormControl();
		}
	}

	/**
	 * @param  $name
	 * @param  $value
	 *
	 * @return void
	 */
	public function __set($name, $value)
	{
		if (property_exists($this, $name)) {
			$this->{$name} = $value;
		}
	}


	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 * @since   11.1
	 */
	public function getTitle()
	{
		// Initialise variables.
		$title = '';

		if ($this->hidden) {

			return $title;
		}

		// Get the label text from the XML element, defaulting to the element name.
		$title = $this->element['label'] ? (string)$this->element['label'] : (string)$this->element['name'];
		$title = $this->translateLabel ? JText::_($title) : $title;

		return $title;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return    string    The field label markup.
	 * @since    1.6
	 */
	public function getLabel()
	{
		// Initialise variables.
		$label = '';

		if ($this->hidden) {
			return $label;
		}

		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string)$this->element['label'] : (string)$this->element['name'];
		$text = $this->translateLabel ? JText::_($text) : $text;

		// Build the class for the label.
		$class = !empty($this->description) ? 'hasTip' : '';
		$class = $this->required == true ? $class . ' required' : $class;

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->description)) {
			$label .= ' title="' . htmlspecialchars(trim($text, ':') . '::' . ($this->translateDescription ? JText::_($this->description) : $this->description), ENT_COMPAT, 'UTF-8') . '"';
		}

		// Add the label text and closing tag.
		$label .= '>' . $text . '</label>';

		return $label;
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param \RokCommon_Form $form The JForm object to attach to the form field.
	 *
	 * @return    object    The form field object so that the method can be used in a chain.
	 * @since    1.6
	 */
	public function setForm(RokCommon_Form $form)
	{
		$this->form        = $form;
		$this->formControl = $form->getFormControl();

		return $this;
	}


	/**
	 * Method to get the name used for the field input tag.
	 *
	 * @param   string  $fieldName  The field element name.
	 *
	 * @return  string  The name to be used for the field input tag.
	 *
	 * @since   11.1
	 */
	public function getName($fieldName)
	{
		/** @var $namehandler RokCommon_Form_IItemNameHandler */
		$namehandler = $this->container->getService('form.namehandler');

		return $namehandler->getName($fieldName, $this->group, $this->formControl, false);
	}

	/**
	 * Method to get the id used for the field input tag.
	 *
	 * @param    string    $fieldId      The field element id.
	 * @param    string    $fieldName    The field element name.
	 *
	 * @return    string    The id to be used for the field input tag.
	 * @since    1.6
	 */
	public function getId($fieldId, $fieldName)
	{
		/** @var $namehandler RokCommon_Form_IItemNameHandler */
		$namehandler = $this->container->getService('form.namehandler');

		return $namehandler->getId($fieldName, $fieldId, $this->group, $this->formControl, false);
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param    string    $name    The property name for which to the the value.
	 *
	 * @return    mixed|null    The property value or null.
	 * @since    1.6
	 */
	public function __get($name)
	{

		switch ($name) {
			case 'input':
				// If the input hasn't yet been generated, generate it.
				if (empty($this->input)) {
					$this->input = $this->getInput();
				}
				return $this->input;
				break;

			case 'label':
				// If the label hasn't yet been generated, generate it.
				if (empty($this->label)) {
					$this->label = $this->getLabel();
				}
				return $this->label;
				break;
			case 'title':
				return $this->getTitle();
				break;
			default :
				if (property_exists($this, $name) && isset($this->{$name})) {
					return $this->{$name};
				} elseif (method_exists($this, 'get' . ucfirst($name))) {
					return call_user_func(array($this, 'get' . ucfirst($name)));
				} elseif (property_exists($this, $name)) {
					return $this->{$name};
				} elseif (isset($this->element[$name])) {
					return (string)$this->element[$name];
				} else {
					return null;
				}
				break;
		}
	}


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
	public function setup(& $element, $value, $group = null)
	{
		global $gantry;

		// Make sure there is a valid JFormField XML element.
		if (!($element instanceof RokCommon_XMLElement)) {
			return false;
		}

		// Reset the input and label values.
		$this->input = null;
		$this->label = null;

		// Set the xml element object.
		$this->element = $element;

		// Get some important attributes from the form field element.
		$class               = (string)$element['class'];
		$id                  = (string)$element['id'];
		$name                = (string)$element['name'];
		$type                = (string)$element['type'];
		$panel_position      = (string)$element['panel_position'];
		$this->show_label    = ((string)$element['show_label'] == 'false') ? false : true;
		$this->setinoverride = ((string)$element['setinoverride'] == 'false') ? false : true;
		$default             = (string)$element['default'];


//        if (!empty($name)) {
//            if (empty($group)) {
//                $gantry_name = $name;
//            } else {
//                $groups = explode('.', $group);
//                if (count($groups > 0)) {
//                    //array_shift($groups);
//                    $groups[]    = $name;
//                    $gantry_name = implode('-', $groups);
//                }
//            }
//	        // TODO set this up to get for Default not for RokCommon param value
//            //$this->base_value = $gantry->get($gantry_name, null);
//        }

		// Set the field description text.
		$this->description = (string)$element['description'];

		// Set the visibility.
		$this->hidden = ((string)$element['type'] == 'hidden' || (string)$element['hidden'] == 'true');

		// Determine whether to translate the field label and/or description.
		$this->translateLabel       = !((string)$this->element['translate_label'] == 'false' || (string)$this->element['translate_label'] == '0');
		$this->translateDescription = !((string)$this->element['translate_description'] == 'false' || (string)$this->element['translate_description'] == '0');

		// Set the group of the field.
		$this->group = $group;

		// Set the field name and id.
		$this->fieldname = $name;
		$this->name      = $this->getName($name, $group);
		$this->id        = $this->getId($id, $name, $group);
		$this->type      = $type;

		$this->class = $class;

		if ($panel_position != null) $this->panel_position = $panel_position;

		// Set the field default value.
		$this->value = $value;
		if (null != $this->default && $this->default != $this->value) $this->customized = true;
		return true;
	}


	/**
	 * @static
	 * @return void
	 */
	public static function initialize()
	{

	}

	/**
	 * @static
	 * @return void
	 */
	public static function finalize()
	{

	}

	/**
	 * @param  $callback
	 *
	 * @return mixed
	 */
	public function render($callback)
	{
		return call_user_func_array($callback, array($this));
	}
}
