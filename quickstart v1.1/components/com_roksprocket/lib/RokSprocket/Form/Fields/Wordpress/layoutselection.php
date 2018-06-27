<?php
/**
 * @version   $Id: layoutselection.php 19225 2014-02-27 00:15:10Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Form_Field_LayoutSelection extends RokCommon_Form_AbstractField
{

	protected static $loaded_icons = array();

	protected $type = 'LayoutSelection';

	public function getLabel()
	{
		return "";
	}


	public function getInput()
	{
		return '<input id="'.$this->id.'" type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
	}
}
