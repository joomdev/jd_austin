<?php
/**
 * @version   $Id: Joomla.php 19249 2014-02-27 19:21:50Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
jimport('joomla.application.module.helper');

/**
 *
 */
class RokSprocket_PlatformHelper_Joomla implements RokSprocket_PlatformHelper
{

	/**
	 * Replaces the matched tags
	 *
	 * @param    array    An array of matches (see preg_match_all)
	 *
	 * @return    string
	 */
	protected static function route(&$matches)
	{
		$original = $matches[0];
		$url      = $matches[1];
		$url      = str_replace('&amp;', '&', $url);
		$route    = JRoute::_('index.php?' . $url);

		return 'href="' . $route;
	}

	/**
	 * @return string
	 */
	public function getCurrentTemplate()
	{
		$app      = JFactory::getApplication();
		$template = $app->getTemplate();
		return $template;
	}

	/**
	 * Get the parameters for the passes in module id
	 *
	 * @param $id
	 *
	 * @return RokCommon_Registry
	 */
	public function getModuleParameters($id)
	{
		/** @var $module JTableModule */
		$module = JTable::getInstance('Module', 'JTable', array());
		$module->load($id);
		$params = new RokCommon_Registry($module->params);
		return $params;
	}

	/**
	 * @param $callback
	 * @param $args
	 * @param $params
	 * @param $moduleid
	 *
	 * @return RokSprocket_ItemCollection|bool
	 */
	public function getFromCache($callback, $args, $params, $moduleid)
	{
		$conf = JFactory::getConfig();
		if ($conf->get('caching') && $params->get('module_cache', 1)) {
			$cache = JFactory::getCache('mod_roksprocket');
			$cache->setCaching(true);
			$cache->setLifeTime($params->get('cache_time', 900));
			$user   = JFactory::getUser();
			$levels = $user->getAuthorisedViewLevels();
			$key    = 'mod_roksprocket' . md5(var_export($args, true)) . md5((string)$params) . implode(',', $levels) . '.' . $moduleid;

			$return = $cache->get($callback, $args, $key);
		} else {
			$return = call_user_func_array($callback, $args);
		}
		return $return;
	}

	/**
	 * @param RokSprocket_ItemCollection $items
	 *
	 * @param \RokCommon_Registry        $parameters
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function processItemsForEvents(RokSprocket_ItemCollection $items, RokCommon_Registry $parameters)
	{
		$parameters = new JRegistry((string)$parameters);
		if ($parameters->get('run_content_plugins', 'onmodule') == 'oneach') {
			// process content plugins
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			/** @var $item RokSprocket_Item */
			foreach ($items as &$item) {
				$article       = new stdClass();
				$article->text = $item->getText();
				$results       = $dispatcher->trigger('onContentPrepare', array(
					'mod_roksprocket.article',
					&$article,
					&$parameters,
					$item->getOrder()
				));
				$item->setText($article->text);


			}
		}
		return $items;
	}

	public function processOutputForEvents($output, RokCommon_Registry $parameters)
	{
		$parameters = new JRegistry((string)$parameters);
		if ($parameters->get('run_content_plugins', 'onmodule') == 'onmodule' || (int)$parameters->get('run_content_plugins', 1) == true) {
			// process content plugins
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$article       = new stdClass();
			$article->text = $output;
			$results       = $dispatcher->trigger('onContentPrepare', array(
				'mod_roksprocket.article',
				&$article,
				&$parameters,
			    0
			));
			$output = $article->text;
		}
		return $output;
	}

	/**
	 * Gets the cache directory for the platform
	 *
	 * @return string the absolute path to the cache dir
	 */
	public function getCacheDir()
	{
		return JPATH_CACHE . '/mod_roksprocket';
	}

	/**
	 * @return string
	 */
	public function getCacheUrl()
	{
		return 'cache/mod_roksprocket';
	}

	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	public function convertRelativeUrl($url)
	{
		$base      = JURI::base(true) . '/';
		$protocols = '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$regex     = '#^(?!/|' . $protocols . '|\#|\')#m';
		$url       = preg_replace($regex, "$base$1", $url);
		return $url;
	}

	/**
	 * Converting the site URL to fit to the HTTP request
	 */
	public function cleanup($buffer)
	{
		$app = JFactory::getApplication();

		if ($app->getName() != 'site' || $app->getCfg('sef') == '0') {
			return $buffer;
		}

		//Replace src links
		$base = JURI::base(true) . '/';

		$regex  = '#href="index.php\?([^"]*)#m';
		$buffer = preg_replace_callback($regex, array('RokSprocket_PlatformHelper_Joomla', 'route'), $buffer);
		$this->checkBuffer($buffer);

		$protocols = '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$regex     = '#(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer    = preg_replace($regex, "$1=\"$base\$2\"", $buffer);
		$this->checkBuffer($buffer);
		$regex  = '#(onclick="window.open\(\')(?!/|' . $protocols . '|\#)([^/]+[^\']*?\')#m';
		$buffer = preg_replace($regex, '$1' . $base . '$2', $buffer);
		$this->checkBuffer($buffer);

		// ONMOUSEOVER / ONMOUSEOUT
		$regex  = '#(onmouseover|onmouseout)="this.src=([\']+)(?!/|' . $protocols . '|\#|\')([^"]+)"#m';
		$buffer = preg_replace($regex, '$1="this.src=$2' . $base . '$3$4"', $buffer);
		$this->checkBuffer($buffer);

		// Background image
		$regex  = '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|' . $protocols . '|\#)([^\)\'\"]+)[\'\"]?\)#m';
		$buffer = preg_replace($regex, 'style="$1: url(\'' . $base . '$2$3\')', $buffer);
		$this->checkBuffer($buffer);

		// OBJECT <param name="xx", value="yy"> -- fix it only inside the <param> tag
		$regex  = '#(<param\s+)name\s*=\s*"(movie|src|url)"[^>]\s*value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer = preg_replace($regex, '$1name="$2" value="' . $base . '$3"', $buffer);
		$this->checkBuffer($buffer);

		// OBJECT <param value="xx", name="yy"> -- fix it only inside the <param> tag
		$regex  = '#(<param\s+[^>]*)value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"\s*name\s*=\s*"(movie|src|url)"#m';
		$buffer = preg_replace($regex, '<param value="' . $base . '$2" name="$3"', $buffer);
		$this->checkBuffer($buffer);

		// OBJECT data="xx" attribute -- fix it only in the object tag
		$regex  = '#(<object\s+[^>]*)data\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer = preg_replace($regex, '$1data="' . $base . '$2"$3', $buffer);
		$this->checkBuffer($buffer);

		JResponse::setBody($buffer);
		return $buffer;
	}

	/**
	 * @param $buffer
	 */
	private function checkBuffer($buffer)
	{
		if ($buffer === null) {
			switch (preg_last_error()) {
				case PREG_BACKTRACK_LIMIT_ERROR:
					$message = "PHP regular expression limit reached (pcre.backtrack_limit)";
					break;
				case PREG_RECURSION_LIMIT_ERROR:
					$message = "PHP regular expression limit reached (pcre.recursion_limit)";
					break;
				case PREG_BAD_UTF8_ERROR:
					$message = "Bad UTF8 passed to PCRE function";
					break;
				default:
					$message = "Unknown PCRE error calling PCRE function";
			}
			JError::raiseError(500, $message);
		}
	}

}


$app    = JFactory::getApplication();
$input  = $app->input;
$format = $input->get('format', null, 'word');
if ($format == 'raw') {
	/**
	 * JDocument Module renderer
	 *
	 * @package     Joomla.Platform
	 * @subpackage  Document
	 * @since       11.1
	 */
	class JDocumentRendererModule extends JDocumentRenderer
	{
		/**
		 * Renders a module script and returns the results as a string
		 *
		 * @param   string $module  The name of the module to render
		 * @param   array  $attribs Associative array of values
		 * @param   string $content If present, module information from the buffer will be used
		 *
		 * @return  string  The output of the script
		 *
		 * @since   11.1
		 */
		public function render($module, $attribs = array(), $content = null)
		{
			if (!is_object($module)) {
				$title = isset($attribs['title']) ? $attribs['title'] : null;

				$module = JModuleHelper::getModule($module, $title);

				if (!is_object($module)) {
					if (is_null($content)) {
						return '';
					} else {
						/**
						 * If module isn't found in the database but data has been pushed in the buffer
						 * we want to render it
						 */
						$tmp            = $module;
						$module         = new stdClass;
						$module->params = null;
						$module->module = $tmp;
						$module->id     = 0;
						$module->user   = 0;
					}
				}
			}

			// Get the user and configuration object
			// $user = JFactory::getUser();
			$conf = JFactory::getConfig();

			// Set the module content
			if (!is_null($content)) {
				$module->content = $content;
			}

			// Get module parameters
			$params = new JRegistry;
			$params->loadString($module->params);

			// Use parameters from template
			if (isset($attribs['params'])) {
				$template_params = new JRegistry;
				$template_params->loadString(html_entity_decode($attribs['params'], ENT_COMPAT, 'UTF-8'));
				$params->merge($template_params);
				$module         = clone $module;
				$module->params = (string)$params;
			}

			$contents = '';
			// Default for compatibility purposes. Set cachemode parameter or use JModuleHelper::moduleCache from within the
			// module instead
			$cachemode = $params->get('cachemode', 'oldstatic');

			if ($params->get('cache', 0) == 1 && $conf->get('caching') >= 1 && $cachemode != 'id' && $cachemode != 'safeuri') {

				// Default to itemid creating method and workarounds on
				$cacheparams               = new stdClass;
				$cacheparams->cachemode    = $cachemode;
				$cacheparams->class        = 'JModuleHelper';
				$cacheparams->method       = 'renderModule';
				$cacheparams->methodparams = array($module, $attribs);

				$contents = JModuleHelper::ModuleCache($module, $params, $cacheparams);

			} else {
				$contents = JModuleHelper::renderModule($module, $attribs);
			}

			return $contents;
		}
	}

}