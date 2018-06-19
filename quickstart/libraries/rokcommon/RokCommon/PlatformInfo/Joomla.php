<?php
/**
 * @version   $Id: Joomla.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_PlatformInfo_Joomla extends RokCommon_PlatformInfo_AbstractPlatformInfo
{
	/**
	 * @param bool $admin
	 *
	 * @return string the name of the current template
	 */
	public function getDefaultTemplate($admin = false)
	{

		$app = JFactory::getApplication();
		if ($admin) {
			return $app->getTemplate();
		} else {
			// Load styles
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('template');
			$query->from('#__template_styles as s');
			$query->where('s.client_id = 0');
			$query->where('s.home = 1');
			$db->setQuery($query);
			$template = $db->loadResult();
			return $template;
		}
	}

	/**
	 * Gets the path for the named template
	 *
	 * @param string    $template The name of the template to get the path for.
	 * @param bool      $admin    is this an admin template
	 *
	 * @return string|bool the path to the template or false if the template does not exist
	 */
	public function getPathForTemplate($template, $admin = false)
	{
		$ret = false;
		if ($admin) {
			$path = JPATH_ADMINISTRATOR . DS . 'templates' . DS . $template;
		} else {
			$path = JPATH_SITE . DS . 'templates' . DS . $template;
		}
		if (is_dir($path)) {
			$ret = $path;
		}
		return $ret;
	}


	/**
	 * @param bool $admin
	 *
	 * @return string the path to the current template/theme root
	 */
	public function getDefaultTemplatePath($admin = false)
	{
		$root = ($admin) ? JPATH_ADMINISTRATOR : JPATH_ROOT;
		return $root . '/templates/' . $this->getDefaultTemplate($admin);
	}

	/**
	 * @return string the path to the current platform root
	 */
	public function getRootPath()
	{
		return JPATH_ROOT;
	}

	/**
	 * @return string the root url to the current platform
	 */
	public function getRootUrl()
	{
		return JURI::root();
	}


	/**
	 * @return string
	 */
	public function getUrlBase()
	{
		return JURI::root(true);
	}


	/**
	 * @param RokCommon_Service_Container $container
	 */
	public function setPlatformParameters(RokCommon_Service_Container &$container)
	{
		$container['platform.name']        = 'joomla';
		$container['platform.displayname'] = 'Joomla';
		$container['platform.version']     = JVERSION;
		$container['platform.root']        = $this->getRootPath();
		$container['template.name']        = $this->getDefaultTemplate();
		$container['template.path']        = $this->getDefaultTemplatePath();
	}

	/**
	 * @return string
	 */
	public function getInstanceId()
	{
		$config = JFactory::getConfig();
		return md5($config->get('secret'));
	}

	/**
	 * Is the current platform RTL?
	 * @return mixed
	 */
	public function isRTL()
	{
		$doc = JFactory::getDocument();
		if ($doc->direction == 'rtl') {
			return true;
		}
	}

	/**
	 * Gets the seo version of a URL
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	public function getSEOUrl($url, $xhtml = true, $ssl = null)
	{
		return JRoute::_($url);
	}


	/**
	 * @return array
	 */
	public function getPathChecks()
	{
		$jversion        = new JVersion();
		$platform_checks = array(
			'/joomla/' . $jversion->getShortVersion(),
			'/joomla/' . $jversion->RELEASE,
			''
		);
		return $platform_checks;
	}

}
