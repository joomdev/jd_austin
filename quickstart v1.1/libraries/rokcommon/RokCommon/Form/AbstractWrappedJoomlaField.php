<?php
/**
 * @version   $Id: AbstractWrappedJoomlaField.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokCommon_Form_AbstractWrappedJoomlaField extends RokCommon_Form_AbstractField
{
	/** @var JFormField */
	protected $joomla_field;

	public function setup(&$element, $value, $group = null)
	{
		parent::setup($element, $value, $group);
		$this->joomla_field = new RokCommon_Form_JoomlaFieldWrapper($element, $group, $value, $this->name, $this->id);
		if ($this->joomla_field === false) {
			return false;
		}
		$this->joomla_field->setRokCommonForm($this->form);
		return true;
	}


	public function getInput()
	{
		return $this->joomla_field->input;
	}
}

