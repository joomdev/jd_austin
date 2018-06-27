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
 * RokCommon_Service_Container_AbstractDumper is the abstract class for all built-in dumpers.
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: AbstractDumper.php 10831 2013-05-29 19:32:17Z btowles $
 */
abstract class RokCommon_Service_Container_AbstractDumper implements RokCommon_Service_Container_Dumper
{
  protected $container;

  /**
   * Constructor.
   *
   * @param RokCommon_Service_Container_Builder $container The service container to dump
   */
  public function __construct(RokCommon_Service_Container_Builder $container)
  {
    $this->container = $container;
  }

  /**
   * Dumps the service container.
   *
   * @param  array  $options An array of options
   *
   * @return string The representation of the service container
   */
  public function dump(array $options = array())
  {
    throw new LogicException('You must extend this abstract class and implement the dump() method.');
  }
}
