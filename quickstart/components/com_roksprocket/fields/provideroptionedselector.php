<?php
/**
 * @version   $Id: provideroptionedselector.php 19225 2014-02-27 00:15:10Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

require_once(dirname(__FILE__) . '/dynamicfields.php');

class JFormFieldProviderOptionedSelector extends JFormFieldDynamicFields
{
	protected static $cck_group_controls;
	protected $type = 'ProviderOptionedSelector';

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


		$configkey  = (string)$this->element['configkey'];
		$controller = (string)$this->element['controller'];
		$populator  = (string)$this->element['populator'];

		$cck_group_control = $this->getCCKGroupControls();


		$options = array();

		$params = $container[$configkey];

		$form_wrapper = $this->container['roksprocket.form.wrapper.class'];
		$wrapper_form = new $form_wrapper($this->form);

		$provider_id   = $wrapper_form->getData()->get('params')->provider;
		$provider_info = $this->container["roksprocket.providers.registered.{$provider_id}"];

		//foreach ($params as $provider_id => $provider_info) {
		/** @var $provider RokSprocket_IProvider */
		$provider_class = $container[sprintf('roksprocket.providers.registered.%s.class', $provider_id)];
		$available      = call_user_func(array($provider_class, 'isAvailable'));
		if ($available) {

			if (method_exists($provider_class, $populator)) {
				$provider_options = call_user_func(array($provider_class, $populator));

				foreach ($provider_options as $provider_option_value => $provider_option_data) {
					$provider_option_label = $provider_option_data['display'];
					$cck_grouping          = '';
					if (isset($cck_group_control[$provider_id]) && isset($provider_option_data['group'])) {
						$cck_grouping = sprintf('%s %s_%s', $cck_group_control[$provider_id], $cck_group_control[$provider_id], $provider_option_data['group']);
					}
					//if ($this->value == $provider_option_value) $selected = ' selected="selected"'; else $selected = "";
					$tmp = JHtml::_('select.option', $provider_info->name . '_' . $provider_option_value, $provider_option_label);
					// Set some option attributes.
					$tmp->attr = array(
						'class' => sprintf('%s %s_%s %s', $controller, $controller, $provider_id, $cck_grouping),
						'rel'   => $fieldname . '_' . $provider_info->name . '_' . $provider_option_value
					);
					//$tmp->icon = 'provider ' . $provider_id;
					$options[] = $tmp;
				}
			}
		}
		//}

		$defined_options = $this->getDefinedOptions();
		foreach ($defined_options as &$defined_option) {
			$defined_option->attr = array(
				'class' => '',
				'rel'   => $fieldname . '_' . $defined_option->value
			);
		}

		$options = array_merge($defined_options, $options);

		reset($options);
		return $options;
	}

	protected function getCCKGroupControls()
	{
		if (!isset(self::$cck_group_controls)) {
			self::$cck_group_controls = array();
			$fields                   = $this->form->getFieldset('roksprocket');
			foreach ($fields as $field) {
				if (strtolower($this->form->getFieldAttribute($field->fieldname, 'cckgroup', 'false', 'params')) == 'true' && ($provider = $this->form->getFieldAttribute($field->fieldname, 'provider', false, 'params'))) {
					self::$cck_group_controls[strtolower($provider)] = $field->fieldname;
				}
			}
		}
		return self::$cck_group_controls;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getDefinedOptions()
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option) {

			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', (string)$option['value'], JText::alt(trim((string)$option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text', ((string)$option['disabled'] == 'true'));

			// Set some option attributes.
			$tmp->class = (string)$option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string)$option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
