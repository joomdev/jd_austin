<?php
/**
 * @version   $Id: Collection.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Collection implements IteratorAggregate, ArrayAccess, Countable
{
	/**
	 * @var array
	 */
	protected $items = array();

	/**
	 *
	 */
	public function __construct()
	{

	}

	/**
	 * @param $item
	 */
	public function addItem($item)
	{
		$this->items[] = $item;
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * @param $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->items);
	}

	/**
	 * @param $offset
	 * @param $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->items[$offset] = $value;
	}

	/**
	 * @param $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->items[$offset];
	}

	/**
	 * @param $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	/**
	 * @return int|void
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * @param int  $offset
	 * @param null $length
	 *
	 * @return array
	 */
	public function slice($offset = 0, $length = null)
	{
        if ($length === null) $length = $this->count();
        $classtype = get_class($this);
        $output    = new $classtype();
        $slices    = array_slice($this->items, $offset, $length, true);
        foreach ($slices as $sliced_item_id => &$sliced_item) {
            $output[$sliced_item_id] = $sliced_item;
        }
        return $output;

	}

	/**
	 * @param $length
	 *
	 * @return array
	 */
	public function trim($length)
	{
		return $this->slice(0, $length);
	}
}
