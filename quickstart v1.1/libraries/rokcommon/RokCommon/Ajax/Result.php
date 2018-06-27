<?php
/**
 * @version   $Id: Result.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Ajax_Result
{

	/**
	 *
	 */
	const STATUS_SUCCESS = 'success';
	/**
	 *
	 */
	const STATUS_ERROR = 'error';

	/**
	 * @var string error|success
	 */
	public $status = self::STATUS_SUCCESS;

	/**
	 * @var string
	 */
	public $message = '';

	/**
	 * The model specific payload
	 * @var mixed
	 */
	public $payload;


	/**
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param mixed $payload
	 */
	public function setPayload($payload)
	{
		$this->payload = $payload;
	}

	/**
	 * @return mixed
	 */
	public function getPayload()
	{
		return $this->payload;
	}


	/**
	 * Sets the result to be an error
	 */
	public function setAsError()
	{
		$this->status = self::STATUS_ERROR;
	}

	/**
	 * Sets the result to be a success
	 */
	public function setAsSuccess()
	{
		$this->status = self::STATUS_SUCCESS;
	}
}
