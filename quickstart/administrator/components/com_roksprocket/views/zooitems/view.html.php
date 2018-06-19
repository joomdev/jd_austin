<?php
/**
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
JHtml::_('behavior.tooltip');
include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/legacy_class.php');

/**
 *
 */
class RoksprocketViewZooItems extends RokSprocketLegacyJView
{
    /**
     * @var
     */
    protected $items;
    /**
     * @var
     */
    protected $pagination;
    /**
     * @var
     */
    protected $state;

    /**
     * @param null $tpl
     * @return bool
     */
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->authors = $this->get('Authors');
        $this->categories = $this->get('Categories');
        $this->applications = $this->get('Applications');
        $this->types = $this->get('Types');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        parent::display($tpl);
    }
}
