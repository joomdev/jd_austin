<?php
/**
 * @version   $Id: Select.php 13544 2013-09-16 20:14:06Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_FieldProcessor_Select extends RokSprocket_Provider_Zoo_FieldProcessor_Abstract
{
	public function getValue(Element $element)
	{
		$result = null;
		$data = $element->data();

		foreach ($data['option'] as $data_value) {
			foreach ($element->config->option as $object) {
				if ($object['value'] == $data_value) {
					$result = $object['name'];
					break 2;
				}
			}
		}
		return $result;
	}
}
