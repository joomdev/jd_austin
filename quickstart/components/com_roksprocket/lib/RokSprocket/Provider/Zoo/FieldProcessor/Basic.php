<?php
/**
 * @version   $Id: Basic.php 13745 2013-09-24 21:47:20Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


class RokSprocket_Provider_Zoo_FieldProcessor_Basic extends RokSprocket_Provider_Zoo_FieldProcessor_Abstract
{
	public function getValue(Element $element, $all_array_data = false)
	{
		$can_repeat = $this->canRepeat($element);
		$data       = $element->data();
		$result     = null;
		if (!empty($data)) {
			if ($can_repeat) {
				$data = $data[0];
			}

			$value_container = RokSprocket_Provider_Zoo_FieldProcessorFactory::getValueContainer($element->getElementType());
			if (!$all_array_data && array_key_exists($value_container, $data) && is_string($data[$value_container])) {
				$result = $data[$value_container];
			} elseif ($all_array_data) {
				$result = $data;
			}
		}
		return $result;
	}
}
