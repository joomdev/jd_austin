<?php
/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewMain extends AView
{
	/** @var   array  Required settings */
	public $reqSettings = array();

	/** @var   bool  Are the required settings met? */
	public $reqMet = false;

	/** @var   array  Recommended settings */
	public $recommendedSettings = array();

	/** @var   array  Extra information about the backup */
	public $extraInfo = array();

	/** @var   string  Detected Joomla! version. Only used by ANGIE for Joomla!. */
	public $joomlaVersion = '0.0.0';

	/** @var   string  Version of the platform (application being restored). Used by all other ANGIE installers. */
	public $version = '0.0.0';

	public function onBeforeMain()
	{
		if ($this->input->get('layout') != 'init')
		{
			return true;
		}

		/** @var AngieModelBaseMain $model */
		$model = $this->getModel();

		/** @var ASession $session */
		$session = $this->container->session;

		// Assign the results of the various checks
		$this->reqSettings         = $model->getRequired();
		$this->reqMet              = $model->isRequiredMet();
		$this->recommendedSettings = $model->getRecommended();
		$this->extraInfo           = $model->getExtraInfo();
		$this->joomlaVersion       = $session->get('jversion');
		$this->version             = $session->get('version');

		// Am I restoring to a different site?
		$this->restoringToDifferentHost = false;

		if (isset($this->extraInfo['host']))
		{
			$uri                            = AUri::getInstance();
			$this->restoringToDifferentHost = $this->extraInfo['host']['current'] != $uri->getHost();
		}

		// If I am restoring to a different host blank out the database
		// connection information to prevent unpleasant situations, like a user
		// "accidentally" overwriting his original site's database...
		if ($this->restoringToDifferentHost && !$session->get('main.resetdbinfo', false))
		{
			$model->resetDatabaseConnectionInformation();
		}

		return true;
	}
}
