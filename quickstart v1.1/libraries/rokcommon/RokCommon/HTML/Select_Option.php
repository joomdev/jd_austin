<?php
/**
 * @version   $Id: Select_Option.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_HTML_Select_Option
{


	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string[]
	 */
	protected $attribs;

	/**
	 * @var bool
	 */
	protected $selected = false;

	/**
	 * @param string $value
	 * @param string $label
	 * @param bool   $selected
	 * @param array  $attribs
	 */
	public function __construct($value, $label, $selected = false, $attribs = array())
	{
		$this->value    = $value;
		$this->label    = $label;
		$this->selected = $selected;
		$this->attribs  = $attribs;
	}


	/**
	 * @param $attribs
	 */
	public function setAttribs($attribs)
	{
		$this->attribs = $attribs;
	}

	/**
	 * @return array|string[]
	 */
	public function getAttribs()
	{
		return $this->attribs;
	}

	/**
	 * @param string $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param boolean $selected
	 */
	public function setSelected($selected)
	{
		$this->selected = $selected;
	}

	/**
	 * @return boolean
	 */
	public function isSelected()
	{
		return $this->selected;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function getHTML()
	{
		$internal = array();

		if (!empty($this->value)) {
			$internal[] = 'value="' . $this->value . '"';
		}

		foreach ($this->attribs as $attrib_name => $attrib_value) {
			$internal[] = $attrib_name . '="' . $attrib_value . '"';
		}

		if ($this->selected) {
			$internal[] = 'selected="selected"';
		}
		$internal_html = implode(' ', $internal);


		$html = '<option ' . $internal_html . '>' . rc__($this->label) . '</option>';
		return $html;
	}
}
