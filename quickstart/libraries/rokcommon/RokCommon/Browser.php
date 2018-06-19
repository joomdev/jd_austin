<?php
/**
 * @version   $Id: Browser.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;

/**
 * @package    RokCommon
 */
class RokCommon_Browser
{
	/** @var string */
	protected $ua;

	/** @var string */
	protected $name;

	/** @var string */
	protected $version;

	/** @var string */
	protected $shortversion;

	/** @var string */
	protected $platform;

	/** @var string */
	protected $engine;

	/** @var array */
	protected $checks = array();

	public function __get($name)
	{
		switch ($name) {
			case 'checks':
				return null;
				break;
			default:
				if (property_exists($this, $name) && isset($this->{$name})) {
					return $this->{$name};
				} elseif (method_exists($this, 'get' . ucfirst($name))) {
					return call_user_func(array($this, 'get' . ucfirst($name)));
				} else {
					return null;
				}
		}
	}

	/**
	 *
	 */
	public function __construct()
	{
		$this->ua = $_SERVER['HTTP_USER_AGENT'];
		$this->checkPlatform();
		$this->checkBrowser();
		$this->checkEngine();

		// add short version
		if ($this->version != 'unknown') $this->shortversion = substr($this->version, 0, strpos($this->version, '.')); else $this->shortversion = 'unknown';
	}

	/**
	 */
	protected function checkPlatform()
	{
		if (preg_match("/iPhone/", $this->ua) || preg_match("/iPod/", $this->ua)) {
			$this->platform = "iphone";
		} elseif (preg_match("/iPad/", $this->ua)) {
			$this->platform = "ipad";
		} elseif (preg_match("/Android/", $this->ua)) {
			$this->platform = "android";
		} elseif (preg_match("/Mobile/i", $this->ua)) {
			$this->platform = "mobile";
		} elseif (preg_match("/win/i", $this->ua)) {
			$this->platform = "win";
		} elseif (preg_match("/mac/i", $this->ua)) {
			$this->platform = "mac";
		} elseif (preg_match("/linux/i", $this->ua)) {
			$this->platform = "linux";
		} else {
			$this->platform = "unknown";
		}

		return $this->platform;
	}

	/**
	 */
	protected function checkEngine()
	{
		switch ($this->name) {
			case 'ie':
				$this->engine = 'trident';
				break;
			case 'minefield':
			case 'firefox':
				$this->engine = 'gecko';
				break;
			case 'android':
			case 'ipad':
			case 'iphone':
			case 'chrome':
			case 'safari':
				$this->engine = 'webkit';
				break;
			case 'opera':
				$this->engine = 'presto';
				break;
			default:
				$this->engine = 'unknown';
				break;
		}
	}

	/**
	 */
	protected function checkBrowser()
	{
		// IE
		if (preg_match('/msie/i', $this->ua) && !preg_match('/opera/i', $this->ua)) {
			$result        = explode(' ', stristr(str_replace(';', ' ', $this->ua), 'msie'));
			$this->name    = 'ie';
			$this->version = $result[1];
		} //IE 11+
		elseif (preg_match('#Trident\/.*rv:([0-9]{1,}[\.0-9]{0,})#i',$this->ua,$matches)) {
			$this->name    = 'ie';
			$this->version = $matches[1];
		} // Firefox
		elseif (preg_match('/Firefox/', $this->ua)) {
			$result        = explode('/', stristr($this->ua, 'Firefox'));
			$version       = explode(' ', $result[1]);
			$this->name    = 'firefox';
			$this->version = $version[0];
		} // Minefield
		elseif (preg_match('/Minefield/', $this->ua)) {
			$result        = explode('/', stristr($this->ua, 'Minefield'));
			$version       = explode(' ', $result[1]);
			$this->name    = 'minefield';
			$this->version = $version[0];
		} // Chrome
		elseif (preg_match('/Chrome/', $this->ua)) {
			$result        = explode('/', stristr($this->ua, 'Chrome'));
			$version       = explode(' ', $result[1]);
			$this->name    = 'chrome';
			$this->version = $version[0];
		} //Safari
		elseif (preg_match('/Safari/', $this->ua) && !preg_match('/iPhone/', $this->ua) && !preg_match('/iPod/', $this->ua) && !preg_match('/iPad/', $this->ua)) {
			$result     = explode('/', stristr($this->ua, 'Version'));
			$this->name = 'safari';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = 'unknown';
			}
		} // Opera
		elseif (preg_match('/opera/i', $this->ua)) {
			$result = stristr($this->ua, 'opera');

			if (preg_match('/\//', $result)) {
				$result        = explode('/', $result);
				$version       = explode(' ', $result[1]);
				$this->name    = 'opera';
				$this->version = $version[0];
			} else {
				$version       = explode(' ', stristr($result, 'opera'));
				$this->name    = 'opera';
				$this->version = $version[1];
			}
		} // iPhone/iPod
		elseif (preg_match('/iPhone/', $this->ua) || preg_match('/iPod/', $this->ua)) {
			$result     = explode('/', stristr($this->ua, 'Version'));
			$this->name = 'iphone';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = 'unknown';
			}
		} // iPad
		elseif (preg_match('/iPad/', $this->ua)) {
			$result     = explode('/', stristr($this->ua, 'Version'));
			$this->name = 'ipad';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = 'unknown';
			}
		} // Android
		elseif (preg_match('/Android/', $this->ua)) {
			$result     = explode('/', stristr($this->ua, 'Version'));
			$this->name = 'android';
			if (isset ($result[1])) {
				$version       = explode(' ', $result[1]);
				$this->version = $version[0];
			} else {
				$this->version = "unknown";
			}
		} else {
			$this->name    = "unknown";
			$this->version = "unknown";
		}
	}


	protected function createChecks()
	{
		$this->checks = array(
			'', // filename
			'-' . $this->name, // browser check
			'-' . $this->platform, // platform check
			'-' . $this->engine, // render engine
			'-' . $this->name . '-' . $this->platform, // browser + platform check
			'-' . $this->name . $this->shortversion, // short browser version check
			'-' . $this->name . $this->version, // longbrowser version check
			'-' . $this->name . $this->shortversion . '-' . $this->platform, // short browser version + platform check
			'-' . $this->name . $this->version . '-' . $this->platform // longbrowser version + platform check
		);
	}

	public function getChecks($file, $keep_path = false)
	{
		$checkfiles = array();
		$ext        = substr($file, strrpos($file, '.'));
		$path       = ($keep_path) ? dirname($file) . DS : '';
		$filename   = basename($file, $ext);
		foreach ($this->checks as $suffix) {
			$checkfiles[] = $path . $filename . $suffix . $ext;
		}
		return $checkfiles;
	}

	/**
	 * @return string
	 */
	public function getUa()
	{
		return $this->ua;
	}

	/**
	 * @return string
	 */
	public function getEngine()
	{
		return $this->engine;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	/**
	 * @return string
	 */
	public function getShortversion()
	{
		return $this->shortversion;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	public function getShortName()
	{
		return $this->name.$this->shortversion;
	}
}