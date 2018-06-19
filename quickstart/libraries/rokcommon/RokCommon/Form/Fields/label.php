<?php
/**
 * @version   $Id: label.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
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
		$class = $this->element['class'] ? (string) $this->element['class'] : '';

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
}