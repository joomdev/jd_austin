<?php
/**
 * @version   $Id: containerlist.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

class RokCommon_Form_Field_ContainerList extends RokCommon_Form_Field_List
{
	protected $type = 'ContainerList';

	public function getLabel()
	{
		$label = $this->type;

		if (isset($this->element['label']) && !empty($this->element['label'])) {
			$label       = rc__((string)$this->element['label']);
			$description = rc__((string)$this->element['description']);
			return '<label class="sprocket-tip" title="' . $description . '">' . $label . '</label>';
		} else {
			return;
		}

	}

	public function getInput()
	{
		$fieldname = $this->element['name'];

		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$this->element['class'] = $this->element['class'] ? (string)$this->element['class'] . " " . strtolower($this->type) : strtolower($this->type);
		$attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string)$this->element['readonly'] == 'true' || (string)$this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

		if ($this->element['attrs']) {
			$additional_attrs = explode(',', (string)$this->element['attrs']);
			foreach ($additional_attrs as $additional_attr) {
				$additional_attr = strtolower(trim($additional_attr));
				$attr .= $this->element[$additional_attr] ? sprintf(' %s="', $additional_attr) . (string)$this->element[$additional_attr] . '"' : '';
			}
		}

		// Get the field options.
		$options = (array)$this->getOptions();

		if ((string)$this->element['readonly'] == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		} // Create a regular list.
		else {
			if (count($options) == 1) {
				$html[] = '<div class="single-layout"> ' . $options[0]->text . "</div>\n";
				$attr .= ' style="display: none;" ';
			}

			$listattr = array(
				'list.attr'   => $attr,
				'id'          => $this->id,
				'list.select' => $this->value,
				'option.text' => 'text',
				'option.value'=> 'value',
				'option.attr' => 'attr'
			);

			$list   = RokCommon_HTML_SelectList::genericlist($options, $this->name, $listattr);
			$html[] = $list;
		}

		return implode('', $html);
	}


	/**
	 * Method to get the field options for the list of installed editors.
	 *
	 * @return  array  The field option objects.
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$container = RokCommon_Service::getContainer();

		$fieldname = $this->element['name'];
		$options   = array();
		if (isset($this->element['configkey'])) {
			$configkey = (string)$this->element['configkey'];
			$params    = $container[$configkey];

			foreach ($params as $id => $info) {
				if ($this->value == $id) $selected = ' selected="selected"'; else $selected = "";
				$tmp       = RokCommon_HTML_SelectList::option($id, $info->displayname);
				$options[] = $tmp;
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		foreach ($options as &$option) {
			// Set some option attributes.
			$option->attr = array(
				'class'=> $option->value,
				'rel'  => $fieldname . '_' . $option->value
			);
		}
		reset($options);
		return $options;
	}
}
