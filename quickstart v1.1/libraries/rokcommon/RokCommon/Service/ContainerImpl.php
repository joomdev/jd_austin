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
 * RokCommon_Service_ContainerImpl is a dependency injection container.
 *
 * It gives access to object instances (services), and parameters.
 *
 * Services and parameters are simple key/pair stores.
 *
 * Parameters keys are case insensitive.
 *
 * A service id can contain lowercased letters, digits, underscores, and dots.
 * Underscores are used to separate words, and dots to group services
 * under namespaces:
 *
 * <ul>
 *   <li>request</li>
 *   <li>mysql_session_storage</li>
 *   <li>symfony.mysql_session_storage</li>
 * </ul>
 *
 * A service can also be defined by creating a method named
 * getXXXService(), where XXX is the camelized version of the id:
 *
 * <ul>
 *   <li>request -> getRequestService()</li>
 *   <li>mysql_session_storage -> getMysqlSessionStorageService()</li>
 *   <li>symfony.mysql_session_storage -> getSymfony_MysqlSessionStorageService()</li>
 * </ul>
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: ContainerImpl.php 30067 2016-03-08 13:44:25Z matias $
 */
class RokCommon_Service_ContainerImpl implements RokCommon_Service_Container, ArrayAccess, Iterator
{

    /**
     * @var array
     */
    protected $serviceIds = array();

    /**
     * @var RokCommon_Registry
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $services = array();

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * Constructor.
     *
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = new RokCommon_Registry();
        $this->setParameters($parameters);
        $this->setService('service_container', $this);
    }

    /**
     * Sets the service container parameters.
     *
     * @param array $parameters An array of parameters
     */
    public function setParameters(array $parameters)
    {

        foreach ($parameters as $key => $value) {
            if (is_array($value) && RokCommon_Utils_ArrayHelper::isAssociative($value)) {
                foreach ($value as $subkey => $subvalue) {
                    $subname = $key . RokCommon_Registry::SEPARATOR . $subkey;
                    $this->setParameter($subname, $subvalue);
                }
            } else {
                $this->parameters->set(strtolower($key), $value);
            }
        }
    }

    /**
     * Adds parameters to the service container parameters.
     *
     * @param array $parameters An array of parameters
     */
    public function addParameters(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
            //$this->setParameters(array_merge($this->parameters, $parameters));
        }
    }

    /**
     * Gets the service container parameters.
     *
     * @return array An array of parameters
     */
    public function getParameters()
    {
        return $this->parameters->toArray();
    }

    /**
     * Gets a service container parameter.
     *
     * @param  string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throw  InvalidArgumentException if the parameter is not defined
     */
    public function getParameter($name, $default = null)
    {
        if ($default === null && !$this->hasParameter(strtolower($name))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }
        $value = $this->parameters->get(strtolower($name),$default);
        $value = $this->resolveValue($value);
        return $value;
    }

    /**
     * Sets a service container parameter.
     *
     * @param string $name       The parameter name
     * @param        $value
     *
     * @internal param mixed $parameters The parameter value
     */
    public function setParameter($name, $value)
    {
	    $cleaned_name = strtolower($name);
        if (is_array($value) && RokCommon_Utils_ArrayHelper::isAssociative($value)) {
            foreach ($value as $key => $subvalue) {
                $subname = $cleaned_name . RokCommon_Registry::SEPARATOR . strtolower($key);
                $this->setParameter($subname, $subvalue);
            }
        }
        elseif (is_array($value) && $this->parameters->exists($cleaned_name)) {
            $current = $this->parameters->get($cleaned_name);
            if (is_array($current)) {
                $merged = array_merge($current, $value);
                $this->parameters->set($cleaned_name, $merged);
            } else {
                $this->parameters->set($cleaned_name, $value);
            }
        } else {
            $this->parameters->set($cleaned_name, $value);
        }
    }

    /**
     * Replaces parameter placeholders (%name%) by their values.
     *
     * @param  mixed $value A value
     *
     * @return mixed The same value with all placeholders replaced by their values
     *
     * @throw RuntimeException if a placeholder references a parameter that does not exist
     */
    public function resolveValue($value)
    {
        if (is_array($value)) {
            $args = array();
            foreach ($value as $k => $v) {
                $args[$this->resolveValue($k)] = $this->resolveValue($v);
            }

            $value = $args;
        } else if (is_string($value)) {
            if (preg_match('/^%([^%]+)%$/', $value, $match)) {
                // we do this to deal with non string values (boolean, integer, ...)
                // the preg_replace_callback converts them to strings
                if (!$this->hasParameter($name = strtolower($match[1]))) {
                    throw new RuntimeException(sprintf('The parameter "%s" must be defined.', $name));
                }

                $value = $this->getParameter($name);
            } elseif (preg_match('/^#([^#]+)#$/', $value, $match)) {
	           // we do this to deal with non string values (boolean, integer, ...)
                // the preg_replace_callback converts them to strings
                if (!defined($match[1])) {
                    throw new RuntimeException(sprintf('The define "%s" must be defined to be used as a parameter.', $match[1]));
                }
	            $value = constant($match[1]);
            }
            else {
                $value = str_replace('%%', '%', preg_replace_callback('/(?<!%)(%)([^%]+)\1/', array(
                                                                                                   $this,
                                                                                                   'replaceParameter'
                                                                                              ), $value));
            }
        }

        return $value;
    }

    /**
     * @param $match
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected function replaceParameter($match)
    {
        if (!$this->hasParameter($name = strtolower($match[2]))) {
            throw new RuntimeException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->getParameter($name);
    }

    /**
     * Returns true if a parameter name is defined.
     *
     * @param  string  $name       The parameter name
     *
     * @return Boolean true if the parameter name is defined, false otherwise
     */
    public function hasParameter($name)
    {
        return $this->parameters->exists($name);
    }

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     */
    public function setService($id, $service)
    {
        $this->services[$id] = $service;
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param  string  $id      The service identifier
     *
     * @return Boolean true if the service is defined, false otherwise
     */
    public function hasService($id)
    {
        return isset($this->services[$id]) || method_exists($this, 'get' . self::camelize($id) . 'Service');
    }

    /**
     * Gets a service.
     *
     * If a service is both defined through a setService() method and
     * with a set*Service() method, the former has always precedence.
     *
     * @param  string $id The service identifier
     *
     * @return object The associated service
     *
     * @throw InvalidArgumentException if the service is not defined
     */
    public function getService($id)
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (method_exists($this, $method = 'get' . self::camelize($id) . 'Service')) {
            return $this->{$method}();
        }

        throw new InvalidArgumentException(sprintf('The service "%s" does not exist.', $id));
    }

    /**
     * Gets all service ids.
     *
     * @return array An array of all defined service ids
     */
    public function getServiceIds()
    {
        $ids = array();
        $r   = new ReflectionClass($this);
        foreach ($r->getMethods() as $method) {
            if (preg_match('/^get(.+)Service$/', $name = $method->getName(), $match)) {
                $ids[] = self::underscore($match[1]);
            }
        }

        return array_merge($ids, array_keys($this->services));
    }

    /**
     * Returns true if the parameter name is defined (implements the ArrayAccess interface).
     *
     * @param  string  The parameter name
     *
     * @return Boolean true if the parameter name is defined, false otherwise
     */
    public function offsetExists($name)
    {
        return $this->hasParameter($name);
    }

    /**
     * Gets a service container parameter (implements the ArrayAccess interface).
     *
     * @param  string The parameter name
     *
     * @return mixed  The parameter value
     */
    public function offsetGet($name)
    {
        return $this->getParameter($name);
    }

    /**
     * Sets a parameter (implements the ArrayAccess interface).
     *
     * @param string The parameter name
     * @param mixed  The parameter value
     */
    public function offsetSet($name, $value)
    {
        $this->setParameter($name, $value);
    }

    /**
     * Removes a parameter (implements the ArrayAccess interface).
     *
     * @param string The parameter name
     */
    public function offsetUnset($name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * Returns true if the container has a service with the given identifier.
     *
     * @param  string  The service identifier
     *
     * @return Boolean true if the container has a service with the given identifier, false otherwise
     */
    public function __isset($id)
    {
        return $this->hasService($id);
    }

    /**
     * Gets the service associated with the given identifier.
     *
     * @param  string The service identifier
     *
     * @return mixed  The service instance associated with the given identifier
     */
    public function __get($id)
    {
        return $this->getService($id);
    }

    /**
     * Sets a service.
     *
     * @param string The service identifier
     * @param mixed  A service instance
     */
    public function __set($id, $service)
    {
        $this->setService($id, $service);
    }

    /**
     * Removes a service by identifier.
     *
     * @param string The service identifier
     */
    public function __unset($id)
    {
        throw new LogicException('You can\'t unset a service.');
    }

    /**
     * Resets the service identifiers array to the beginning (implements the Iterator interface).
     */
    public function rewind()
    {
        $this->serviceIds = $this->getServiceIds();

        $this->count = count($this->serviceIds);
    }

    /**
     * Gets the key associated with the current service (implements the Iterator interface).
     *
     * @return string The service identifier
     */
    public function key()
    {
        return current($this->serviceIds);
    }

    /**
     * Returns the current service (implements the Iterator interface).
     *
     * @return mixed The service
     */
    public function current()
    {
        return $this->getService(current($this->serviceIds));
    }

    /**
     * Moves to the next service (implements the Iterator interface).
     */
    public function next()
    {
        next($this->serviceIds);

        --$this->count;
    }

    /**
     * Returns true if the current service is valid (implements the Iterator interface).
     *
     * @return boolean The validity of the current service; true if it is valid
     */
    public function valid()
    {
        return $this->count > 0;
    }

    /**
     * @static
     *
     * @param $id
     *
     * @return mixed
     */
    static public function camelize($id)
    {
	    $id = preg_replace_callback('/(^|_|-)+(.)/', create_function ('$matches', 'return strtoupper($matches[2]);'), $id);
		$id =  preg_replace_callback('/\.(.)/', create_function ('$matches', 'return "_".strtoupper($matches[1]);'), $id);
	    return $id;

		//        return preg_replace(array(
		//                                 '/(^|_|-)+(.)/e', '/\.(.)/e'
		//                            ), array(
		//                                    "strtoupper('\\2')", "'_'.strtoupper('\\1')"
		//                               ), $id);


    }

    /**
     * @static
     *
     * @param $id
     *
     * @return string
     */
    static public function underscore($id)
    {
        return strtolower(preg_replace(array(
                                            '/_/', '/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'
                                       ), array(
                                               '.', '\\1_\\2', '\\1_\\2'
                                          ), $id));
    }
}
