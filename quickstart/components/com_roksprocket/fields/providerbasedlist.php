<?php
/**
 * @version   $Id: providerbasedlist.php 10885 2013-05-30 06:31:41Z btowles $
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

class JFormFieldProviderBasedList extends JFormFieldDynamicFields
{
	protected $type = 'ProviderBasedList';



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

		$options = array();

		/** @var $provider RokSprocket_IProvider */
		$provider_class = $container[sprintf('roksprocket.providers.registered.%s.class', $provider)];
		$available      = call_user_func(array($provider_class, 'isAvailable'));
		if ($available) {

			if (method_exists($provider_class, $populator)) {
				$provider_options = call_user_func(array($provider_class, $populator));
				foreach ($provider_options as $provider_option_value => $provider_option_label) {
					//if ($this->value == $provider_option_value) $selected = ' selected="selected"'; else $selected = "";
					$tmp = JHtml::_('select.option', $provider_option_value, $provider_option_label);
					// Set some option attributes.
					$tmp->attr = array(
						//'class'=> sprintf('%s %s_%s', $controller, $controller, $provider_id),
						'rel'  => $fieldname . '_' . $provider_option_value
					);
					//$tmp->icon = 'provider ' . $provider_id;
					$options[] = $tmp;
				}
			}
		}

		reset($options);
		return $options;
	}
}
