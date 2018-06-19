<?php
/**
 * @version        $Id: Registry.php 30067 2016-03-08 13:44:25Z matias $
 * @package        Joomla.Framework
 * @subpackage    Registry
 * @copyright    Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Derived from Joomla class JRegistry
 *
 */

// No direct access
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Registry
{
    const SEPARATOR = '.';

    /**
     * Registry Object
     *
     * @var object
     */
    protected $data;

	/**
	 * Constructor
	 *
	 * @param null $data
	 *
	 * @return \RokCommon_Registry
	 */
    public function __construct($data = null)
    {
        // Instantiate the internal data object.
        $this->data = new stdClass();

        // Optionally load supplied data.
        if (is_array($data) || is_object($data))
        {
            $this->bindData($this->data, $data);
        }
        elseif (!empty($data) && is_string($data))
        {
            $this->loadString($data);
        }
    }

    /**
     * Magic function to clone the registry object.
     */
    public function __clone()
    {
        $this->data = unserialize(serialize($this->data));
    }

    /**
     * Magic function to render this object as a string using default args of toString method.
     * @return string
     */
    public function __toString()
    {
		try {
            return $this->toString();
		}
		catch(Exception $e)
		{
			$container = RokCommon_Service::getContainer();
            $logger = $container->logger;
            $logger->error($e->getMessage());
            return '';
		}
    }

    /**
     * Sets a default value if not alreay assigned.
     *
     * @param    string    The name of the parameter.
     * @param    string    An optional value for the parameter.
     * @param    string    An optional group for the parameter.
     * @return    string    The value set, or the default if the value was not previously set (or null).
     * @since    1.6
     */
    public function def($key, $default = '')
    {
        $value = $this->get($key, (string)$default);
        $this->set($key, $value);
        return $value;
    }

    /**
     * Check if a registry path exists.
     *
     * @param    string    Registry path (e.g. joomla.content.showauthor)
     * @return    boolean
     * @since    1.6
     */
    public function exists($path)
    {
        // Return default value if path is empty
        if (empty($path))
        {
            return false;
        }

        // Explode the registry path into an array
        $nodes = explode('.', $path);

        // Initialize the current node to be the registry root.
        $node = $this->data;
        $found = false;

        // Traverse the registry to find the correct node for the result.
        foreach ($nodes as $n)
        {
            if (is_array($node) && isset($node[$n]))
            {
                $node = $node[$n];
                $found = true;
                continue;
            }

            if (!isset($node->{$n}))
            {
                return false;
            }

            $node = $node->{$n};
            $found = true;
        }

        return $found;
    }

    /**
     * Get a registry value.
     *
     * @param    string    Registry path (e.g. joomla.content.showauthor)
     * @param    mixed    Optional default value, returned if the internal value is null.
     * @return    mixed    Value of entry or null
     * @since    1.6
     */
    public function get($path, $default = null)
    {
        // Initialise variables.
        $result = $default;

        if (!strpos($path, '.'))
        {
            return (isset($this->data->{$path}) && $this->data->{$path} !== null && $this->data->{$path} !== '')
                    ? $this->data->{$path} : $default;
        }
        // Explode the registry path into an array
        $nodes = explode('.', $path);

        // Initialize the current node to be the registry root.
        $node = $this->data;
        $found = false;

        // Traverse the registry to find the correct node for the result.
        foreach ($nodes as $n)
        {
            if (is_array($node) && isset($node[$n]))
            {
                $node = $node[$n];
                $found = true;

                continue;
            }

            if (!isset($node->{$n}))
            {
                return $default;
            }

            $node = $node->{$n};
            $found = true;
        }

        if (!$found || $node === null || $node === '')
        {
            return $default;
        }

        return $node;
    }

    /**
     * Returns a reference to a global JRegistry object, only creating it
     * if it doesn't already exist.
     *
     * This method must be invoked as:
     *        <pre>$registry = JRegistry::getInstance($id);</pre>
     *
     * @param    string    An ID for the registry instance
     * @return    object    The JRegistry object.
     * @since    1.5
     */
    public static function getInstance($id)
    {
        static $instances;

        if (!isset ($instances))
        {
            $instances = array();
        }

        if (empty ($instances[$id]))
        {
            $instances[$id] = new RokCommon_Registry();
        }

        return $instances[$id];
    }

	/**
	 * Load a associative array of values into the default namespace
	 *
	 * @param \Associative $array
	 *
	 * @return    boolean    True on success
	 * @since    1.5
	 */
    public function loadArray($array)
    {
        $this->bindData($this->data, $array);

        return true;
    }

	/**
	 * Load the public variables of the object into the default namespace.
	 *
	 * @param $object
	 *
	 * @internal param \The $object object holding the public vars to load
	 * @internal param \Namespace $string to load the INI string into [optional]
	 * @return    boolean    True on success
	 * @since    1.5
	 */
    public function loadObject($object)
    {
        $this->bindData($this->data, $object);

        return true;
    }

    /**
     * Load the contents of a file into the registry
     *
     * @param    string    Path to file to load
     * @param    string    Format of the file [optional: defaults to JSON]
     * @param    mixed    Options used by the formatter
     * @return    boolean    True on success
     * @since    1.5
     */
    public function loadFile($file, $format = 'JSON', $options = array())
    {

        // Initialise variables.
        $data = null;
        $amount = 0;
        $chunksize = 8192;
        $offset = 0;
        if ($amount && $chunksize > $amount)
        {
            $chunksize = $amount;
        }

        if (false === $fh = fopen($file, 'rb', false))
        {
            return false;
        }

        clearstatcache();

        if ($offset)
        {
            fseek($fh, $offset);
        }

        if ($fsize = @ filesize($file))
        {
            if ($amount && $fsize > $amount)
            {
                $data = fread($fh, $amount);
            } else
            {
                $data = fread($fh, $fsize);
            }
        } else
        {
            $data = '';
            $x = 0;
            // While its:
            // 1: Not the end of the file AND
            // 2a: No Max Amount set OR
            // 2b: The length of the data is less than the max amount we want
            while (!feof($fh) && (!$amount || strlen($data) < $amount))
            {
                $data .= fread($fh, $chunksize);
            }
        }
        fclose($fh);
        return $this->loadString($data, $format, $options);
    }

    /**
     * Load a string into the registry
     *
     * @param    string    string to load into the registry
     * @param    string    format of the string
     * @param    mixed    Options used by the formatter
     * @return    boolean    True on success
     * @since    1.5
     */
    public function loadString($data, $format = 'JSON', $options = array())
    {
        // Load a string into the given namespace [or default namespace if not given]
        $handler = RokCommon_Registry_Format::getInstance($format);

        $obj = $handler->stringToObject($data, $options);
        $this->loadObject($obj);

        return true;
    }

    /**
     * Merge a JRegistry object into this one
     *
     * @param    object    Source JRegistry object ot merge
     * @return    boolean    True on success
     * @since    1.5
     */
    public function merge(&$source)
    {
        if ($source instanceof RokCommon_Registry)
        {
            // Load the variables into the registry's default namespace.
            foreach ($source->toArray() as $k => $v)
            {
                if (($v !== null) && ($v !== ''))
                {
                    $this->data->{$k} = $v;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Set a registry value.
     *
     * @param    string    Registry Path (e.g. joomla.content.showauthor)
     * @param     mixed    Value of entry
     * @return     mixed    The value of the that has been set.
     * @since    1.6
     */
    public function set($path, $value)
    {
        $result = null;

        // Explode the registry path into an array
        if ($nodes = explode('.', $path))
        {
            // Initialize the current node to be the registry root.
            $node = $this->data;

            // Traverse the registry to find the correct node for the result.
            for ($i = 0, $n = count($nodes) - 1; $i < $n; $i++)
            {
                if (is_object($node))
                {
                    if (!isset($node->{$nodes[$i]}) && ($i != $n))
                    {
                        $node->{$nodes[$i]} = new \stdClass;
                    }

                    // Pass the child as pointer in case it is an object
                    $node = &$node->{$nodes[$i]};

                    continue;
                }

                if (is_array($node))
                {
                    if (!isset($node[$nodes[$i]]) && ($i != $n))
                    {
                        $node[$nodes[$i]] = new \stdClass;
                    }

                    // Pass the child as pointer in case it is an array
                    $node = &$node[$nodes[$i]];
                }
            }

            // Get the old value if exists so we can return it
            switch (true)
            {
                case (is_object($node)):
                    $result = $node->{$nodes[$i]} = $value;
                    break;

                case (is_array($node)):
                    $result = $node[$nodes[$i]] = $value;
                    break;

                default:
                    $result = null;
                    break;
            }
        }

        return $result;
    }

    /**
     * Transforms a namespace to an array
     *
     * @param    string    Namespace to return [optional: null returns the default namespace]
     * @return    array    An associative array holding the namespace data
     * @since    1.5
     */
    public function toArray()
    {
        return (array)$this->asArray($this->data);
    }

    /**
     * Transforms a namespace to an object
     *
     * @param    string    Namespace to return [optional: null returns the default namespace]
     * @return    object    An an object holding the namespace data
     * @since    1.5
     */
    public function toObject()
    {
        return $this->data;
    }

	/**
	 * Get a namespace in a given string format
	 *
	 * @param    string    Format to return the string in
	 * @param    mixed     Parameters used by the formatter, see formatters for more info
	 *
	 * @return    string    Namespace in string format
	 * @since    1.5
	 */
	public function toString($format = 'JSON', $options = array())
	{
		// Return a namespace in a given format
		$handler = RokCommon_Registry_Format::getInstance($format);

		return $handler->objectToString($this->data, $options);
	}

	/**
     * Method to recursively bind data to a parent object.
     *
     * @param    object    $parent    The parent object on which to attach the data values.
     * @param    mixed    $data    An array or object of data to bind to the parent object.
     *
     * @return    void
     * @since    1.6
     */
    protected function bindData(& $parent, $data)
    {
        // Ensure the input data is an array.
        if (is_object($data))
        {
            $data = get_object_vars($data);
        } else
        {
            $data = (array)$data;
        }

        foreach ($data as $k => $v)
        {
            if ((is_array($v) && RokCommon_Utils_ArrayHelper::isAssociative($v)) || is_object($v))
            {
                $parent->{$k} = new stdClass();
                $this->bindData($parent->{$k}, $v);
            } else
            {
                $parent->{$k} = $v;
            }
        }
    }

    /**
     * Method to recursively convert an object of data to an array.
     *
     * @param    object    $data    An object of data to return as an array.
     *
     * @return    array    Array representation of the input object.
     * @since    1.6
     */
    protected function asArray($data)
    {
        $array = array();

        foreach (get_object_vars((object)$data) as $k => $v)
        {
            if (is_object($v))
            {
                $array[$k] = $this->asArray($v);
            } else
            {
                $array[$k] = $v;
            }
        }

        return $array;
    }
}
