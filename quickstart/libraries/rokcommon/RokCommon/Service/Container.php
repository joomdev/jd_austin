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
 * RokCommon_Service_Container is the interface implemented by service container classes.
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: Container.php 27318 2015-03-04 12:51:02Z matias $
 */
interface RokCommon_Service_Container
{
    public function setParameters(array $parameters);

    public function addParameters(array $parameters);

    public function getParameters();

    public function getParameter($name, $default=null);

    public function setParameter($name, $value);

    public function hasParameter($name);

    public function setService($id, $service);

    public function getService($id);

    public function hasService($name);
}
