<?php
/**
 * @version   $Id: DateSelection.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Filter_Type_DateSelection extends RokCommon_Filter_Type
{
	/**
	 * @var string
	 */
	protected $type = 'dateselection';

	/**
	 * @return string
	 */
	public function getChunkRender()
	{
		return $this->getInputBox();
	}

	/**
	 * @return string
	 */
	public function getChunkSelectionRender()
	{
		return rc__('ROKCOMMON_FILTER_DATESELECTION_RENDER', $this->getTypeDescription());
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
		$value = (isset($values[$type]) ? $values[$type] : null);
		return rc__('ROKCOMMON_FILTER_DATESELECTION_RENDER', $this->getInputBox($name, $value));
	}

	/**
	 * @param string $name
	 * @param null   $value
	 *
	 * @return string
	 */
	protected function getInputBox($name = RokCommon_Filter_Type::JAVASCRIPT_NAME_VARIABLE, $value = null)
	{
		if (null == $value) {
			$date  = new RokCommon_Date();
			$value = $date->toFormat('%Y-%m-%d');
		}
		return '<input type="text" name="' . $name . '" value="' . $value . '" class="' . $this->type . '" data-key="' . $this->type . '"/><a href="#" title="Select Date" class="date-picker"><i class="icon tool date"></i></a>';
	}

	/**
	 * @param string  $name
	 * @param mixed   $value
	 *
	 * @return string
	 */
	protected function getJavascript($name = self::JAVASCRIPT_NAME_VARIABLE, $value = null)
	{
		$this->javascript = "";
		$this->javascript .= "(function(){";
		$this->javascript .= "RokSprocket.datepicker.attach(%.date-picker !~ input." . $this->type . "%, %.date-picker%);";
		$this->javascript .= "});";
		return $this->javascript;
	}

}
