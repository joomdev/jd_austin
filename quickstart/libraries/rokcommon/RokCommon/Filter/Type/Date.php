<?php
/**
 * @version   $Id: Date.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Filter_Type_Date extends RokCommon_Filter_Type
{
	/**
	 * @var string
	 */
	protected $type = 'date';
	/**
	 * @var bool
	 */
	protected $isselector = true;

	/**
	 * @var array
	 */
	protected $selection_types = array(
		'withinlast'        => 'RokCommon_Filter_Type_DateWithinLast',
		'exactly'           => 'RokCommon_Filter_Type_DateSelection',
		'before'            => 'RokCommon_Filter_Type_DateSelection',
		'after'             => 'RokCommon_Filter_Type_DateSelection',
		'today'             => 'RokCommon_Filter_Type_DateHiddenEnabled',
		'yesterday'         => 'RokCommon_Filter_Type_DateHiddenEnabled',
		'thisweek'          => 'RokCommon_Filter_Type_DateHiddenEnabled',
		'thismonth'         => 'RokCommon_Filter_Type_DateHiddenEnabled',
		'thisyear'          => 'RokCommon_Filter_Type_DateHiddenEnabled'
	);

	/**
	 * @var array
	 */
	protected $selection_labels = array(
		'withinlast'        => 'within last',
		'exactly'           => 'exactly',
		'before'            => 'before',
		'after'             => 'after',
		'today'             => 'today',
		'yesterday'         => 'yesterday',
		'thisweek'          => 'this week',
		'thismonth'         => 'this month',
		'thisyear'          => 'this year'
	);

	/**
	 * @return string
	 */
	public function getChunkSelectionRender()
	{
		return rc__('ROKCOMMON_FILTER_DATE_RENDER', $this->getTypeDescription());
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
		return rc__('ROKCOMMON_FILTER_DATE_RENDER', parent::render($name, $type, $values));
	}
}
