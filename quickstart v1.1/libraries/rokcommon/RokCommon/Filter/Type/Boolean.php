<?php
/**
 * @version   $Id: Boolean.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Filter_Type_Boolean extends RokCommon_Filter_Type
{
	/**
	 * @var string
	 */
	protected $type = 'boolean';

	/**
	 * @var array
	 */
	protected $select_options = array(
		'yes'=> 'yes',
		'no' => 'no'
	);

	/**
	 * @return string
	 */
	public function getChunkRender()
	{
		return $this->getSelectList();
	}

	/**
	 * @param string $name
	 * @param null   $value
	 *
	 * @return string
	 */
	protected function getSelectList($name = RokCommon_Filter_Type::JAVASCRIPT_NAME_VARIABLE, $value = null)
	{
		$options = array();
		$attribs = array('class'   => $this->type.' chzn-done',
		                 'data-key'=> $this->type
		);
		foreach ($this->select_options as $select_option_value => $select_option_label) {
			$option    = new RokCommon_HTML_Select_Option($select_option_value, $select_option_label, $value == $select_option_value);
			$options[] = $option;
		}
		$service = $this->selectRenderer;
		/** @var $renderer RokCommon_HTML_ISelect */
		$renderer = $this->container->{$service};
		return $renderer->getList($name, $options, $attribs);
	}

	/**
	 * @return string
	 */
	public function getChunkSelectionRender()
	{
		return rc__('ROKCOMMON_FILTER_BOOLEAN_RENDER', $this->getTypeDescription());
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
		return rc__('ROKCOMMON_FILTER_BOOLEAN_RENDER', $this->getSelectList($name, $value));
	}
}
