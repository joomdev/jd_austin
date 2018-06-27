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
 * RokCommon_Service_Container_Dumper_Yaml dumps a service container as a YAML string.
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: Yaml.php 10831 2013-05-29 19:32:17Z btowles $
 */
class RokCommon_Service_Container_Dumper_Yaml extends RokCommon_Service_Container_AbstractDumper
{
  /**
   * Dumps the service container as an YAML string.
   *
   * @param  array  $options An array of options
   *
   * @return string A YAML string representing of the service container
   */
  public function dump(array $options = array())
  {
    return $this->addParameters()."\n".$this->addServices();
  }

  protected function addService($id, $definition)
  {
    $code = "  $id:\n";
    $code .= sprintf("    class: %s\n", $definition->getClass());

    if ($definition->getFile())
    {
      $code .= sprintf("    file: %s\n", $definition->getFile());
    }

    if ($definition->getConstructor())
    {
      $code .= sprintf("    constructor: %s\n", $definition->getConstructor());
    }

    if ($definition->getArguments())
    {
      $code .= sprintf("    arguments: %s\n", sfYaml::dump($this->dumpValue($definition->getArguments()), 0));
    }

    if ($definition->getMethodCalls())
    {
      $code .= sprintf("    calls:\n      %s\n", str_replace("\n", "\n      ", sfYaml::dump($this->dumpValue($definition->getMethodCalls()), 1)));
    }

    if (!$definition->isShared())
    {
      $code .= "    shared: false\n";
    }

    if ($callable = $definition->getConfigurator())
    {
      if (is_array($callable))
      {
        if (is_object($callable[0]) && $callable[0] instanceof RokCommon_Service_Reference)
        {
          $callable = array($this->getServiceCall((string) $callable[0]), $callable[1]);
        }
        else
        {
          $callable = array($callable[0], $callable[1]);
        }
      }

      $code .= sprintf("    configurator: %s\n", sfYaml::dump($callable, 0));
    }

    return $code;
  }

  protected function addServiceAlias($alias, $id)
  {
    return sprintf("  %s: @%s\n", $alias, $id);
  }

  protected function addServices()
  {
    if (!$this->container->getServiceDefinitions())
    {
      return '';
    }

    $code = "services:\n";
    foreach ($this->container->getServiceDefinitions() as $id => $definition)
    {
      $code .= $this->addService($id, $definition);
    }

    foreach ($this->container->getAliases() as $alias => $id)
    {
      $code .= $this->addServiceAlias($alias, $id);
    }

    return $code;
  }

  protected function addParameters()
  {
    if (!$this->container->getParameters())
    {
      return '';
    }

    return sfYaml::dump(array('parameters' => $this->prepareParameters($this->container->getParameters())), 2);
  }

  protected function dumpValue($value)
  {
    if (is_array($value))
    {
      $code = array();
      foreach ($value as $k => $v)
      {
        $code[$k] = $this->dumpValue($v);
      }

      return $code;
    }
    elseif (is_object($value) && $value instanceof RokCommon_Service_Reference)
    {
      return $this->getServiceCall((string) $value);
    }
    elseif (is_object($value) && $value instanceof RokCommon_Service_Parameter)
    {
      return $this->getParameterCall((string) $value);
    }
    elseif (is_object($value) || is_resource($value))
    {
      throw new RuntimeException('Unable to dump a service container if a parameter is an object or a resource.');
    }
    else
    {
      return $value;
    }
  }

  protected function getServiceCall($id)
  {
    return sprintf('@%s', $id);
  }

  protected function getParameterCall($id)
  {
    return sprintf('%%%s%%', $id);
  }

  protected function prepareParameters($parameters)
  {
    $filtered = array();
    foreach ($parameters as $key => $value)
    {
      if (is_array($value))
      {
        $value = $this->prepareParameters($value);
      }
      elseif ($value instanceof RokCommon_Service_Reference)
      {
        $value = '@'.$value;
      }

      $filtered[$key] = $value;
    }

    return $this->escape($filtered);
  }

  protected function escape($arguments)
  {
    $args = array();
    foreach ($arguments as $k => $v)
    {
      if (is_array($v))
      {
        $args[$k] = $this->escape($v);
      }
      elseif (is_string($v))
      {
        $args[$k] = str_replace('%', '%%', $v);
      }
      else
      {
        $args[$k] = $v;
      }
    }

    return $args;
  }
}
