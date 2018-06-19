<?php
/**
 * @version   $Id: Dispatcher.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Derived from:
 *
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * RokCommon_Dispatcher implements a dispatcher object.
 *
 * @see        http://developer.apple.com/documentation/Cocoa/Conceptual/Notifications/index.html Apple's Cocoa framework
 *
 * @package    symfony
 * @subpackage event_dispatcher
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: Dispatcher.php 10831 2013-05-29 19:32:17Z btowles $
 */
class RokCommon_Dispatcher
{
	/**
	 * @var array
	 */
	protected $listeners = array();

	/**
	 * Connects a listener to a given event name.
	 *
	 * @param string  $name      An event name
	 * @param mixed   $listener  A PHP callable
	 * @param int     $priority
	 */
	public function connect($name, $listener, $priority = 10)
	{
		if (!isset($this->listeners[$name][$priority])) {
			$this->listeners[$name][$priority] = array();
		}

		$this->listeners[$name][$priority][] = $listener;
		ksort($this->listeners[$name]);
	}

	/**
	 * Disconnects a listener for a given event name.
	 *
	 * @param string   $name      An event name
	 * @param mixed    $listener  A PHP callable
	 *
	 * @param int      $priority
	 *
	 * @return mixed false if listener does not exist, null otherwise
	 */
	public function disconnect($name, $listener, $priority = 10)
	{
		if (!isset($this->listeners[$name][$priority])) {
			return false;
		}

		foreach ($this->listeners[$name][$priority] as $i => $callable) {
			if ($listener === $callable) {
				unset($this->listeners[$name][$i]);
			}
		}
		ksort($this->listeners[$name]);
	}

	/**
	 * Notifies all listeners of a given event.
	 *
	 * @param RokCommon_Event $event A RokCommon_Event instance
	 *
	 * @return RokCommon_Event The RokCommon_Event instance
	 */
	public function notify(RokCommon_Event $event)
	{
		foreach ($this->listeners[$event->getName()] as $priority_listeners) {
			foreach ($priority_listeners as $listener) {
				call_user_func($listener, $event);
			}
		}
		return $event;
	}

	/**
	 * Notifies all listeners of a given event until one returns a non null value.
	 *
	 * @param  RokCommon_Event $event A RokCommon_Event instance
	 *
	 * @return RokCommon_Event The RokCommon_Event instance
	 */
	public function notifyUntil(RokCommon_Event $event)
	{
		if (isset($this->listeners[$event->getName()])) {
			foreach ($this->listeners[$event->getName()] as $priority_listeners) {
				foreach ($priority_listeners as $listener) {
					if (call_user_func($listener, $event)) {
						$event->setProcessed(true);
						break;
					}
				}
			}
		}

		return $event;
	}

	/**
	 * Filters a value by calling all listeners of a given event.
	 *
	 * @param  RokCommon_Event  $event   A RokCommon_Event instance
	 * @param  mixed            $value   The value to be filtered
	 *
	 * @return RokCommon_Event The RokCommon_Event instance
	 */
	public function filter(RokCommon_Event $event, $value)
	{
		if (isset($this->listeners[$event->getName()])) {
			foreach ($this->listeners[$event->getName()] as $priority_listeners) {
				foreach ($priority_listeners as $listener) {
					$value = call_user_func_array($listener, array($event, $value));
				}
			}

			$event->setReturnValue($value);
		}
		return $event;
	}

	/**
	 * Returns true if the given event name has some listeners.
	 *
	 * @param  string   $name    The event name
	 *
	 * @return Boolean true if some listeners are connected, false otherwise
	 */
	public function hasListeners($name)
	{
		if (!isset($this->listeners[$name])) {
			$this->listeners[$name] = array();
		}
		$listeners = new RecursiveArrayIterator($this->listeners[$name]);
		return (boolean)iterator_count($listeners);
	}

	/**
	 * Returns all listeners associated with a given event name.
	 *
	 * @param  string   $name    The event name
	 *
	 * @return array  An array of listeners
	 */
	public function getListeners($name)
	{
		$ret = array();
		if (!isset($this->listeners[$name])) {
			return $ret;
		}
		foreach ($this->listeners[$name] as $priority_listeners) {
			foreach ($priority_listeners as $listener) {
				$ret = $listener;
			}
		}
		return $ret;
	}
}
