<?php
/**
 * @version   $Id: Image.php 13721 2013-09-24 16:46:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


class RokSprocket_Provider_Zoo_FieldProcessor_Image extends RokSprocket_Provider_Zoo_FieldProcessor_Basic implements RokSprocket_Provider_Zoo_ImageFieldProcessorInterface
{
	/**
	 * @param Element $element
	 *
	 * @return RokSprocket_Item_Image
	 */
	public function getAsSprocketImage(Element $element)
	{
		$image_field_data = $this->getValue($element, true);
		$image            = new RokSprocket_Item_Image();
		$image->setIdentifier('image_field_' . $element->identifier);
		if (isset($image_field_data['file']) && !is_array($image_field_data['file'])) $image->setSource(JUri::base(false) . $image_field_data['file']);
		$image->setCaption((isset($image_field_data['title']) && !is_array($image_field_data['title'])) ? $image_field_data['title'] : '');
		$image->setAlttext((isset($image_field_data['title']) && !is_array($image_field_data['title'])) ? $image_field_data['title'] : '');
		return $image;
	}

}
