<?php
/**
 * @version   $Id: controller.php 11852 2013-06-28 21:17:33Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/legacy_class.php');


/**
 * rokgallery Controller
 *
 * @package    Joomla
 * @subpackage rokgallery
 */
class RokSprocketController extends RokSprocketLegacyJController
{
    /**
     * Constructor
     * @access     private
     * @subpackage rokgallery
     */
    function __construct()
    {
        $app   = JFactory::getApplication();
        $input = $app->input;

        //Get View
        if ($input->get('view') == '') {
            $input->set('view', 'default');
        }
        $this->item_type = 'Default';
        parent::__construct();
    }

    /**
     * Method to display a view.
     *
     * @param bool $cachable
     * @param bool $urlparams
     *
     * @internal param \If $boolean true, the view output will be cached
     * @internal param \An $array array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return    JController        This object to support chaining.
     * @since    1.5
     */
   	public function display($cachable = false, $urlparams = false)
   	{
   		require_once JPATH_ADMINISTRATOR.'/components/com_modules/helpers/modules.php';

   		// Load the submenu.
   		ModulesHelper::addSubmenu(JFactory::getApplication()->input->getCmd('view', 'modules'));

   		$view		= JFactory::getApplication()->input->getCmd('view', 'modules');
   		$layout 	= JFactory::getApplication()->input->getCmd('layout', 'default');
   		$id			= JFactory::getApplication()->input->getInt('id', null);

   		// Check for edit form.
   		if ($view == 'module' && $layout == 'edit' && !$this->checkEditId('com_roksprocket.edit.module', $id)) {
   			// Somehow the person just went to the form - we don't allow that.
   			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
   			$this->setMessage($this->getError(), 'error');
   			$this->setRedirect(JRoute::_(sprintf('index.php?option=%s&view=modules',RokSprocket_Helper::getRedirectionOption()), false));

   			return false;
   		}
	    if (RokCommon_Session::get('roksprocket.' . $id, false)){
	        RokCommon_Session::clear('roksprocket.' . $id);
	    }
   		parent::display();
   	}

    /**
     *
     */
    public function ajax()
    {
        try {
            $app   = JFactory::getApplication();
            $input = $app->input;

            RokCommon_Ajax::addModelPath(JPATH_SITE . '/components/com_roksprocket/lib/RokSprocket/Admin/Ajax/Model',
                'RokSprocketAdminAjaxModel');
            $model  = $input->getWord('model', null);
            $action = $input->getWord('model_action', null);
            if (isset($_REQUEST['params'])) {
                $params = $this->smartstripslashes($_REQUEST['params']);
            }
            echo RokCommon_Ajax::run($model, $action, $params);
        } catch (Exception $e) {
            $result = new RokCommon_Ajax_Result();
            $result->setAsError();
            $result->setMessage($e->getMessage());
            echo json_encode($result);
        }
    }

    /**
     * @param $str
     *
     * @return string
     */
    protected function smartstripslashes($str)
    {
        $cd1 = substr_count($str, "\"");
        $cd2 = substr_count($str, "\\\"");
        $cs1 = substr_count($str, "'");
        $cs2 = substr_count($str, "\\'");
        $tmp = strtr($str, array(
                                "\\\""  => "",
                                "\\'"   => ""
                           ));
        $cb1 = substr_count($tmp, "\\");
        $cb2 = substr_count($tmp, "\\\\");
        if ($cd1 == $cd2 && $cs1 == $cs2 && $cb1 == 2 * $cb2) {
            return strtr($str, array(
                                    "\\\""  => "\"",
                                    "\\'"   => "'",
                                    "\\\\"  => "\\"
                               ));
        }
        return $str;
    }
}
