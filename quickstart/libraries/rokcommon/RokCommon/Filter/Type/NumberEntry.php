<?php
/**
 * @version   $Id: NumberEntry.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Filter_Type_NumberEntry extends RokCommon_Filter_Type
{
	/**
	 * @var string
	 */
	protected $type = 'numberentry';

	/**
	 * @return string
	 */
	public function getChunkRender()
	{
		return $this->getInput();
	}

	/**
	 * @param $name
	 * @param $type
	 * @param $values
	 *
	 * @return string
	 */
	public function render($name, $type, $values)
	{
		$value = (isset($values[$type]) ? $values[$type] : '');
		return rc__('ROKCOMMON_FILTER_NUMBERENTRY_RENDER', $this->getInput($name, $value));
	}

	/**
	 * @return string
	 */
	public function getChunkSelectionRender()
	{
		return rc__('ROKCOMMON_FILTER_NUMBERENTRY_RENDER', $this->getTypeDescription());
	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @return string
	 */
	public function getInput($name = RokCommon_Filter_Type::JAVASCRIPT_NAME_VARIABLE, $value = '')
	{
		return '<input type="text" name="' . $name . '" class="' . $this->type . '" data-key="' . $this->type . '" value="' . $value . '"/>';
	}
}
