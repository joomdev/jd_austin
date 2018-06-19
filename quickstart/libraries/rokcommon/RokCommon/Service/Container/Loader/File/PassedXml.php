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
 * RokCommon_Service_Container_Loader_File_Xml loads XML files service definitions.
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: PassedXml.php 10831 2013-05-29 19:32:17Z btowles $
 */
class RokCommon_Service_Container_Loader_File_PassedXml extends RokCommon_Service_Container_Loader_File_Xml
{
  public function doLoad($xmls)
  {
    return $this->parse($xmls);
  }
}
