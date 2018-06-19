<?php
 /**
  * @version   $Id: requirements.php 10831 2013-05-29 19:32:17Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
$errors = array();
if (version_compare(PHP_VERSION, '5.2.8') < 0) {
    $errors[] = 'Needs a minimum PHP version of 5.2.8.  You are running PHP version ' . PHP_VERSION;
}

if (!empty($errors)) return $errors;

return true;
