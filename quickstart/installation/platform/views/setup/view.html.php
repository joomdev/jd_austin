<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewSetup extends AView
{
	public $stateVars   =   null;
	public $hasFTP      =   true;

	/**
	 * Are we running under Apache webserver?
	 *
	 * @var bool
	 */
	public $htaccessSupported  =   false;

	/**
	 * Are we running under NGINX webserver?
	 *
	 * @var bool
	 */
	public $nginxSupported     =   false;

	/**
	 * Are we running under IIS webserver?
	 *
	 * @var bool
	 */
	public $webConfSupported   =   false;

	public $removePhpiniOptions    = array();
	public $replaceHtaccessOptions = array();
	public $replaceWeconfigOptions = array();
	public $removeHtpasswdOptions  = array();

	public function onBeforeMain()
	{
		/** @var AngieModelJoomlaSetup $model */
		$model           = $this->getModel();
		$this->stateVars = $model->getStateVariables();
		$this->hasFTP    = function_exists('ftp_connect');

		$this->htaccessSupported = AUtilsServertechnology::isHtaccessSupported();
		$this->nginxSupported    = AUtilsServertechnology::isNginxSupported();
		$this->webConfSupported  = AUtilsServertechnology::isWebConfigSupported();

		// Prime the options array with some default info
		$this->removePhpiniOptions = array(
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEPHPINI_HELP'
		);

		$this->replaceHtaccessOptions = array(
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REPLACEHTACCESS_HELP'
		);

		$this->replaceWeconfigOptions = array(
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REPLACEWEBCONFIG_HELP'
		);

		$this->removeHtpasswdOptions = array(
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEHTPASSWD_HELP'
		);

		// If we are restoring to a new server everything is checked by default
		if ($model->isNewhost())
		{
			$this->removePhpiniOptions['checked']    = 'checked="checked"';
			$this->replaceHtaccessOptions['checked'] = 'checked="checked"';
			$this->replaceWeconfigOptions['checked'] = 'checked="checked"';
			$this->removeHtpasswdOptions['checked']  = 'checked="checked"';
		}

		// If any option is not valid (ie missing files) we gray out the option AND remove the check
		// to avoid user confusion
		if (!$model->hasPhpIni())
		{
			$this->removePhpiniOptions['disabled']   = 'disabled="disabled"';
			$this->removePhpiniOptions['checked']    = '';
			$this->removePhpiniOptions['help']       = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasHtaccess())
		{
			$this->replaceHtaccessOptions['disabled'] = 'disabled="disabled"';
			$this->replaceHtaccessOptions['checked']  = '';
			$this->replaceHtaccessOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasWebconfig())
		{
			$this->replaceWeconfigOptions['disabled'] = 'disabled="disabled"';
			$this->replaceWeconfigOptions['checked']  = '';
			$this->replaceWeconfigOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasHtpasswd())
		{
			$this->removeHtpasswdOptions['disabled'] = 'disabled="disabled"';
			$this->removeHtpasswdOptions['checked']  = '';
			$this->removeHtpasswdOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		return true;
	}
}
