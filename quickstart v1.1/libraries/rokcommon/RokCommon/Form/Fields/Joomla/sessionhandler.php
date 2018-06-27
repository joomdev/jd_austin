<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

RokCommon_Form_Helper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of session handler options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class RokCommon_Form_Field_SessionHandler extends RokCommon_Form_Field_List
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'SessionHandler';

	/**
	 * Method to get the session handler field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Get the options from JSession.
		foreach (JSession::getStores() as $store)
		{
			$options[] = RokCommon_HTML_SelectList::option($store, rc__('JLIB_FORM_VALUE_SESSION_' . $store), 'value', 'text');
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
