<?php
/**
 * @version   $Id: AbstractPlatformInfo.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokCommon_PlatformInfo_AbstractPlatformInfo implements RokCommon_IPlatformInfo
{
	/**
	 * Returns the URL for a given file based on the full file path passed in
	 *
	 * @param $path
	 *
	 * @internal param $filepath
	 *
	 * @return string
	 */
	public function getUrlForPath($path)
	{
		$path = $this->cleanPath($path);
		// if its external  just return the external url
		if ($this->isLinkExternal($path)) return $path;

		$parsed_path     = parse_url($this->cleanPath($path));
		$return_url_path = $parsed_path['path'];
		if (preg_match('/^WIN/', PHP_OS)) {
			$return_url_path = $path;
		}
		if (!@file_exists($return_url_path)) {
			return $return_url_path;
		}
		$instance_url_path           = $this->getUrlBase();
		$instance_filesystem_path    = $this->cleanPath($this->getRootPath());
		$server_filesystem_root_path = $this->cleanPath($_SERVER['DOCUMENT_ROOT']);


		// check if the path seems to be in the instances  or  server path
		// leave it as is if not one of the two
		if (strpos($return_url_path, $instance_filesystem_path) === 0) {
			// its an instance path
			$return_url_path = $instance_url_path . str_replace($instance_filesystem_path, '', $return_url_path);
		} elseif (strpos($return_url_path, $server_filesystem_root_path) === 0) {
			// its a server path
			$return_url_path = str_replace($server_filesystem_root_path, '', $return_url_path);
		}

		// append any passed query string
		if (isset($parsed_path['query'])) {
			$return_url_path = $return_url_path . '?' . $parsed_path['query'];
		}

		return $return_url_path;
	}

	/**
	 * Determine if the the passed url is external to the current running platform
	 *
	 * @param string $url      the url to check to see if its local;
	 *
	 * @return mixed
	 */
	public function isLinkExternal($url)
	{

		if (@file_exists($url)) return false;
		$root_url = $this->getRootUrl();
		$url_uri  = parse_url($url);

		//if the url does not have a scheme must be internal
		if (isset($url_uri['scheme'])) {
			$scheme = strtolower($url_uri['scheme']);
			if ($scheme == 'http' || $scheme == 'https') {
				$site_uri = parse_url($root_url);
				if (isset($url_uri['host']) && strtolower($url_uri['host']) == strtolower($site_uri['host'])) return false;
			} elseif ($scheme == 'file' || $scheme == 'vfs') {
				return false;
			}
		}
		// cover external urls like //foo.com/foo.js
		if (!isset($url_uri['host']) && !isset($url_uri['scheme']) && isset($url_uri['path']) && substr($url_uri['path'], 0, 2) != '//') return false;
		//the url has a host and it isn't internal
		return true;
	}

	/**
	 * Returns the Full path for a file passed in as a local url.
	 *
	 * @param $url
	 *
	 * @return string|bool the full path to the file or false if the file does not exist
	 */
	public function getPathForUrl($url, $exists = true)
	{
		// if its an external link dont even process
		if ($this->isLinkExternal($url)) return false;


		$parsed_url = parse_url($url);
		if (preg_match('/^WIN/', PHP_OS) && isset($parsed_url['scheme'])) {
			if (preg_match('/^[A-Za-z]$/', $parsed_url['scheme']) && @file_exists($url)) return $url;
		}
		if (@file_exists($parsed_url['path']) && !isset($parsed_url['scheme'])) return $parsed_url['path'];
		if (isset($parsed_url['scheme'])) {
			$scheme = strtolower($parsed_url['scheme']);
			if ($scheme == 'file') {
				return $parsed_url['path'];
			}
			if ($scheme != 'http' && $scheme != 'https')
			{
				return $url;
			}
		}

		$instance_url_path           = $this->getUrlBase();
		$instance_filesystem_path    = $this->cleanPath($this->getRootPath());
		$server_filesystem_root_path = $this->cleanPath($_SERVER['DOCUMENT_ROOT']);

		$missing_ds = (substr($parsed_url['path'], 0, 1) != '/') ? '/' : '';
		if (!empty($instance_url_path) && strpos($parsed_url['path'], $instance_url_path) === 0) {
			$stripped_base = $this->cleanPath($parsed_url['path']);
			if (strpos($stripped_base, $instance_url_path) == 0) {
				$stripped_base = substr_replace($stripped_base, '', 0, strlen($instance_url_path));
			}
			$return_path = $instance_filesystem_path . $missing_ds . $this->cleanPath($stripped_base);
		} elseif (empty($instance_url_path) && file_exists($instance_filesystem_path . $missing_ds . $parsed_url['path'])) {
			$return_path = $instance_filesystem_path . $missing_ds . $parsed_url['path'];
		} else {
			$return_path = $server_filesystem_root_path . $missing_ds . $this->cleanPath($parsed_url['path']);
		}
		return $return_path;
	}

	protected function cleanPath($path)
	{
		if (!preg_match('#^/$#', $path)) {
			$path = preg_replace('#[/\\\\]+#', '/', $path);
			$path = preg_replace('#/$#', '', $path);
		}
		return $path;
	}

	/**
	 * @return array
	 */
	public function getPathChecks(){
		$platform_checks = array('');
		return $platform_checks;
	}
}
