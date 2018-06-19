<?php
/**
 * @version   $Id: item.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


// no direct access
defined('_JEXEC') or die;

/**
 * Client table
 *
 * @package		Joomla.Administrator
 * @subpackage     com_banners
 * @since          1.6
 */
class RokSprocketTableItem extends JTable
{
	function __construct(&$_db)
	{
		parent::__construct('#__roksprocket_items', 'id', $_db);
	}


	/**
	 * Overloaded bind function.
	 *
	 * @param   array  $array   Named array.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable::bind
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{

		if (isset($array['params']) && is_array($array['params'])) {
			if (@ini_get('magic_quotes_gpc')=='1') {
				$array['params'] = self::_stripSlashesRecursive($array['params']);
			}
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 *
	 * @param $value
	 *
	 * @return array|string
	 */
	protected static function _stripSlashesRecursive($value)
	{
		$value = is_array($value) ? array_map(array( 'RokSprocketTableItem','_stripSlashesRecursive'
		                                      ), $value) : stripslashes($value);
		return $value;
	}
}