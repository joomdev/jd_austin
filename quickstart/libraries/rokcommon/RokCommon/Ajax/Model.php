<?php
/**
 * @version   $Id: Model.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

interface RokCommon_Ajax_Model
{

	/**
	 * @abstract
	 *
	 * @param  $action
	 * @param  $params
	 *
	 * @return RokCommon_Ajax_Result
	 */
	public function run($action, $params);

	/**
	 * @abstract
	 *
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 *
	 * @return mixed
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline);
}
