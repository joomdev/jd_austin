<?php
/**
 * @version   $Id: label.php 11757 2013-06-26 16:24:01Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

class JFormFieldLabel extends JFormField
{
	protected static $css_loaded = false;
	protected $type = 'Label';

	public function __construct($form = null)
	{
		parent::__construct($form);
		$this->container = RokCommon_Service::getContainer();
	}

	protected function getInput()
	{
		return ' ';
	}

	protected function getTitle()
	{
		return $this->getLabel();
	}

	protected function getLabel()
	{

		$this->_loadAssets();
		$html = array();

		$css_classes = explode(' ', (string)$this->element['class']);
		$css_classes = array_merge($css_classes, $this->getProviderClasses());
		$css_classes = array_unique($css_classes);
		$class       = implode(' ', $css_classes);

		//$class = $this->element['class'] ? (string) $this->element['class'] : '';

		$html[] = '<div class="spacer-wrapper ' . $class . '">';
		if ((string)$this->element['hr'] == 'true') {
			$html[] = '<hr class="' . $class . '" />';
		} else {
			$text = $this->element['label'] ? (string)$this->element['label'] : (string)$this->element['name'];
			$text = JText::_($text);

			$class = $this->required == true ? $class . ' required' : $class;


			$label  = '<h6>' . $text . '</h6>';
			$html[] = $label;
		}
		$html[] = '</div>';
		return implode('', $html);
	}

	public function _loadAssets()
	{
		if (!self::$css_loaded) {
			$type   = strtolower($this->type);
			$assets = JURI::root() . 'components/' . JFactory::getApplication()->input->getString('option') . '/fields/' . $type . '/';

			$css = $assets . 'css/' . $type . '.css';
			JFactory::getDocument()->addStyleSheet($css);

			self::$css_loaded = true;
		}
	}

	protected function getProviderClasses()
	{

		$provider_classes = array();
		$params           = $this->container['roksprocket.providers.registered'];

		foreach ($params as $provider_id => $provider_info) {
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
		}

		return $provider_classes;
	}
}

