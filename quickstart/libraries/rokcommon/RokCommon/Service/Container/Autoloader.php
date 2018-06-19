<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * RokCommon_Service_Container_Autoloader is an autoloader for the service container classes.
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: Autoloader.php 10831 2013-05-29 19:32:17Z btowles $
 */
class RokCommon_Service_Container_Autoloader
{
  /**
   * Registers RokCommon_Service_Container_Autoloader as an SPL autoloader.
   */
  static public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');
    spl_autoload_register(array(new self, 'autoload'));
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    if (0 !== strpos($class, 'sfService'))
    {
      return false;
    }

    require dirname(__FILE__).'/'.$class.'.php';

    return true;
  }
}
