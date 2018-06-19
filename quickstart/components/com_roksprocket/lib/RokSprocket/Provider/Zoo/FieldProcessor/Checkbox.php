<?php
/**
 * @version   $Id: Checkbox.php 13467 2013-09-13 23:41:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_FieldProcessor_Checkbox extends RokSprocket_Provider_Zoo_FieldProcessor_Abstract
{
	public function getValue(Element $element)
	{
		$result = null;
		$data = $element->data();

		foreach ($data['option'] as $data_value) {
			foreach ($element->config->option as $object) {
				if ($object['value'] == $data_value) {
					$result = $object;
					break 2;
				}
			}
		}
		return $result;
	}
}
