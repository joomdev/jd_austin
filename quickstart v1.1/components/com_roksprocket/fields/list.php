<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldList extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'List';
	protected static $js_loaded = false;
	protected $options = array();

	public function __get($name)
	{
		switch ($name)
		{
			case 'class':
			case 'description':
			case 'formControl':
			case 'hidden':
			case 'id':
			case 'multiple':
			case 'name':
			case 'required':
			case 'type':
			case 'validate':
			case 'value':
			case 'fieldname':
			case 'group':
				return $this->$name;
				break;

			case 'input':
				// If the input hasn't yet been generated, generate it.
				if (empty($this->input))
				{
					$this->input = ($this->multiple || $this->element['readonly'] == 'true' || $this->element['disabled'] == 'true' ) ? $this->getInput() : $this->fancyDropDown($this->getInput());
				}

				return $this->input;
				break;

			case 'label':
				// If the label hasn't yet been generated, generate it.
				if (empty($this->label))
				{
					$this->label = $this->getLabel();
				}

				return $this->label;
				break;
			case 'title':
				return $this->getTitle();
				break;
		}

		return null;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		if (!self::$js_loaded){
			RokCommon_Header::addInlineScript($this->attachJavaScript());
			self::$js_loaded = true;
		}

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . ' chzn-done"' : ' class="chzn-done"';
		$attr .= ' data-chosen="skip"';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		if ($this->element['attrs']){
			$additional_attrs = explode(',',(string)$this->element['attrs']);
			foreach($additional_attrs as $additional_attr)
			{
				$additional_attr = strtolower(trim($additional_attr));
				$attr .= $this->element[$additional_attr] ? sprintf(' %s="',$additional_attr) . (string) $this->element[$additional_attr] . '"' : '';
			}
		}

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true' || $this->element['disabled'] == true)
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		// Create a regular list.
		else
		{
			$list = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = $list;
		}

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option)
		{

			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option['value'],
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];
			$tmp->divider = $option['divider'];
			$tmp->divider = empty($tmp->divider) ? false : true;

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}

	public function fancyDropDown($html){
		$list = $output = $displayText = $displayValue = "";
		$options = $this->getOptions();

		// if no options or just 1, something wrong, let's return the original html to be safe
		if (!count($options) || count($options) == 1) return $html;

		// cycling through options for the dropdown list and to get the selected option text to display

		foreach($options as $option){
			$class = (isset($option->class) ? $option->class : "") . (isset($option->disabled) ? " disabled" : "");
			$divider = (isset($option->divider)) ? $option->divider : false;
			$class = (strlen($class) ? 'class="'. $class . '"' : "");
			$icon = (isset($option->icon) ? $option->icon : "");

			if ($this->value == $option->value){
				$displayText = $option->text;
				$displayValue = $option->value;
			}

			if (strlen($icon)) $icon_html = '<i data-dynamic="false" class="icon '.$this->fieldname.' '.$option->value.'"></i>';
			else $icon_html = "";

			$class = (isset($option->attr['class']) && !empty($option->attr['class']))?' class="'.$option->attr['class'].'"':'';
			if ($divider) {
				$list .= '		<li class="divider" data-divider="true" data-dynamic="false" data-text="" data-value="">'."\n";
			} else {
				$list .= '		<li '.$class.' data-dynamic="false" data-icon="'.$icon.'" data-text="'.$option->text.'" data-value="'.$option->value.'">'."\n";
				$list .= '			<a href="#"'.$class.'>'.$icon_html.'<span>'.$option->text.'</span></a>'."\n";
			}
			$list .= '		</li>'."\n";
		}

		// rendering output
		$class = $this->fieldname . " " . $displayValue;

		$output .= '<div class="sprocket-dropdown">'."\n";
		$output .= '	<a href="#" class="btn dropdown-toggle" data-toggle="dropdown">'."\n";
		if (strlen($icon))
			$output .= '		<i data-dynamic="false" class="icon '.$class.'"></i> '."\n";
		$output .= '		<span>'.$displayText.'</span>'."\n";
		$output .= ' 		<span class="caret"></span>'."\n";
		$output .= '	</a>'."\n";
		$output .= '	<ul class="dropdown-menu">'."\n";

		$output .= $list;

		$output .= '	</ul>'."\n";
		$output .= '	<div class="dropdown-original">' . $html . '</div>'."\n";
		$output .= "</div>";
		return $output;
	}

	protected function getLabel()
	{
		$label = $this->type;

		if (isset($this->element['label']) && !empty($this->element['label']))
		{
			$label = rc__((string)$this->element['label']);
			$description = rc__((string)$this->element['description']);
			return '<label class="sprocket-tip" title="'.$description.'">'.$label.'</label>';
		} else {
			return;
		}

	}


    public function addOptions($options = array())
    {
        foreach ($options as $option)
        {
            $this->options[] = $option;
        }
    }

    public function addOption($option)
    {
        $this->options[] = $option;
    }

    public function setOptions($options = array())
    {
        $this->options = $options;
    }

	protected function attachJavaScript(){
		$js = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	if (typeof RokSprocket != 'undefined' && typeof RokSprocket.articles != 'undefined'){";
		$js[] = "		RokSprocket.articles.addEvent('onModelSuccess', function(response){";
		$js[] = "			RokSprocket.dropdowns.attach(document.getElements('.articles .dropdown-original select'));";
		$js[] = "		});";
		$js[] = "	};";
		$js[] = "});";

		return implode("\n", $js);
	}
}
