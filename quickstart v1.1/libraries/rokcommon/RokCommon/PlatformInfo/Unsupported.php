<?php
/**
 * @version   $Id: Unsupported.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_PlatformInfo_Unsupported implements RokCommon_IPlatformInfo
{
    /**
     * Returns the URL for a given file based on the full file path passed in
     *
     * @param $filepath
     *
     * @return string
     */
    public function getUrlForPath($filepath)
    {
        return 'file://' . $filepath;
    }

    /**
     * @param bool $admin
     *
     * @return string the name of the current template
     */
    public function getDefaultTemplate($admin = false)
    {
        throw new RokCommon_Exception('Unimplmented function getDefaultTemplate()');
    }

    /**
     * @param bool $admin
     *
     * @return string the path to the current template/theme root
     */
    public function getDefaultTemplatePath($admin = false)
    {
        throw new RokCommon_Exception('Unimplmented function getDefaultTemplatePath()');
    }

    /**
     * @return string the path to the current platform root
     */
    public function getRootPath()
    {
        throw new RokCommon_Exception('Unimplmented function getRootPath()');
    }

    /**
     * @param RokCommon_Service_Container $container
     *
     * @throws RokCommon_Exception
     */
    public function setPlatformParameters(RokCommon_Service_Container &$container)
    {
        // TODO: Implement getRootPath() method.
        throw new RokCommon_Exception('Unimplmented function setPlatformParameters()');
    }

	/**
	 * @return string
	 */
	public function getInstanceId()
	{
		return '';
	}

	/**
	 * Returns the Full path for a file passed in as a local url.
	 *
	 * @param      $url
	 * @param bool $exists if true forces a check if the file exists
	 *
	 * @return string|bool the full path to the file or false if the file does not exist
	 */
	public function getPathForUrl($url, $exists = true)
	{
		// TODO: Implement getPathForUrl() method.
	}

	/**
	 * Determine if the the passed url is external to the current running platform
	 *
	 *
	 * @param string $url the url to check to see if its local;
	 *
	 * @return mixed
	 */
	public function isLinkExternal($url)
	{
		// TODO: Implement isLinkExternal() method.
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
		// TODO: Implement getPathForTemplate() method.
	}

	/**
	 * @return string the root url to the current platform
	 */
	public function getRootUrl()
	{
		// TODO: Implement getRootUrl() method.
	}

	/**
	 * @return string  the base uri path for the platform
	 */
	public function getUrlBase()
	{
		// TODO: Implement getUrlBase() method.
	}

	/**
	 * Is the current platform RTL?
	 * @return mixed
	 */
	public function isRTL()
	{
		// TODO: Implement isRTL() method.
		return false;
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
