<?php
/**
 * @version   $Id: roksprocket.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die;
include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/legacy_class.php');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_modules')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JTable::addIncludePath(JPATH_SITE.'/components/com_roksprocket/tables');

RokCommon_Composite::addPackagePath('roksprocket', JPATH_COMPONENT_ADMINISTRATOR . '/templates');
$controller = RokSprocketLegacyJController::getInstance('RokSprocket');
$app        = JFactory::getApplication();
$input      = $app->input;
$task       = $input->get('task');
$controller->execute($task);
if ($task == 'apply') {
	die;
} else {
	$controller->redirect();
}
