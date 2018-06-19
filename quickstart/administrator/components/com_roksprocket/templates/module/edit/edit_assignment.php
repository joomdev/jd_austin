<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$version = new JVersion();

if (version_compare($version->getShortVersion(), '3.0', '>=')) {
    echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_assignment_30.php', array('that'=>$that));
} else {
    echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_assignment_25.php', array('that'=>$that));
}