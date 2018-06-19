<?php
/**
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$jversion = new JVersion();
if (version_compare($jversion->getShortVersion(), '3.0', '>=')) {
	class RenderAMM
	{
		public $item;
		public $config;
		public $assignments;
		public $form;

		public function render(&$form, $name = '')
		{
			$items = array();
			foreach ($form->getFieldset($name) as $field) {
				$items[] = '<div class="control-group"><div class="control-label">' . $field->label . '</div><div class="controls">' . $field->input . '</div></div>';
			}
			if (empty ($items)) {
				return '';
			}

			return implode('', $items);
		}

		public function renderPage()
		{
			include(JPATH_ADMINISTRATOR . '/components/com_advancedmodules/views/module/tmpl/edit_assignment.php');
		}
	}
} else {

	class RenderAMM
	{
		public $item;
		public $config;
		public $assignments;
		public $form;

		public function render(&$form, $name = '')
		{
			$items = array();
			foreach ($form->getFieldset($name) as $field) {
				$items[] = $field->label . $field->input;
			}
			if (empty ($items)) {
				return '';
			}

			return '<li>' . implode('</li><li>', $items) . '</li>';
		}

		public function renderPage()
		{
			include(JPATH_ADMINISTRATOR . '/components/com_advancedmodules/views/module/tmpl/edit_assignment.php');
		}
	}
}

if (version_compare($jversion->getShortVersion(), '3.0', '>=')) {
	JHtml::_('jquery.framework');
}

$juri_base = preg_replace("/administrator$/", "", JURI::base(true));
RokCommon_Header::addStyle($juri_base . 'components/com_roksprocket/lib/RokSprocket/Addon/AdvancedModuleManager/assets/styles/amm-fixes.css');


$renderer = new RenderAMM();

$renderer->item = $that->item;
$renderer->form = $that->form;

if (!isset($renderer->config)) {
	$path = JPATH_LIBRARIES . '/regularlabs/helpers/parameters.php';
	if (is_file($path)) {
		require_once $path;
		$parameters       = RLParameters::getInstance();
	} else {
		$path = JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
		require_once $path;
		$parameters       = NNParameters::getInstance();
	}
	$config           = JComponentHelper::getParams('com_advancedmodules');
	$renderer->config = $parameters->getParams($config->toObject(), JPATH_ADMINISTRATOR . '/components/com_advancedmodules/config.xml');
}
if (!isset($renderer->assignments)) {
	$xmlfile     = JPATH_ADMINISTRATOR . '/components/com_advancedmodules/assignments.xml';
	$assignments = new JForm('assignments', array('control' => 'advancedparams'));
	$assignments->loadFile($xmlfile, 1, '//config');
	$assignments->bind($renderer->item->advancedparams);
	$renderer->assignments = $assignments;
}

if ($renderer->config->show_color) {
	if (isset($renderer->config->main_colors)) {
		$colors = explode(',', $renderer->config->main_colors);
		foreach ($colors as $i => $c) {
			$colors[$i] = strtoupper('#' . preg_replace('#[^a-z0-9]#i', '', $c));
		}
		$script = "
            mainColors = new Array( '" . implode("', '", $colors) . "' );";
		RokCommon_Header::addInlineScript($script);
	}
}

?>
<fieldset class="adminform">
	<div class="advanced-module-manager">
		<!-- opening divs twice for fixing joomla accordions -->
		<?php if ($renderer->config->show_color) :
		echo $renderer->render($renderer->assignments, 'color');
		endif;
		if(version_compare($jversion->getShortVersion(), '3.0', '>=')):
			$renderer->renderPage();
		else:?>
			<div>
				<div>
					<?php $renderer->renderPage(); ?>
				</div>
		<?php
		endif;
		?>
</fieldset>





