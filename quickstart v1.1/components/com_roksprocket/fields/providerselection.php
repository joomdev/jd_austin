<?php
/**
 * @version   $Id: providerselection.php 19225 2014-02-27 00:15:10Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

//require_once(dirname(__FILE__) . '/dynamicfields.php');

class JFormFieldProviderSelection extends JFormField
{
    protected $type = 'ProviderSelection';

	protected function getLabel()
	{
		return "";
	}


	protected function getInput()
	{
		return '<input id="'.$this->id.'" type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
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


        $configkey = (string)$this->element['configkey'];

        $options = array();

        $params = $container[$configkey];
	    $params = get_object_vars($params);
	  	ksort($params);

        foreach ($params as $provider_id => $provider_info) {
            /** @var $provider RokSprocket_IProvider */
            $provider_class   = $container[sprintf('roksprocket.providers.registered.%s.class', $provider_id)];
            $available        = call_user_func(array($provider_class, 'isAvailable'));
            if ($available) {
                //if ($this->value == $provider_id) $selected = ' selected="selected"'; else $selected = "";
                $tmp = JHtml::_('select.option', $provider_id, $provider_info->displayname);
                // Set some option attributes.
                $tmp->attr = array(
                    //'class'=> 'provider ' . $provider_id,
                    'rel'  => $fieldname . '_' . $provider_id
                );
                $tmp->icon = 'provider ' . $provider_id;
                $options[] = $tmp;
            }
        }

        reset($options);
        return $options;
    }
}
