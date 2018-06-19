<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewSession extends AView
{
	public function onBeforeMain()
	{
		$this->state = $this->getModel()->getStateVariables();
		$this->hasFTP = function_exists('ftp_connect');
		return true;
	}
}
