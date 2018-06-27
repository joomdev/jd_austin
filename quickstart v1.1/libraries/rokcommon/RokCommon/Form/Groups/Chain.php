<?php
/**
 * @version   $Id: Chain.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Form_Group_Chain extends RokCommon_Form_AbstractGroup
{
	/**
	 * @var string
	 */
	protected $type = 'chain';

	/**
	 * @var string
	 */
	protected $baseetype = 'group';

	/**
	 * @var GantryFormItem
	 */
	protected $enabler;

	/**
	 * @return string
	 */
	public function getInput()
	{
		global $gantry;
		$buffer = '';

		$buffer .= "<div class='wrapper'>\n";
		foreach ($this->fields as $field) {
			if ($field->element['enabler'] && strtolower((string)$field->element['enabler']) == 'true') {
				$this->enabler = $field;
			}
		}
		foreach ($this->fields as $field) {
			$itemName        = $this->fieldname . "-" . $field->fieldname;
			$field->detached = false;

			if ($field != $this->enabler && isset($this->enabler) && (int)$this->enabler->value == 0) {
				$field->detached = true;
			}

			if ($field->basetype == 'select') $basetype = ' base-selectbox'; else $basetype = ' base-' . $field->basetype;

			$buffer .= '<div class="chain ' . $itemName . ' chain-' . strtolower($field->type) . $basetype . '">' . "\n";
			if (strlen($field->getLabel())) $buffer .= '<span class="chain-label">' . JText::_($field->getLabel()) . '</span>' . "\n";
			$buffer .= $field->getInput();
			$buffer .= "</div>" . "\n";

		}
		$buffer .= "</div>" . "\n";

		return $buffer;
	}
}
