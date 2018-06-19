<?php
/**
 * @version   $Id: JoomlaItemNameHandler.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Form_JoomlaItemNameHandler implements RokCommon_Form_IItemNameHandler
{

	/**
	 * @param string       $name
	 * @param string|null  $group
	 * @param string|null  $formcontrol
	 * @param bool         $multiple
	 *
	 * @return string the name to use for the html tag
	 */
	public function getName($name, $group = null, $formcontrol = null, $multiple = false)
	{
		// Initialise variables.
		$retname = '';

		// If there is a form control set for the attached form add it first.
		if ($formcontrol) {
			$retname .= $formcontrol;
		}

		// If the field is in a group add the group control to the field name.
		if ($group) {
			// If we already have a name segment add the group control as another level.
			$groups = explode('.', $group);
			if ($retname) {
				foreach ($groups as $group) {
					$retname .= '[' . $group . ']';
				}
			} else {
				$retname .= array_shift($groups);
				foreach ($groups as $group) {
					$retname .= '[' . $group . ']';
				}
			}
		}

		// If we already have a name segment add the field name as another level.
		if ($retname) {
			$retname .= '[' . $name . ']';
		} else {
			$retname .= $name;
		}

		// If the field should support multiple values add the final array segment.
		if ($multiple) {
			$retname .= '[]';
		}

		return $retname;
	}

	/**
	 * @param string          $name
	 * @param string|null     $id
	 * @param string|null     $group
	 * @param string|null     $formcontrol
	 * @param bool            $multiple
	 *
	 * @return string the id to use for the html tag
	 */
	public function getId($name, $id = null, $group = null, $formcontrol = null, $multiple = false)
	{
		// Initialise variables.
		$retid = '';

		// If there is a form control set for the attached form add it first.
		if ($formcontrol) {
			$retid .= $formcontrol;
		}

		// If the field is in a group add the group control to the field id.
		if ($group) {
			// If we already have an id segment add the group control as another level.
			if ($retid) {
				$retid .= '_' . str_replace('.', '_', $group);
			} else {
				$retid .= str_replace('.', '_', $group);
			}
		}

		// If we already have an id segment add the field id/name as another level.
		if ($retid) {
			$retid .= '_' . ($id ? $id : $name);
		} else {
			$retid .= ($id ? $id : $name);
		}

		// Clean up any invalid characters.
		$retid = preg_replace('#\W#', '_', $retid);

		return $retid;
	}

}
