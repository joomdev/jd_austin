<?php
/**
 * @version   $Id: rokcommon.php 27322 2015-03-04 13:11:37Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 */
class plgSystemRokCommon extends JPlugin
{

	/**
	 *
	 */
	const ROKCOMMON_CONFIG_TYPE_LIBRARY = 'library';
	/**
	 *
	 */
	const ROKCOMMON_CONFIG_TYPE_CONTAINER = 'container';
	/**
	 *
	 */
	const ROKCOMMON_CONFIG_TYPE_METACONFIG = 'metaconfig';

	const ROKCOMMON_PLUGIN_VERSION = '3.2.5';


	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;

	/**
	 * @var RokCommon_Logger
	 */
	protected $logger;


	/** @var RokCommon_Dispatcher */
	protected $dispatcher;

	/**
	 * @param       $subject
	 * @param array $config
	 */
	public function __construct(&$subject, $config = array())
	{
		if ($this->loadCommonLib()) {

			if (!defined('ROKCOMMON')) {
				$error_string = 'RokCommon System Plug-in is missing the RokCommon Library.  Please Reinstall.';
			} elseif (ROKCOMMON != self::ROKCOMMON_PLUGIN_VERSION) {
				$error_string = sprintf('RokCommon Library Version (%s) does not match the RokCommon System Plug-in Version (%s).  Please Reinstall.', ROKCOMMON, self::ROKCOMMON_PLUGIN_VERSION);
			}
			if (!empty($error_string)) {
				JFactory::getApplication()->enqueueMessage($error_string, 'warning');
				return;
			}

			// Only register plugin on success.
			parent::__construct($subject, $config);

			RokCommon_ClassLoader::addPath(dirname(__FILE__) . '/lib');
			$conf = JFactory::getConfig();
			RokCommon_Service::setTempFileDir($conf->get('tmp_path'));
			RokCommon_Service::setDevelopmentMode($this->params->get('developmentMode', false));
			$this->container   = RokCommon_Service::getContainer();
			$this->logger     = $this->container->logger;
			$this->dispatcher = $this->container->dispatcher;
			$this->processRegisteredConfigs();
			if (!defined('ROKCOMMON_PLUGIN_LOADED')) define('ROKCOMMON_PLUGIN_LOADED', self::ROKCOMMON_PLUGIN_VERSION);
		}
	}


	/**
	 * @return bool
	 */
	protected function loadCommonLib()
	{
		$ret    = false;
		$errors = array();
		if (!defined('ROKCOMMON_LIB_PATH')) define('ROKCOMMON_LIB_PATH', JPATH_SITE . '/libraries/rokcommon');

		$rokcommon_inlcude_path = @realpath(ROKCOMMON_LIB_PATH . '/include.php');

		if (file_exists($rokcommon_inlcude_path)) {
			if (!defined('ROKCOMMON_ERROR_MISSING_LIBS')) define('ROKCOMMON_ERROR_MISSING_LIBS', true);
			$included_files = get_included_files();
			if (!in_array($rokcommon_inlcude_path, $included_files) && ($libret = require_once($rokcommon_inlcude_path)) !== 'ROKCOMMON_LIB_INCLUDED') {
				if (!defined('ROKCOMMON_ERROR_MISSING_LIBS')) define('ROKCOMMON_ERROR_MISSING_LIBS', true);
				$errors = (array) $libret;
			} else {
				$ret = true;
			}
		} else {
			$errors[] = 'Unable to find the RokCommon library at ' . ROKCOMMON_LIB_PATH;
		}

		if (!empty($errors)) {
			$app = JFactory::getApplication();
			if ($app->isAdmin()) {
				foreach ($errors as $error) {
					$app->enqueueMessage('RokCommon: ' . $error, 'warning');
				}
			}
		}

		return $ret;
	}

	/**
	 *
	 */
	protected function processRegisteredConfigs()
	{
		if (defined('ROKCOMMON')) {
			$db = JFactory::getDBO();
			/** @var $query JDatabaseQuery */
			$query = $db->getQuery(true);
			$query->select('extension, file, type');
			$query->from('#__rokcommon_configs');
			$query->order('priority');

			$db->setQuery($query);
			$results = $db->loadObjectList();

			if ($results) {
				$this->registerLibraries($results);
				$this->registerContainerFiles($results);
			}
		}
	}


	/**
	 * @param $config_entries
	 */
	protected function registerLibraries($config_entries)
	{
		foreach ($config_entries as $config_entry) {
			if ($config_entry->type === self::ROKCOMMON_CONFIG_TYPE_LIBRARY) {
				$filepath = JPATH_SITE . $config_entry->file;
				if (is_dir($filepath)) {
					$this->logger->debug(rc__('Registering library path %s for %s', $filepath, $config_entry->extension));
					RokCommon_ClassLoader::addPath($filepath);
				} else {
					$this->logger->notice(rc__('Directory %s does not exist.  Unable to add to Class Loader ', $filepath));
				}
			}
		}
	}

	/**
	 * @param $config_entries
	 */
	protected function registerContainerFiles($config_entries)
	{
		foreach ($config_entries as $config_entry) {
			if ($config_entry->type === self::ROKCOMMON_CONFIG_TYPE_CONTAINER) {
				$filepath = JPATH_SITE . $config_entry->file;
				if (is_file($filepath)) {
					$this->logger->debug(rc__('Loading container config file for %s from %s', $config_entry->extension, $filepath));
					RokCommon_Service::addConfigFile($filepath);
				} else {
					$this->logger->notice(rc__('Unable to find registered container config file %s at %s', $config_entry->extension, $filepath));
				}
			}
		}
	}


	/**
	 * @return mixed
	 */
	public function onBeforeCompileHead()
	{
		/** @var $header RokCommon_Header_Joomla */
		$header = $this->container->getService('header');
		$header->populate();
	}
}

