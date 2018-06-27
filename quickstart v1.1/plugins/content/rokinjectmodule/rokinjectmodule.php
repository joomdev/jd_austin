<?php
/**
 * @version   $Id: rokinjectmodule.php 28743 2015-08-19 16:51:12Z kat $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 */
class plgContentRokInjectModule extends JPlugin
{

	/**
	 * @var array
	 */
	protected static $has_run = array();

	protected $originalHeader;

	/**
	 * @param $context
	 * @param $article
	 * @param $params
	 * @param $limitstart
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}

		if (is_scalar($params) || (is_object($params) && method_exists($params, '__toString' ))) {
			$serializedParams = (string) $params;
		} elseif (is_array($params)) {
			$serializedParams = json_encode($params);
		} else {
			$serializedParams = 'xxx';
		}

		$checksum = md5($context . $article->text . $serializedParams . $limitstart);
		if (!in_array($checksum, self::$has_run)) {
			self::$has_run[] = $checksum;
			// [module-28 style=xhtml|none] syntax for loading any module instance

			$regex   = '/\[module-(\d{1,})(.*)\]/i';
			$matches = array();
			preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

			if (!empty($matches)) {
				if (defined('ROKCOMMON') && JFactory::getConfig()->get('caching')) {
					$this->switchRokCommonHeader();
				}
				foreach ($matches as $match) {

					$module_id     = $match[1];
					$match_params  = $match[2];
					$module_params = array();

					if (isset($match_params)) {
						$param_match = array();
						preg_match_all('/((\w+)\=(\w+))/i', $match_params, $param_match, PREG_SET_ORDER);
						foreach ($param_match as $pmatch) {
							$module_params[$pmatch[2]] = $pmatch[3];
						}
					}

					$module_output = $this->_load_module($module_id, $module_params);
					if ($module_output) {
						$article->text = preg_replace($regex, $module_output, $article->text, 1);
					} else {
                        $article->text = preg_replace($regex, '', $article->text, 1);
                    }

				}
				if (defined('ROKCOMMON') && $this->originalHeader !== null) {
					$this->revertRokCommonHeader();
				}
			}

		}

	}

	/**
	 * @param $module_id
	 * @param $params
	 *
	 * @return mixed
	 */
	protected function _load_module($module_id, $params)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__modules WHERE id='$module_id' AND published=1");
		$module = $db->loadObject();
		if ($module) {
			return JModuleHelper::renderModule($module, $params);
		} else {
			return false;
		}
	}

	protected function switchRokCommonHeader()
	{
		require_once(dirname(__FILE__) . '/lib/RokInjectModule_Header.php');
		$container            = RokCommon_Service::getContainer();
		$this->originalHeader = $container->getService('header');
		$container->setService('header', new RokInjectModule_Header());
	}

	protected function revertRokCommonHeader()
	{
		$container = RokCommon_Service::getContainer();
		$container->setService('header', $this->originalHeader);
		$this->originalHeader = null;
	}
}
