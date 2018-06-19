<?php
/**
 * @version   $Id: label.php 11776 2013-06-26 20:01:47Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2013 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;


class RokCommon_Form_Field_Label extends RokCommon_Form_AbstractField
{
	protected $type = 'Label';
	protected static $css_loaded = false;

	public function getInput()
	{
		return ' ';
	}

	public function getLabel()
	{
		$this->_loadAssets();
		$html = array();
		$css_classes = explode(' ', (string)$this->element['class']);
        $css_classes = array_merge($css_classes, $this->getProviderClasses());
        $css_classes = array_unique($css_classes);
        $class       = implode(' ', $css_classes);


		$html[] = '<div id="'.$this->id.'" class="spacer-wrapper '.$class.'">';
		if ((string) $this->element['hr'] == 'true') {
			$html[] = '<hr class="'.$class.'" />';
		}
		else {
			$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
			$text = rc__($text);

			$class = $this->required == true ? $class.' required' : $class;


			$label = '<h6>'.$text.'</h6>';
			$html[] = $label;
		}
		$html[] = '</div>';
		return implode('',$html);
	}

	public function getTitle()
	{
		return $this->getLabel();
	}

	public function _loadAssets(){
		if (!self::$css_loaded){
			$type = strtolower($this->type);
			/** @var $header RokCommon_IHeader */
			$header = $this->container->getService('header');
			$header->addStyle(RokCommon_Composite::get($this->assets_content.'.'.$type.'.css')->getUrl($type.'.css'));
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
}					$provider_classes[] = 'provider_' . $provider_id;
				}
			}
		}

		return $provider_classes;
	}
}

