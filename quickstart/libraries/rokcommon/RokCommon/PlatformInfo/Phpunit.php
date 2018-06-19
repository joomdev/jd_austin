<?php
/**
 * @version   $Id: Phpunit.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_PlatformInfo_Phpunit extends RokCommon_PlatformInfo_AbstractPlatformInfo
{
	/**
	 * @var string
	 */
	public $rootPath = '';
	/**
	 * @var string
	 */
	public $rootUrl = 'file://';
	/**
	 * @var string
	 */
	public $urlBase = '';
	/**
	 * @var string
	 */
	public $defaultTemplatePath = '';
	/**
	 * @var string
	 */
	public $defaultTemplate = 'phpunit';
	/**
	 * @var string
	 */
	public $pathForTemplate = '';

	/**
	 * @var string
	 */
	public $instanceId = '';

	public $rtl = false;

	/**
	 * @param bool $admin
	 *
	 * @return string the name of the current template
	 */
	public function getDefaultTemplate($admin = false)
	{
		return $this->defaultTemplate;
	}

	/**
	 * @param bool $admin
	 *
	 * @return string the path to the current template/theme root
	 */
	public function getDefaultTemplatePath($admin = false)
	{
		return $this->defaultTemplatePath;
	}

	/**
	 * @return string the path to the current platform root
	 */
	public function getRootPath()
	{
		return $this->rootPath;
	}

	/**
	 * @param RokCommon_Service_Container $container
	 *
	 * @throws RokCommon_Exception
	 */
	public function setPlatformParameters(RokCommon_Service_Container &$container)
	{

		$container['platform.name']        = 'phpunit';
		$container['platform.displayname'] = 'PHPUnit';
		$container['platform.version']     = 0;
		$container['platform.root']        = $this->getRootPath();
		$container['template.name']        = $this->getDefaultTemplate();
		$container['template.path']        = $this->getDefaultTemplatePath();
	}

	/**
	 * @return string the root url to the current platform
	 */
	public function getRootUrl()
	{
		return $this->rootUrl;
	}

	/**
	 * @return string  the base uri path for the platform
	 */
	public function getUrlBase()
	{
		return $this->urlBase;
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
		return $this->pathForTemplate;
	}

	/**
	 * @return string
	 */
	public function getInstanceId()
	{
		return $this->instanceId;
	}

	/**
	 * @param string $defaultTemplate
	 */
	public function setDefaultTemplate($defaultTemplate)
	{
		$this->defaultTemplate = $defaultTemplate;
	}

	/**
	 * @param string $defaultTemplatePath
	 */
	public function setDefaultTemplatePath($defaultTemplatePath)
	{
		$this->defaultTemplatePath = $defaultTemplatePath;
	}

	/**
	 * @param string $instanceId
	 */
	public function setInstanceId($instanceId)
	{
		$this->instanceId = $instanceId;
	}

	/**
	 * @param string $pathForTemplate
	 */
	public function setPathForTemplate($pathForTemplate)
	{
		$this->pathForTemplate = $pathForTemplate;
	}

	/**
	 * @param string $rootPath
	 */
	public function setRootPath($rootPath)
	{
		$this->rootPath = $rootPath;
	}

	/**
	 * @param string $rootUrl
	 */
	public function setRootUrl($rootUrl)
	{
		$this->rootUrl = $rootUrl;
	}

	/**
	 * @param string $urlBase
	 */
	public function setUrlBase($urlBase)
	{
		$this->urlBase = $urlBase;
	}

	/**
	 * Is the current platform RTL?
	 * @return mixed
	 */
	public function isRTL()
	{
		return $this->rtl;
	}


	/**
	 * Gets the seo version of a URL
	 *
	 * @param           $url
	 * @param bool      $xhtml
	 * @param bool|null $ssl
	 *
	 * @return mixed
	 */
	public function getSEOUrl($url, $xhtml = true, $ssl = null)
	{
		// TODO: Implement getSEOUrl() method.
		return $url;
	}
}
