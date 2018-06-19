<?php
/**
 * @version   $Id: AdvancedModuleManager.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Addon_AdvancedModuleManager implements RokSprocket_Addon
{
	protected $component;

	public function __construct(RokCommon_Dispatcher $dispatcher)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_advancedmodules/tables');

		if (JPluginHelper::isEnabled('system', 'advancedmodules') && $this->isComponentEnabled()) {
			RokCommon_Composite::addPackagePath('roksprocket', dirname(__FILE__) . '/AdvancedModuleManager', 25);
			$dispatcher->connect('roksprocket.module.get', array($this, 'getModule'));
			$dispatcher->connect('roksprocket.module.save', array($this, 'saveModule'));
			$dispatcher->connect('roksprocket.module.duplicate', array($this, 'duplicateModule'));
		}
	}

	public function getModule(RokCommon_Event $event, array $arguments = array())
	{
		$pk           = $arguments['primary_key'];
		$item         = $arguments['menu_item'];
		$extension_id = $arguments['extension_id'];

		require_once(JPATH_ADMINISTRATOR . '/components/com_advancedmodules/models/module.php');
		$model = new AdvancedModulesModelModule();
		$model->setState('extension.id', $extension_id);
		$arguments['menu_item']                       = $model->getItem($pk);
		$arguments['menu_item']->edit_display_options = new RokCommon_Registry();
		$arguments['menu_item']->edit_display_options->set('showAccess', false);
		$event->setProcessed(true);
		return $arguments;

	}

	public function saveModule(RokCommon_Event $event, array $arguments = array())
	{
		$data = $arguments['data'];
		require_once(JPATH_ADMINISTRATOR . '/components/com_advancedmodules/models/module.php');
		$model = new AdvancedModulesModelModule();

		$arguments['save_result'] = $model->save($data);
		$event->setProcessed(true);
		return $arguments;
	}

	public function duplicateModule(RokCommon_Event $event)
	{
		$db =JFactory::getDbo();
		$arguments = $event->getParameters();
		$old_pk = $arguments['old_pk'];
		$new_pk = $arguments['new_pk'];

		/** @var $table_a AdvancedModulesTableAdvancedModules */
		$table_a = JTable::getInstance('AdvancedModules', 'AdvancedModulesTable');
		if (!$table_a->load($new_pk)) {
			$table_a->moduleid = $new_pk;
			$db->insertObject($table_a->getTableName(), $table_a, $table_a->getKeyName());
		}

		if ($table_a->load($old_pk, true)) {
			$table_a->moduleid =$new_pk;

			if (!$table_a->check() || !$table_a->store()) {
				throw new Exception($table_a->getError());
			}
		}
	}

	protected function isComponentEnabled()
	{

		$option = 'com_advancedmodules';
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->select('extension_id AS id, element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where($query->qn('type') . ' = ' . $db->quote('component'));
		$query->where($query->qn('element') . ' = ' . $db->quote($option));
		$db->setQuery($query);

		$cache = JFactory::getCache('_system', 'callback');

		$this->component = $cache->get(array($db, 'loadObject'), null, $option, false);

		$result = $db->loadObject();
		if ($error = $db->getErrorMsg() || empty($this->component)) {
			return false;
		}

		// Convert the params to an object.
		if (is_string($this->component->params)) {
			$temp = new JRegistry;
			$temp->loadString($this->component->params);
			$this->component->params = $temp;
		}

		return true;
	}
}
