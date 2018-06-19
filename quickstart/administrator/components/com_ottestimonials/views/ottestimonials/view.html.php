<?php
/*------------------------------------------------------------------------
# view.html.php - OT Testimonials Component
# ------------------------------------------------------------------------
# author    Vishal Dubey
# copyright Copyright (C) 2014 OurTeam. All Rights Reserved
# license   GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
# website   www.ourteam.co.in
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * ottestimonials View
 */
class OttestimonialsViewottestimonials extends JViewLegacy
{
	/**
	 * Ottestimonials view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Include helper submenu
		OttestimonialsHelper::addSubmenu('ottestimonials');

		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		};

		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;

		// Set the toolbar
		$this->addToolBar();
		// Show sidebar
		$this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$canDo = OttestimonialsHelper::getActions();
		JToolBarHelper::title(JText::_('Ottestimonials Manager'), 'ottestimonials');
		if($canDo->get('core.create')){
			JToolBarHelper::addNew('ottestimonial.add', 'JTOOLBAR_NEW');
		};
		if($canDo->get('core.edit')){
			JToolBarHelper::editList('ottestimonial.edit', 'JTOOLBAR_EDIT');
		};
		if($canDo->get('core.delete')){
			JToolBarHelper::deleteList('', 'ottestimonials.delete', 'JTOOLBAR_DELETE');
		};
		if($canDo->get('core.admin')){
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_ottestimonials');
		};
        JToolBarHelper::publishList($task = 'ottestimonials.publish', $alt = 'Publish');
        JToolBarHelper::unpublishList($task = 'ottestimonials.unpublish', $alt = 'Unpublish');
      }
	/**
	 * Method to set up the document properties
	 *
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('Ottestimonials Manager - Administrator'));
	}
}
?>