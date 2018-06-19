<?php
/**
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$lang = JFactory::getLanguage();
$lang->load('com_modules', JPATH_ADMINISTRATOR, $lang->getDefault(), true);
$lang->load('com_modules', JPATH_ADMINISTRATOR, $lang->getTag(), true);
$lang->load('com_roksprocket', JPATH_COMPONENT, $lang->getDefault(), true);
$lang->load('com_roksprocket', JPATH_COMPONENT, $lang->getTag(), true);
echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit.php', array('that'=>$this));