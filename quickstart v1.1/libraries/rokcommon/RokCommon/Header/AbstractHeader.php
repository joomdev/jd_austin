<?php
/**
 * @version   $Id: AbstractHeader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokCommon_Header_AbstractHeader implements RokCommon_IHeader
{
	/**
	 * The Composite context name that holds the paths for script files
	 */
	const SCRIPT_CONTEXT = 'rokcommon_scripts';

	/**
	 * The Composite context name that holds the paths for style files
	 */
	const STYLE_CONTEXT = 'rokcommon_styles';

	/**
	 * @var RokCommon_Service_Container The main RokCommon DI Container
	 */
	protected $container;

	protected $script_files = array();

	protected $style_files = array();

	protected $inline_scripts = array();

	protected $inline_styles = array();

	protected $domready_scripts = array();

	protected $loadevent_scripts = array();

	public function __construct()
	{
		$this->container = RokCommon_Service::getContainer();
	}

	public function addScriptPath($path, $priority = self::DEFAULT_PRIORITY)
	{
		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $this->container->getService('platforminfo');
		foreach($platforminfo->getPathChecks() as $append)
		{
			RokCommon_Composite::addPackagePath(self::SCRIPT_CONTEXT, $path.$append, $priority);
		}
	}

	public function addStylePath($path, $priority = self::DEFAULT_PRIORITY)
	{
		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $this->container->getService('platforminfo');
		foreach($platforminfo->getPathChecks() as $append)
		{
			RokCommon_Composite::addPackagePath(self::STYLE_CONTEXT, $path.$append, $priority);
		}
	}


	public function addScript($file, $order = self::DEFAULT_ORDER)
	{
		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $this->container->getService('platforminfo');

		if (empty($file)) return;

		// If it is a full path or url file check if its external
		if ($platforminfo->isLinkExternal($file)) {
			// Its an external url    just pass it through
			$this->registerScriptPath($file, $order);
		} else {
			// its a local url or path  see if we can convert it to a local URL
			$path = $platforminfo->getPathForUrl($file);
			if ($path !== false) {
				// Local path  and file exists   add it as a local url
				$this->registerScriptPath($platforminfo->getUrlForPath($path), $order);
			} else {
				// cant find the file or not local really   just pass it through
				$this->registerScriptPath($file, $order);
			}
		}
	}

	protected function registerScriptPath($path, $order)
	{
		$this->addIfHigherOrder($path, $path, $order, $this->script_files);
		ksort($this->script_files);
	}

	public function addInlineScript($text, $order = self::DEFAULT_ORDER)
	{
		if (!empty($text)) {
			$md5 = md5($text);
			$this->addIfHigherOrder($text, $md5, $order, $this->inline_scripts);
			ksort($this->inline_scripts);
		}
	}

	public function addStyle($file, $order = self::DEFAULT_ORDER)
	{

		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $this->container->getService('platforminfo');

		if (empty($file)) return;

		// If it is a full path or url file check if its external
		if ($platforminfo->isLinkExternal($file)) {
			// Its an external url    just pass it through
			$this->registerStylePath($file, $order);
		} else {
			// its a local url or path  see if we can convert it to a local URL
			$path = $platforminfo->getPathForUrl($file);
			if ($path !== false) {
				// Local path  and file exists   add it as a local url
				$this->registerStylePath($platforminfo->getUrlForPath($path), $order);
			} else {
				// cant find the file or not local really   just pass it through
				$this->registerStylePath($file, $order);
			}
		}
	}

	protected function registerStylePath($path, $order)
	{
		$this->addIfHigherOrder($path, $path, $order, $this->style_files);
		ksort($this->style_files);
	}

	public function addInlineStyle($text, $order = self::DEFAULT_ORDER)
	{
		if (!empty($text)) {
			$md5 = md5($text);
			$this->addIfHigherOrder($text, $md5, $order, $this->inline_styles);
			ksort($this->inline_styles);
		}
	}

	public function addDomReadyScript($js, $order = self::DEFAULT_ORDER)
	{

		if (!empty($js)) {
			$md5 = md5($js);
			$this->addIfHigherOrder($js, $md5, $order, $this->domready_scripts);
			ksort($this->domready_scripts);
		}
	}

	public function addLoadScript($js, $order = self::DEFAULT_ORDER)
	{
		if (!empty($js)) {
			$md5 = md5($js);
			$this->addIfHigherOrder($js, $md5, $order, $this->loadevent_scripts);
			ksort($this->loadevent_scripts);
		}
	}

	protected function addIfHigherOrder($data, $key, $order, &$array)
	{
		$found_priority = $this->checkForOrderedEntry($key, $array);
		if ($found_priority !== false) {
			if ($found_priority < $order) {
				// remove duplicate entry
				unset($array[$found_priority][$key]);
				if (count($array[$found_priority]) == 0) {
					// remove the priority lev le if its empty
					unset($array[$found_priority]);
				}
				$array[$order][$key] = $data;
			}
		} else {
			$array[$order][$key] = $data;
		}
	}

	protected function checkForOrderedEntry($key, &$array)
	{
		foreach ($array as $order => $order_array) {
			foreach ($order_array as $searching_key => $searching_entry) {
				if ($key === $searching_key) {
					return $order;
				}
			}
		}
		return false;
	}

	public function reset()
	{
		$this->script_files      = array();
		$this->inline_scripts    = array();
		$this->style_files       = array();
		$this->inline_styles     = array();
		$this->domready_scripts  = array();
		$this->loadevent_scripts = array();
	}


	/**
	 * @param      $file
	 * @param bool $keep_path
	 *
	 * @internal param $filename
	 *
	 * @return array
	 */
	protected function getBrowserChecks($file, $keep_path = false)
	{
		$ext      = substr($file, strrpos($file, '.'));
		$path     = ($keep_path) ? dirname($file) . DS : '';
		$filename = basename($file, $ext);

		/** @var $browser RokCommon_Browser */
		$browser = $this->container->getService('browser');
		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $this->container->getService('platforminfo');

		$checks = $browser->getChecks($file, $keep_path);

		//TODO turn on RTL when RokCommon RTL is enabled and the platform is currently RTL
//		if ($platforminfo->isRTL() && $this->get('rtl-enabled')) {
//			$checks[] = $path . $filename . '-rtl' . $ext;
//		}
		return $checks;
	}
}
