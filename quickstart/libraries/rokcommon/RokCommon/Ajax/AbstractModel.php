<?php
/**
 * @version   $Id: AbstractModel.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Ajax_AbstractModel implements RokCommon_Ajax_Model
{
	/**
	 * @param  $action
	 * @param  $params
	 *
	 * @throws Exception
	 * @throws RokCommon_Ajax_Exception
	 * @return RokCommon_Ajax_Result
	 */
	public function run($action, $params)
	{

		try {
			set_error_handler(array($this, 'errorHandler'));
			$action = (empty($action)) ? 'default' : $action;
			if (!method_exists($this, $action)) {
				throw new RokCommon_Ajax_Exception('The ' . $action . ' action does not exist for this model');
			}
			$result = $this->{$action}($params);
			restore_error_handler();
			return $result;
		} catch (Exception $e) {
			throw $e;
		}

	}

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @return bool
     * @throws Exception
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return false;
		}

		switch ($errno) {
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
				throw new Exception(sprintf('%s %s on %s line %s', $this->getErrorType($errno), $errstr, $errfile, $errline));
				break;
			default:
				error_log(sprintf('%s %s on %s line %s', $this->getErrorType($errno), $errstr, $errfile, $errline));
				break;
		}
		return true;
	}

    /**
     * @param $type
     * @return string
     */
    protected function getErrorType($type)
	{
		switch ($type) {
			case E_ERROR: // 1 //
				return 'E_ERROR';
			case E_WARNING: // 2 //
				return 'E_WARNING';
			case E_PARSE: // 4 //
				return 'E_PARSE';
			case E_NOTICE: // 8 //
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16 //
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32 //
				return 'E_CORE_WARNING';
			case E_CORE_ERROR: // 64 //
				return 'E_COMPILE_ERROR';
			case E_CORE_WARNING: // 128 //
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256 //
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512 //
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024 //
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048 //
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096 //
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192 //
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384 //
				return 'E_USER_DEPRECATED';
		}
		return "";
	}

	/**
	 * @param RokCommon_Ajax_Result $result
	 */
	protected function sendDisconnectingReturn(RokCommon_Ajax_Result $result)
	{
		// clean outside buffers;
		while (@ob_end_clean()) ;
		header("Connection: close\r\n");
		header('Content-type: text/plain');
		session_write_close();
		ignore_user_abort(true);
		ob_start();
		echo json_encode($result);
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush(); // Strange behaviour, will not work
		flush(); // Unless both are called !
		while (@ob_end_clean()) ;
		if (!ini_get('safe_mode') && strpos(ini_get('disable_functions'), 'set_time_limit') === false) {
			@set_time_limit(0);
		} else {
			error_log('RokGallery: PHP safe_mode is on or the set_time_limit function is disabled.  This can cause timeouts while processing a job if your max_execution_time is not set high enough');
		}
	}
}
