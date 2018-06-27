<?php
/**
 * @version   $Id: Number.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Filter_Type_Number extends RokCommon_Filter_Type
{
	/**
	 * @var string
	 */
	protected $type = 'number';
	/**
	 * @var bool
	 */
	protected $isselector = true;

	/**
	 * @var array
	 */
	protected $selection_types = array(
		'equals'        => 'RokCommon_Filter_Type_NumberEntry',
		'greaterthan'   => 'RokCommon_Filter_Type_NumberEntry',
		'lessthan'      => 'RokCommon_Filter_Type_NumberEntry',
		'isnot'         => 'RokCommon_Filter_Type_NumberEntry'
	);

	/**
	 * @var array
	 */
	protected $selection_labels = array(
		'equals'        => 'equals',
		'greaterthan'   => 'greater than',
		'lessthan'      => 'less than',
		'isnot'         => 'is not'
	);

	/**
	 * @return string
	 */
	public function getChunkSelectionRender()
	{
		return rc__('ROKCOMMON_FILTER_NUMBER_RENDER', $this->getTypeDescription());
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
		return rc__('ROKCOMMON_FILTER_NUMBER_RENDER', parent::render($name, $type, $values));
	}
}
