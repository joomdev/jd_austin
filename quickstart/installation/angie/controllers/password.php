<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerPassword extends AController
{
	public function unlock()
	{
		$parts    = explode(':', AKEEBA_PASSHASH);
		$password = $this->input->get('password', '', 'raw');
		$passHash = md5($password . $parts[1]);

		$this->container->session->set('angie.passhash', $passHash);

		if ($passHash == $parts[0])
		{
			$this->container->session->saveData();
			$this->setRedirect('index.php?view=main');

			return;
		}

		$msg = AText::_('PASSWORD_ERR_INVALIDPASSWORD');
		$this->container->session->disableSave();
		$this->setRedirect('index.php?view=password', $msg, 'error');
	}
}
