<?php
/**
 * @version   $Id: multiselect.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;

class RokCommon_Form_Field_Multiselect extends RokCommon_Form_AbstractField
{
	protected $type = 'Multiselect';

	public function getInput()
	{

		//$document = JFactory::getDocument();
		//$document->addStyleSheet(substr(JURI::base(true), 0, -14) . '/templates/rt_voxel/admin/forms/fields/multiselect/css/multiselect.css');
		//$document->addScript(substr(JURI::base(true), 0, -14) . '/templates/rt_voxel/admin/forms/fields/tags/js/resizable-textbox.js');
		//$document->addScript(substr(JURI::base(true), 0, -14) . '/templates/rt_voxel/admin/forms/fields/multiselect/js/multiselect.js');

		$class = isset($this->element['class']) ? 'class="'.$this->element['class'].'"' : '';
		if (!is_array($this->value)) $this->value = explode(",", preg_replace("/\s/", "", $this->value));

		$output = array();
		$data = $this->_createOptions();

		$output[] = '<div class="multiselect" data-multiselect>';

		$output[] = '	<ul class="multiselect-holder" data-multiselect-holder>';
		$output[] = 		$data->html;
		$output[] = '		<li class="multiselect-input main-input"><input type="text" data-multiselect-maininput /></li>';
		$output[] = '	</ul>';

		$output[] = '	<select id="'.$this->id.'" name="'.$this->name.'[]" '.$class.' multiple="multiple" data-multiselect-select>';
		$output[] = 	$data->options;
		$output[] = '	</select>';

		$output[] = '	<div class="multiselect-feeds" data-multiselect-feeds>';
		$output[] = '		<ul data-multiselect-feed></ul>';
		$output[] = '	</div>';

		$output[] = '</div>';
		return implode("\n", $output);
	}

	protected function getOptions(){
		$options = array();

		foreach ($this->element->children() as $option){
			if ($option->getName() != 'option') continue;

			$tmp = RokCommon_HTML_SelectList::option((string) $option['value'],
                rc_alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			$tmp->class = (string) $option['class'];
			$options[] = $tmp;
		}

		reset($options);
		return $options;
	}

	protected function _createOptions(){
		$output = array(
			"options" => array(),
			"html" => array()
		);

		foreach($this->getOptions() as $index => $option){
			$class = isset($option->class) && strlen($option->class) ? ' class="'.$option->class.'" ' : '';
			$disabled = isset($option->disabled) && strlen($option->disabled) ? ' disabled="disabled" ' : '';
			$selected = in_array($option->value, $this->value) ? ' selected="selected" ' : '';

			$output["options"][] = '	<option value="'.$option->value.'"'.$class.$disabled.$selected.'>'.$option->text.'</option>';

			if (strlen($selected)){
				$output["html"][] = '	<li class="multiselect-box" data-multiselect-box="'.$option->value.'"><span class="multiselect-title">'. $option->text .'</span><span class="multiselect-remove" data-multiselect-remove>&times;</span></li>';
			}
		}

		$data = new stdClass;
		$data->options = implode("\n", $output['options']);
		$data->html = implode("\n", $output['html']);

		return $data;
	}
}
