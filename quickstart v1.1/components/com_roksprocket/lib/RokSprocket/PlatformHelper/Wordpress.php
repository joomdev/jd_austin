<?php
/**
 * @version   $Id: Wordpress.php 19249 2014-02-27 19:21:50Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_PlatformHelper_Wordpress implements RokSprocket_PlatformHelper
{
	public function getCurrentTemplate()
	{
		$container = RokCommon_Service::getContainer();
		/** @var $pi RokCommon_IPlatformInfo */
		$pi = $container->getService('platforminfo');
		return $pi->getDefaultTemplate();
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
		$container = RokCommon_Service::getContainer();
		/** @var $model RokSprocket_Model_Edit */
		$model = $container->getService('roksprocket.edit.model');
		$item = $model->get($id);
		return $item->getParams();
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
		$return = call_user_func_array($callback, $args);
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
		return $items;
	}

	/**
	 *
	 * @param string              $output
	 *
	 * @param \RokCommon_Registry $parameters
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function processOutputForEvents($output, RokCommon_Registry $parameters)
	{
		return $output;
	}


	/**
	 * Gets the cache directory for the platform
	 *
	 * @return string the absolute path to the cache dir
	 */
	public function getCacheDir()
	{
		return ROKSPROCKET_PLUGIN_PATH. DS.'cache';
	}

	public function getCacheUrl()
	{
		return ROKSPROCKET_PLUGIN_URL.'/cache';
	}

	/**
	 * Converting the site URL to fit to the HTTP request
	 */
	public function cleanup($buffer)
	{
		$container = RokCommon_Service::getContainer();

		/** @var $platforminfo RokCommon_IPlatformInfo */
		$platforminfo = $container->getService('platforminfo');

		if (is_admin()) {
			return $buffer;
		}

		//Replace src links
		$base	= $platforminfo->getRootUrl().'/';

//		$regex  = '#href="index.php\?([^"]*)#m';
//		$buffer = preg_replace_callback($regex, array('RokSprocket_PlatformHelper_Joomla', 'route'), $buffer);
//        $this->checkBuffer($buffer);

		$protocols	= '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$regex		= '#(src|href|poster)="(?!/|'.$protocols.'|\#|\')([^"]*)"#m';
		$buffer		= preg_replace($regex, "$1=\"$base\$2\"", $buffer);
        $this->checkBuffer($buffer);
		$regex		= '#(onclick="window.open\(\')(?!/|'.$protocols.'|\#)([^/]+[^\']*?\')#m';
		$buffer		= preg_replace($regex, '$1'.$base.'$2', $buffer);
        $this->checkBuffer($buffer);

		// ONMOUSEOVER / ONMOUSEOUT
		$regex		= '#(onmouseover|onmouseout)="this.src=([\']+)(?!/|'.$protocols.'|\#|\')([^"]+)"#m';
		$buffer	= preg_replace($regex, '$1="this.src=$2'. $base .'$3$4"', $buffer);
        $this->checkBuffer($buffer);

		// Background image
		$regex		= '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|'.$protocols.'|\#)([^\)\'\"]+)[\'\"]?\)#m';
		$buffer	= preg_replace($regex, 'style="$1: url(\''. $base .'$2$3\')', $buffer);
        $this->checkBuffer($buffer);

		// OBJECT <param name="xx", value="yy"> -- fix it only inside the <param> tag
		$regex		= '#(<param\s+)name\s*=\s*"(movie|src|url)"[^>]\s*value\s*=\s*"(?!/|'.$protocols.'|\#|\')([^"]*)"#m';
		$buffer	= preg_replace($regex, '$1name="$2" value="' . $base . '$3"', $buffer);
        $this->checkBuffer($buffer);

		// OBJECT <param value="xx", name="yy"> -- fix it only inside the <param> tag
		$regex		= '#(<param\s+[^>]*)value\s*=\s*"(?!/|'.$protocols.'|\#|\')([^"]*)"\s*name\s*=\s*"(movie|src|url)"#m';
		$buffer	= preg_replace($regex, '<param value="'. $base .'$2" name="$3"', $buffer);
        $this->checkBuffer($buffer);

		// OBJECT data="xx" attribute -- fix it only in the object tag
		$regex =	'#(<object\s+[^>]*)data\s*=\s*"(?!/|'.$protocols.'|\#|\')([^"]*)"#m';
		$buffer	= preg_replace($regex, '$1data="' . $base . '$2"$3', $buffer);
        $this->checkBuffer($buffer);

		return $buffer;
	}

    private function checkBuffer($buffer) {
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
            throw new Exception($message);
        }
    }
}