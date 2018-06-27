<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class JFormFieldModuleOrder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ModuleOrder';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		$html[] = '<script type="text/javascript">';

		$ordering = $this->form->getValue('ordering');
		$position = $this->form->getValue('position');
		$clientId = $this->form->getValue('client_id');

		$html[] = 'var originalOrder = "'.$ordering.'";';
		$html[] = 'var originalPos = "'.$position.'";';
		$html[] = 'var orders = new Array();';

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('position, ordering, title');
		$query->from('#__modules');
		$query->where('client_id = '.(int) $clientId);
		$query->where('published > -1');
		$query->order('ordering');

		$db->setQuery($query);
		$orders = $db->loadObjectList();
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		$orders2 = array();
		for ($i = 0, $n = count($orders); $i < $n; $i++) {
			if (!isset($orders2[$orders[$i]->position])) {
				$orders2[$orders[$i]->position] = 0;
			}
			$orders2[$orders[$i]->position]++;
			$ord = $orders2[$orders[$i]->position];
			$title = JText::sprintf('COM_MODULES_OPTION_ORDER_POSITION', $ord, addslashes($orders[$i]->title));

			$html[] = 'orders['.$i.'] =  new Array("'.$orders[$i]->position.'","'.$ord.'","'.$title.'");';
		}

		$html[] = 'writeDynaList(\'name="'.$this->name.'" id="'.$this->id.'"'.$attr.'\', orders, originalPos, originalPos, originalOrder);';
		$html[] = "window.addEvent('domready', function(){ RokSprocket.dropdowns.redraw(document.id('".$this->id."')); });";
		$html[] = '</script>';
		// $list = JHtml::_('select.genericlist', $this->getOptions(), $this->name, $attr, 'value', 'text', $this->value, $this->id);
		// $html[]   = $list;

		return $this->fancyDropDown(implode($html));
	}

	protected function getOptions(){
		$clientId = $this->form->getValue('client_id');

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('position, ordering, title');
		$query->from('#__modules');
		$query->where('client_id = '.(int) $clientId);
		$query->where('published > -1');
		$query->order('ordering');

		$db->setQuery($query);
		$orders = $db->loadObjectList();
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		$orders2 = array();
		$options = array();
		for ($i = 0, $n = count($orders); $i < $n; $i++) {
			if (!isset($orders2[$orders[$i]->position])) {
				$orders2[$orders[$i]->position] = 0;
			}
			$orders2[$orders[$i]->position]++;
			$ord = $orders2[$orders[$i]->position];
			$title = JText::sprintf('COM_MODULES_OPTION_ORDER_POSITION', $ord, addslashes($orders[$i]->title));

			if (!strlen($orders[$i]->position)){
				$data = new stdClass();
				$data->value = $ord;
				$data->text = $title;
				$options[] = $data;
				//$html[] = 'orders['.$i.'] =  new Array("'.$orders[$i]->position.'","'.$ord.'","'.$title.'");';
			}
		}

		return $options;
	}

	public function fancyDropDown($html){
		$list = $output = $displayText = $displayValue = "";
		$options = $this->getOptions();

		// if no options or just 1, something wrong, let's return the original html to be safe
		//if (!count($options) || count($options) == 1) return $html;

		// cycling through options for the dropdown list and to get the selected option text to display

		foreach($options as $option){
			$class = (isset($option->class) ? $option->class : "") . (isset($option->disabled) ? " disabled" : "");
			$class = (strlen($class) ? 'class="'. $class . '"' : "");
			$icon = (isset($option->icon) ? $option->icon : "");

			if ($this->value == $option->value){
				$displayText = $option->text;
				$displayValue = $option->value;
			}

			if (strlen($icon)) $icon_html = '<i data-dynamic="false" class="icon '.$this->fieldname.' '.$option->value.'"></i>';
			else $icon_html = "";

			$list .= '		<li '.$class.' data-dynamic="false" data-icon="'.$icon.'" data-text="'.$option->text.'" data-value="'.$option->value.'">'."\n";
			$list .= '			<a href="#">'.$icon_html.'<span>'.$option->text.'</span></a>'."\n";
			$list .= '		</li>'."\n";
		}

		// rendering output
		$class = $this->fieldname . " " . $displayValue;

		$output .= '<div class="sprocket-dropdown">'."\n";
		$output .= '	<a href="#" class="btn dropdown-toggle" data-toggle="dropdown">'."\n";
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
}
