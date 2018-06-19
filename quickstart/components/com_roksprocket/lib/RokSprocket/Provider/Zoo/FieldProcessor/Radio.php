<?php
/**
 * @version   $Id: Radio.php 16047 2013-11-21 16:31:32Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_FieldProcessor_Radio extends RokSprocket_Provider_Zoo_FieldProcessor_Abstract
{
	public function getValue(Element $element)
	{
		$result = false;
		$data   = $element->data();

		if (is_array($data) && isset($data['option'])) {
			foreach ($data['option'] as $data_value) {
				foreach ($element->config->option as $object) {
					if ($object['value'] == $data_value) {
						$result = $object['name'];
						break 2;
					}
				}
			}
		}
		return $result;
	}
}
