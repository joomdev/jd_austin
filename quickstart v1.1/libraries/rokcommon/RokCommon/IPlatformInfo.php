<?php
/**
 * @version   $Id: IPlatformInfo.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokCommon_IPlatformInfo
{
	/**
	 * Returns the URL for a given file based on the full file path passed in
	 *
	 * @param $filepath
	 *
	 * @return string
	 */
	public function getUrlForPath($filepath);


	/**
	 * Returns the Full path for a file passed in as a local url.
	 * @abstract
	 *
	 * @param      $url
	 * @param bool $exists if true forces a check if the file exists
	 *
	 * @return string|bool the full path to the file or false if the file does not exist
	 */
	public function getPathForUrl($url, $exists = true);

	/**
	 * Determine if the the passed url is external to the current running platform
	 *
	 * @abstract
	 *
	 * @param string $url the url to check to see if its local;
	 *
	 * @return mixed
	 */
	public function isLinkExternal($url);

	/**
	 * @abstract
	 *
	 * @param bool $admin
	 *
	 * @return string the name of the current template
	 */
	public function getDefaultTemplate($admin = false);

	/**
	 * @abstract
	 *
	 * @param bool $admin
	 *
	 * @return string the path to the current template/theme root
	 */
	public function getDefaultTemplatePath($admin = false);

	/**
	 * Gets the path for the named template
	 * @abstract
	 *
	 * @param string    $template The name of the template to get the path for.
	 * @param bool      $admin    is this an admin template
	 *
	 * @return string|bool the path to the template or false if the template does not exist
	 */
	public function getPathForTemplate($template, $admin = false);

	/**
	 * @abstract
	 * @return string the path to the current platform root
	 */
	public function getRootPath();

	/**
	 * @abstract
	 * @return string the root url to the current platform
	 */
	public function getRootUrl();

	/**
	 * @abstract
	 * @return string  the base uri path for the platform
	 */
	public function getUrlBase();

	/**
	 * @abstract
	 *
	 * @param RokCommon_Service_Container $container
	 */
	public function setPlatformParameters(RokCommon_Service_Container &$container);

	/**
	 * @abstract
	 * @return string
	 */
	public function getInstanceId();

	/**
	 * Is the current platform RTL?
	 * @abstract
	 * @return mixed
	 */
	public function isRTL();

	/**
	 * Gets the seo version of a URL
	 *
	 * @param           $url
	 * @param bool      $xhtml
	 * @param bool|null $ssl
	 *
	 * @abstract
	 * @return mixed
	 */
	public function getSEOUrl($url, $xhtml = true, $ssl = null);


	/**
	 * Gets an array of platform specific
	 * @abstract
	 * @return array
	 */
	public function getPathChecks();


}
