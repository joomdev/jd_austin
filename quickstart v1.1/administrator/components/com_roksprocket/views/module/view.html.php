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
include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/legacy_class.php');

/**
 * View to edit a module.
 *
 * @static
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @since          1.6
 */
class RokSprocketViewModule extends RokSprocketLegacyJView
{
	/**
	 * @var JForm
	 */
	public $form;
	public $item;
	public $state;
	public $container;
	public $layout;
	public $provider;

	/** @var RokSprocket_Layout_Lists */
	public $articles;
	public $perItemForm;
	public $showitems;

	public $total_articles;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

        JHtml::_('behavior.framework', true);
		JHtml::_('behavior.keepalive');

		$this->container   = RokCommon_Service::getContainer();

		$this->form        = $this->get('Form');
		$this->item        = $this->get('Item');
		$this->state       = $this->get('State');
		$this->articles    = $this->getModel()->getArticles($this->item->id, $this->item->params);
		$this->layout      = (isset($this->item->params['layout'])) ? $this->item->params['layout'] : $this->form->getFieldAttribute('layout', 'default', 'text', 'params');
		$this->provider    = (isset($this->item->params['provider'])) ? $this->item->params['provider'] : $this->form->getFieldAttribute('provider', 'default', 'text', 'params');
		if (!isset($this->container[sprintf('roksprocket.layouts.%s', $this->layout)]))
		{
			JError::raiseWarning(500, rc__(ROKSPROCKET_UNABLE_TO_FIND_LAYOUT_ERROR,$this->layout));
			$app = JFactory::getApplication();
			$app->redirect(JRoute::_(sprintf('index.php?option=%s&view=modules', RokSprocket_Helper::getRedirectionOption()), false));
			return false;
		}
		$this->perItemForm = $this->getModel()->getPerItemsForm($this->layout);

		/** @var $i18n RokCommon_I18N */
		/** @var $i18n RokCommon_I18N */
		$i18n = $this->container->i18n;

		foreach ($this->container['roksprocket.layouts'] as $layout_type => $layoutinfo) {
			$layout_lang_paths = $this->container[sprintf('roksprocket.layouts.%s.paths', $layout_type)];
			foreach ($layout_lang_paths as $lang_path) {
				@$i18n->loadLanguageFiles('roksprocket_layout_' . $layout_type, $lang_path);
			}
		}

		$load_more_total = count($this->articles);
		$module_params   = new RokCommon_Registry($this->item->params);
		$limit           = 10;

		if ($load_more_total > $limit) {
			$this->articles = $this->articles->trim($limit);
			$load_more      = 'true';
		} else {
			$load_more = 'false';
		}

		$load_more_script = sprintf('RokSprocket.Paging = {more: %s, page: 1, next_page: 2, amount: %d};', $load_more, $load_more_total);


		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Read cookie for showing/hide per-article items
		if (!isset($_COOKIE['roksprocket-showitems'])) {
			$showitems_cookie = 1;
			setcookie("roksprocket-showitems", $showitems_cookie, time() + 60 * 60 * 24 * 365, '/');
		} else {
			$showitems_cookie = $_COOKIE['roksprocket-showitems'];
		}

		$this->showitems = (bool)$showitems_cookie;

		$siteURL  = JURI::root(true);
		$adminURL = JURI::base(true);

		$this->addToolbar();
		$this->compileLess();
		$this->compileJS();

		RokCommon_Header::addInlineScript("RokSprocket.params = 'jform_params';RokSprocket.SiteURL = '" . $siteURL . "'; RokSprocket.AdminURL = '" . $adminURL . "'; RokSprocket.URL = RokSprocket.AdminURL + '/index.php?option=" . JFactory::getApplication()->input->getString('option') . "&task=ajax&format=raw';" . $load_more_script);
		RokCommon_Header::addStyle($siteURL . '/components/com_roksprocket/fields/filters/css/datepicker.css');

		$template_path_param = sprintf('roksprocket.providers.registered.%s.templatepath',strtolower($this->provider));
		if ($this->container->hasParameter($template_path_param))
		{
			RokCommon_Composite::addPackagePath('roksprocket', $this->container->getParameter($template_path_param), 30);
		}
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user       = JFactory::getUser();
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		if (method_exists('ModulesHelper','getActions')){
			$canDo = ModulesHelper::getActions($this->state->get('filter.category_id'), $this->item->id);
		}
		else {
			$canDo = JHelperContent::getActions('com_modules','',$this->item->id);
		}
		$item       = $this->get('Item');

		JToolBarHelper::title(JText::sprintf('COM_MODULES_MANAGER_MODULE', JText::_($this->item->module)), 'roksprocket-logo');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || $canDo->get('core.create'))) {
			JToolBarHelper::apply('module.apply');
			JToolBarHelper::save('module.save');
		}
		if (!$checkedOut && $canDo->get('core.create')) {
			JToolBarHelper::save2new('module.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::save2copy('module.save2copy');
		}
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('module.cancel');
		} else {
			JToolBarHelper::cancel('module.cancel', 'JTOOLBAR_CLOSE');
		}

		// Get the help information for the menu item.
		$lang = JFactory::getLanguage();

		$help = $this->get('Help');
		if ($lang->hasKey($help->url)) {
			$debug = $lang->setDebug(false);
			$url   = JText::_($help->url);
			$lang->setDebug($debug);
		} else {
			$url = null;
		}
		JToolBarHelper::help($help->key, false, $url);
	}

	protected function compileLess()
	{
		$assets = JPATH_COMPONENT_ADMINISTRATOR . '/assets';
		@include_once($assets . '/less/lessc.inc.php');

		if (defined('DEV') && DEV) {
			try {
				$css_file = $assets . '/styles/roksprocket.css';
				@unlink($css_file);
				lessc::ccompile($assets . '/less/global.less', $css_file);
			} catch (exception $e) {
				JError::raiseError('LESS Compiler', $e->getMessage());
			}
		}

		RokCommon_Header::addStyle(JURI::base(true) . '/components/com_roksprocket/assets/styles/roksprocket.css?nocache=2.1.23');
	}

	protected function compileJS()
	{

		$admin_path = JPATH_COMPONENT_ADMINISTRATOR;
		$site_path  = JPATH_ROOT . '/components/com_roksprocket';

		if (defined('DEV') && DEV) {
			$buffer = "";
			$assets = JPATH_COMPONENT_ADMINISTRATOR . '/assets';
			@include_once($assets . '/less/jsmin.php');

			$admin_assets      = $admin_path . '/assets/js/';
			$app               = $admin_path . '/assets/application/';
			$filters           = $site_path . '/fields/filters/js/';
			$imagepicker       = $site_path . '/fields/imagepicker/js/';
			$peritempicker     = $site_path . '/fields/peritempicker/js/';
			$peritempickertags = $site_path . '/fields/peritempickertags/js/';
			$tags = $site_path . '/fields/tags/js/';
			$multiselect = $site_path . '/fields/multiselect/js/';

			$files = array(
				$admin_assets . 'moofx',
				$app . 'RokSprocket',
				$app . 'Tabs',
				$app . 'Dropdowns',
				$app . 'Filters',
				$app . 'Articles',
				$app . 'Response',
				$app . 'Twipsy',
				$app . 'Popover',
				$app . 'Modal',
				$app . 'Flag',
				$imagepicker . 'imagepicker',
				$peritempicker . 'peritempicker',
				$peritempickertags . 'peritempickertags',
				$tags . 'resizable-textbox',
				$tags . 'tags',
				$multiselect . 'multiselect',
				$filters . 'Picker',
				$filters . 'Picker.Attach',
				$filters . 'Picker.Date',
				$admin_assets . 'joomla-calendar',
				$admin_assets . 'ZeroClipboard'
			);

			foreach ($files as $file) {
				$file    = $file . '.js';
				$content = false;

				if (file_exists($file)) $content = file_get_contents($file);

				$buffer .= (!$content) ? "\n\n !!! File not Found: " . $file . " !!! \n\n" : $content;
			}

			if (defined('JS_MINIFIED') && JS_MINIFIED) $buffer = JSMin::minify($buffer);
			file_put_contents($admin_assets . 'roksprocket.js', $buffer);
		}

		RokCommon_Header::addScript(JURI::base(true) . '/components/com_roksprocket/assets/js/roksprocket.js?nocache=2.1.23');

		/*
			To keep track of the ordering
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/js/moofx.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/RokSprocket.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Tabs.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Dropdowns.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Filters.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Articles.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Response.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Twipsy.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Popover.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Modal.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/application/Flag.js');
		RokCommon_Header::addScript($siteURL. '/components/com_roksprocket/fields/imagepicker/js/imagepicker.js');
		RokCommon_Header::addScript($siteURL. '/components/com_roksprocket/fields/peritempicker/js/peritempicker.js');
		RokCommon_Header::addScript($siteURL. '/components/com_roksprocket/fields/tags/js/resizable-textbox.js');
		RokCommon_Header::addScript($siteURL. '/components/com_roksprocket/fields/tags/js/tags.js');
		RokCommon_Header::addScript($siteURL. '/components/com_roksprocket/fields/multiselect/js/multiselect.js');
		RokCommon_Header::addScript($siteURL. '/components/com_roksprocket/fields/filters/js/Picker.js');
		RokCommon_Header::addScript($siteURL . '/components/com_roksprocket/fields/filters/js/Picker.Attach.js');
		RokCommon_Header::addScript($siteURL . '/components/com_roksprocket/fields/filters/js/Picker.Date.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/js/joomla-calendar.js');
		RokCommon_Header::addScript($adminURL . '/components/com_roksprocket/assets/js/ZeroClipboard.js');
		*/
	}
}
