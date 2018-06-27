<?php
 /**
 * @version   $Id: requirements.php 20029 2014-03-30 21:24:28Z jakub $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.htmlk, i' GNU/GPLv2 only
 */
$errors = array();
if (version_compare(PHP_VERSION, '5.2.8') < 0) {
    $errors[] = 'Needs a minimum PHP version of 5.2.8. You are running PHP version ' . PHP_VERSION;
}

if (!empty($errors)) return $errors;

return true;