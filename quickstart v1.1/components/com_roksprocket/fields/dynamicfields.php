<?php
/**
 * @version   $Id: dynamicfields.php 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldDynamicFields extends JFormFieldList
{
	protected static $js_loaded = false;
	protected $type = 'DynamicFields';
	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;

	public function __construct($form = null)
	{
		parent::__construct($form);
		$this->container = RokCommon_Service::getContainer();
	}

	protected function getLabel()
	{
		$label = $this->type;

		if (isset($this->element['label']) && !empty($this->element['label'])) {
			$label       = rc__((string)$this->element['label']);
			$description = rc__((string)$this->element['description']);
			return '<label class="sprocket-tip" title="' . $description . '">' . $label . '</label>';
		} else {
			return '';
		}

	}

	protected function getInput()
	{
		$fieldname = $this->element['name'];
		$js        = "RokSprocket.dynamicfields.add('" . $this->id . "', '" . $fieldname . "');";

		// Initialize variables.
		$html = array();
		$attr = '';

		$css_classes   = explode(' ', (string)$this->element['class']);
		$css_classes   = array_merge($css_classes, $this->getProviderClasses());
		$css_classes[] = strtolower($this->type);
		$css_classes[] = 'chzn-done';
		$css_classes   = array_unique($css_classes);
		$attr .= ' class="' . implode(' ', $css_classes) . '"';
		$attr .= ' data-chosen="skip"';

		// Initialize some field attributes.
//		$this->element['class'] = $this->element['class'] ? (string)$this->element['class'] . " " . strtolower($this->type) : strtolower($this->type);
//		$attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . ' chzn-done"' : ' class="chzn-done"';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string)$this->element['readonly'] == 'true' || (string)$this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		$attr .= $this->element['refresher'] ? ' data-refresher="true" ' : "";

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
		RokCommon_Header::addInlineScript($js);

		if ((string)$this->element['readonly'] == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		} // Create a regular list.
		else {
			if (count($options) == 1) {
				$icon = (isset($options[0]->icon) ? $options[0]->icon : "");
				if (strlen($icon)) $icon_html = '<i data-dynamic="false" class="icon ' . $this->element['name'] . " " . $options[0]->value . '"></i>'; else $icon_html = "";

				$html[] = '<div class="single-layout">' . $icon_html . ' ' . $options[0]->text . "</div>\n";
				$attr .= ' style="display: none;" ';
			}

			$listattr = array(
				'list.attr'    => $attr,
				'id'           => $this->id,
				'list.select'  => $this->value,
				'option.text'  => 'text',
				'option.value' => 'value',
				'option.attr'  => 'attr'
			);

			$list   = JHtml::_('select.genericlist', $options, $this->name, $listattr);
			$html[] = $list;
		}

		return implode('', $html);
	}

	protected function getProviderClasses()
	{

		$provider_classes = array();
		$params           = $this->container['roksprocket.providers.registered'];

		$form_wrapper = $this->container['roksprocket.form.wrapper.class'];
		$wrapper_form = new $form_wrapper($this->form);

		$provider_id = $wrapper_form->getData()->get('params')->provider;

		/** @var $provider RokSprocket_IProvider */
		$provider_class = $this->container[sprintf('roksprocket.providers.registered.%s.class', $provider_id)];
		$available      = call_user_func(array($provider_class, 'isAvailable'));
		if ($available) {
			if (call_user_func_array(array(
					$provider_class,
					'shouldShowField'
				), array(
					$this->type,
					$this->fieldname
				)) == RokSprocket_IProvider::ATTACH_TO_PROVIDER
			) {
				if (empty($provider_classes)) {
					$provider_classes[] = 'provider';
				}
				$provider_classes[] = 'provider_' . $provider_id;
			}
		}
		return $provider_classes;
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
				$tmp       = JHtml::_('select.option', $id, $info->displayname);
				$options[] = $tmp;
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		foreach ($options as &$option) {
			// Set some option attributes.
			$option->attr = array(
				'class' => $option->value,
				'rel'   => $fieldname . '_' . $option->value
			);
		}
		reset($options);
		return $options;
	}
}
