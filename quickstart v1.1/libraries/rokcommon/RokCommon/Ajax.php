<?php
/**
 * @version   $Id: Ajax.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Ajax
{
	const JSON_ENCODING = 'json';
	const FORM_ENCODING = 'form';
	/**
	 *
	 */
	const DEFAULT_MODEL_PREFIX = 'RokCommon_Ajax_Model_';

	/**
	 * @param        $path
	 * @param string $prefix
	 * @param int    $priority
	 *
	 * @throws RokCommon_Ajax_Exception
	 */
	public static function addModelPath($path, $prefix = self::DEFAULT_MODEL_PREFIX, $priority = 10)
	{
		try {
			$container = RokCommon_Service::getContainer();
			/** @var $ajaxModelLoader RokCommon_ClassLoader_PrefixedLoader */
			$ajaxModelLoader = $container->getService('ajax.model.loader');
			$ajaxModelLoader->addPath($path, $prefix, $priority);
		} catch (RokCommon_ClassLoader_Exception $e) {
			throw new RokCommon_Ajax_Exception('Error adding model path.', 0, $e);
		}
	}


	/**
	 * @param string $model
	 *
	 * @return RokCommon_Ajax_Model
	 */
	protected static function &getModel($model)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $ajaxModelLoader RokCommon_ClassLoader_PrefixedLoader */
		$ajaxModelLoader = $container->getService('ajax.model.loader');
		$model           = $ajaxModelLoader->getItem($model, null, 'RokCommon_Ajax_Model');
		return $model;
	}

	/**
	 * @param string   $model
	 * @param string   $action
	 * @param array    $params
	 *
	 * @throws RokCommon_Ajax_Exception
	 * @return string
	 */
	public static function run($model, $action, $params, $encoding = self::JSON_ENCODING)
	{
		// Set up an independent AJAX error handler
		set_error_handler(array('RokCommon_Ajax', 'error_handler'));
		set_exception_handler(array('RokCommon_Ajax', 'exception_handler'));

		while (@ob_end_clean()) ; // clean any pending output buffers
		ob_start(); // start a fresh one

		$result = null;
		try {
			// get a model class instance
			$modelInstance = self::getModel($model);
			if ($encoding == self::JSON_ENCODING) {
				$decoded_params = json_decode($params);
				if (null == $decoded_params && strlen($params) > 0) {
					throw new RokCommon_Ajax_Exception('Invalid JSON for params');
				}
				$params = $decoded_params;
			}
			// set the result to the run
			$result = $modelInstance->run($action, $params);
		} catch (Exception $ae) {
			$result = new RokCommon_Ajax_Result();
			$result->setAsError();
			$result->setMessage($ae->getMessage());
		}

		$encoded_result = json_encode($result);

		// restore normal error handling;
		restore_error_handler();
		restore_exception_handler();

		return $encoded_result;
	}

	/**
	 * @static
	 *
	 * @param Exception $exception
	 */
	public static function exception_handler(Exception $exception)
	{
		echo "Uncaught Exception: " . $exception->getMessage() . "\n";
		echo '[' . $exception->getCode() . '] File: ' . $exception->getFile() . ' Line: ' . $exception->getLine();
	}

	/**
	 * @static
	 *
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 *
	 * @return bool
	 * @throws RokCommon_Ajax_Exception
	 */
	public static function error_handler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return;
		}

		switch ($errno) {
			case E_USER_ERROR:
				echo "ERROR [$errno] $errstr\n";
				echo "  Fatal error on line $errline in file $errfile";
				echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
				echo "Aborting...\n";
				exit(1);
				break;
			case E_USER_WARNING:
				echo "WARNING [$errno] $errstr\n";
				break;
			case E_USER_NOTICE:
				echo "NOTICE [$errno] $errstr\n";
				break;
			case E_STRICT:
				return false;
				break;
			default:
				throw new RokCommon_Ajax_Exception("UNHANDLED ERROR [$errno] $errstr $errfile:$errline");
				break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}

	/**
	 * @param $str
	 *
	 * @return string
	 */
	public static function smartStripSlashes($str)
	{
		$cd1 = substr_count($str, "\"");
		$cd2 = substr_count($str, "\\\"");
		$cs1 = substr_count($str, "'");
		$cs2 = substr_count($str, "\\'");
		$tmp = strtr($str, array(
		                        "\\\""  => "",
		                        "\\'"   => ""
		                   ));
		$cb1 = substr_count($tmp, "\\");
		$cb2 = substr_count($tmp, "\\\\");
		if ($cd1 == $cd2 && $cs1 == $cs2 && $cb1 == 2 * $cb2) {
			return strtr($str, array(
			                        "\\\""  => "\"",
			                        "\\'"   => "'",
			                        "\\\\"  => "\\"
			                   ));
		}
		return $str;
	}
}
