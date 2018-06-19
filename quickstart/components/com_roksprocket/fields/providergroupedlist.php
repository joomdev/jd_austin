<?php
/**
 * @version   $Id: providergroupedlist.php 10885 2013-05-30 06:31:41Z btowles $
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

class JFormFieldProviderGroupedList extends JFormFieldDynamicFields
{
	protected $type = 'ProviderGroupedList';


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
		$provider  = (string)$this->element['provider'];
		$populator = (string)$this->element['populator'];
		$controller = (string)$this->element['controller'];

		$options = array();

		/** @var $provider RokSprocket_IProvider */
		$provider_class = $container[sprintf('roksprocket.providers.registered.%s.class', $provider)];
		$available      = call_user_func(array($provider_class, 'isAvailable'));
		if ($available) {
			if (method_exists($provider_class, $populator)) {
				$provider_options = call_user_func(array($provider_class, $populator));

				foreach ($provider_options as $provider_option_value => $provider_option_data) {
					$provider_option_label = $provider_option_data['display'];
					$cck_grouping          = '';
					if (isset($provider_option_data['group'])) {
						$cck_grouping = sprintf('%s %s_%s', $controller, $controller, $provider_option_data['group']);
					}
					//if ($this->value == $provider_option_value) $selected = ' selected="selected"'; else $selected = "";
					$tmp = JHtml::_('select.option', $provider_option_value, $provider_option_label);
					// Set some option attributes.
					$tmp->attr = array(
						'class'=> $cck_grouping
					);
					//$tmp->icon = 'provider ' . $provider_id;
					$options[] = $tmp;
				}
			}
		}

		$defined_options = $this->getDefinedOptions();
		foreach ($defined_options as &$defined_option) {
			$defined_option->attr = array(
				'class'=> '',
				'rel'  => $fieldname . '_' . $defined_option->value
			);
		}

		$options = array_merge($defined_options, $options);

		reset($options);
		return $options;
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
