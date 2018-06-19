<?php
/**
 * @package angifw
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

/**
 * Pimple service provider interface.
 *
 * @author Fabien Potencier
 * @author Dominik Zogg
 *
 * @codeCoverageIgnore
 */
interface ServiceProviderInterface
{
	/**
	 * Registers services on the given container.
	 *
	 * This method should only be used to configure services and parameters.
	 * It should not get services.
	 *
	 * @param APimple $pimple An Container instance
	 */
	public function register(APimple $pimple);
}
