<?php
/**
 * @version   $Id: Exception.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Exception extends Exception
{
    public function __construct($message)
    {
        $container = RokCommon_Service::getContainer();
        $container->roksprocket_logger->warning($message);
        parent::__construct($message);
    }
}