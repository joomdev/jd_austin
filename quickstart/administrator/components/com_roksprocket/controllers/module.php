<?php
/**
 * @version     $Id: module.php 14559 2013-10-16 22:17:52Z btowles $
 * @author      RocketTheme http://www.rockettheme.com
 * @copyright   Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * based on
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

if (version_compare(JVERSION, '3.0', '<')) {
	abstract class RokSprocketControllerModuleIntermediate extends JControllerForm
	{
		protected function postSaveHook(JModel &$model, $validData = array())
		{
			$this->rsPostSaveHook($model,$validData);
		}

		abstract protected function rsPostSaveHook(RokSprocketModelModule $model, $validData = array());
	}
} else {
	abstract class RokSprocketControllerModuleIntermediate extends JControllerForm
	{
		protected function postSaveHook(JModelLegacy $model, $validData = array())
		{
			$this->rsPostSaveHook($model,$validData);
		}

		abstract protected function rsPostSaveHook(RokSprocketModelModule $model, $validData = array());
	}
}

/**
 * RokSprocket Module controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class RokSprocketControllerModule extends RokSprocketControllerModuleIntermediate
{
	/**
	 * Override parent add method.
	 *
	 * @throws Exception
	 * @return  mixed  True if the record can be added, a JError object if not.
	 *
	 * @since   1.6
	 */
	public function add()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the result of the parent method. If an error, just return it.
		$result = parent::add();
		if ($result instanceof Exception) {
			return $result;
		}

		// Look for the Extension ID.
		$extensionId = $app->input->get('eid', 0, 'int');
		if (empty($extensionId)) {
			$this->setRedirect(JRoute::_(sprintf('index.php?option=%s&view=modules',RokSprocket_Helper::getRedirectionOption()), false));
			throw new Exception(JText::_('COM_MODULES_ERROR_INVALID_EXTENSION', 500));
		}

		$app->setUserState('com_modules.add.module.extension_id', $extensionId);
		$app->setUserState('com_modules.add.module.params', null);

		// Parameters could be coming in for a new item, so let's set them.
		$params = $app->input->get('params', array(), 'array');
		$app->setUserState('com_modules.add.module.params', $params);
	}

	/**
	 * Override parent cancel method to reset the add module state.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel($key = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		$result = parent::cancel();

		$app->setUserState('com_modules.add.module.extension_id', null);
		$app->setUserState('com_modules.add.module.params', null);

		$this->setRedirect(JRoute::_(sprintf('index.php?option=%s&view=modules',RokSprocket_Helper::getRedirectionOption()), false));

		return $result;
	}

	/**
	 * Override parent allowSave method.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowSave($data, $key = 'id')
	{
		return parent::allowSave($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return    boolean  True on success.
	 *
	 * @since    1.7
	 */
	public function batch($model)
	{
		JSession::getFormToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Module', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_(sprintf('index.php?option=%s&view=modules',RokSprocket_Helper::getRedirectionOption()) . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModel  &$model     The data model object.
	 * @param   array   $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function rsPostSaveHook(RokSprocketModelModule $model, $validData = array())
	{
		// Initialise variables.
		$app  = JFactory::getApplication();
		$task = $this->getTask();

		switch ($task) {
			case 'save2new':
				$app->setUserState('com_modules.add.module.extension_id', $model->getState('module.extension_id'));
				break;
			case 'save':
				$app->setUserState('com_modules.add.module.extension_id', null);
				// Redirect to the list screen.
				$this->setRedirect(JRoute::_(sprintf('index.php?option=%s&view=modules',RokSprocket_Helper::getRedirectionOption()) . $this->getRedirectToListAppend(), false));
				break;
			case 'ajaxsave':

			default:
		}

		$app->setUserState('com_modules.add.module.params', null);
	}

	public function save($key = null, $urlVar = null)
	{
		$ret   = true;
		$app   = JFactory::getApplication();
		$input = $app->input;
		$task  = $input->get('task');

		if ($task == 'apply') {
			// Set up an independent AJAX error handler
			set_error_handler(array('RokCommon_Ajax', 'error_handler'));
			set_exception_handler(array('RokCommon_Ajax', 'exception_handler'));
		}

		$ret = parent::save($key);


		if ($task == 'apply') {
			$result = new RokCommon_Ajax_Result();
			if (!$ret) {
				$errors = $app->getMessageQueue();
				$result->setAsError();
				foreach ($errors as $error) {
					$result->setMessage($error['message']);
				}
			}
			else{
				$result->setPayload(array('module_id'=>$app->getUserState('com_roksprocket.module_id',0)));
				$app->setUserState('com_roksprocket.module_id',null);
			}
			$encoded_result = json_encode($result);
			// restore normal error handling;
			restore_error_handler();
			restore_exception_handler();
			echo $encoded_result;
		}
		return true;
	}

	/**
	 * Method to clone an existing module.
	 * @since	1.6
	 */
	public function duplicate()
	{
		// Check for request forgeries
        include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/legacy_class.php');
        roksprocket_checktoken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$input = $app->input;
		$pks  = $input->get('cid', array(), 'array');

		JArrayHelper::toInteger($pks);

		try {
			if (empty($pks)) {
				throw new Exception(JText::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
			}
			/** @var $model RokSprocketModelModule */
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::plural('COM_MODULES_N_MODULES_DUPLICATED', count($pks)));
		} catch (Exception $e) {
			JError::raiseWarning(500, $e->getMessage());
		}
		$this->setRedirect(sprintf('index.php?option=%s&view=modules',RokSprocket_Helper::getRedirectionOption()) . $this->getRedirectToListAppend(), false);
	}

	public function display($cachable = false, $urlparams = false)
	{
		//RokCommon_Session::clear('roksprocket.' . $data['uuid']);
		return parent::display($cachable, $urlparams); // TODO: Change the autogenerated stub
	}
}
