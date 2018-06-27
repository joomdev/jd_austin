<?php

/**
 * @version   $Id: RokCommon.php 19112 2014-02-25 23:05:38Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokSprocket_Form_Wrapper_RokCommon extends RokCommon_Form
{

	protected $form;

	public function __construct($form)
	{
		$this->form = $form;
		foreach(get_object_vars($form) as $param => $value)
		{
			$this->$param = $value;
		}
	}

	public function getData()
	{
		return $this->form->data;
	}
}
 