<?php
/**
 * @version   $Id: Config.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Config_Exception extends Exception
{
}


/**
 *
 */
class RokCommon_Config_PathInfo
{
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $displayName;
}


/**
 *
 */
class RokCommon_Config
{

	/**
	 * @var
	 */
	protected static $parameters;

	/**
	 * @const string
	 */
	const CONTEXT_PREFIX = 'config';

	/**
	 *
	 */
	const ENTRY_SEPERATOR = '_';

	/**
	 *
	 */
	const MODE_BASEDIR = 'basedir';

	/**
	 *
	 */
	const MODE_CHILDDIR = 'childdir';

	/**
	 *
	 */
	const JOINTYPE_MERGE = 'merge';

	/**
	 *
	 */
	const JOINTYPE_OVERRIDE = 'override';


	/**
	 * @const int
	 */
	const ORDERING_DEFAULT = 100;

	/**
	 * @var RokCommon_Platform_Info
	 */
	protected static $platform_info;

	/**
	 * @var RokCommon_XMLElement
	 */
	protected $xml_node;

	/**
	 * @var string[]
	 */
	protected $paths = array();

	/**
	 * @var RokCommon_Config_PathInfo[]
	 */
	protected $path_configs_info = array();

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * The filename the meta entry looks for
	 * @var string
	 */
	protected $filename;

	/** @var  RokCommon_Composite_Context */
	protected $context;

	/** @var string */
	protected $mode = self::MODE_BASEDIR;

	/**
	 * @var
	 */
	protected $jointype = self::JOINTYPE_MERGE;

	/**
	 * @var RokCommon_Config[]
	 */
	protected $subentries = array();

	/**
	 * @var #Fimplode|?
	 */
	protected $identifier;

	/** @var RokCommon_Config */
	protected $parent;


	/** @var string */
	protected $root_file;
	/**
	 * @var
	 */
	protected $joined_xml;

	/**
	 * @var string
	 */
	protected $parent_identifier;


	/** @var RokCommon_Service_Container */
	protected $container;

	/**
	 * @throws \RokCommon_Config_Exception
	 */
	protected function process()
	{
		/** @var $ret array */
		$ret = array();

		if ($this->context) {
			if ($this->mode == self::MODE_BASEDIR) {
				$ret = $this->context->getAll($this->filename);
			} elseif ($this->mode == self::MODE_CHILDDIR) {
				$ret = $this->context->getAllSubFiles($this->filename);
			} else {
				throw new RokCommon_Config_Exception(rc__('Unknown mode of %s on config', $this->mode));
			}

			ksort($ret, SORT_NUMERIC);

			$parameters = new RokCommon_Registry();
			foreach ($ret as $priority => $files) {
				foreach ($files as $file) {
					$file_xml = RokCommon_Utils_XMLHelper::getXML($file, true);
					if (!isset($file_xml['name'])) {
						throw new RokCommon_Config_Exception(rc__('No "name" attribute defined on config %s', $file));
					}
					// add the infomation to the container
					$param_path   = explode('_', $this->identifier);
					$param_path[] = (string)$file_xml['name'];
					self::$parameters->set(implode('.', $param_path) . '.name', (string)$file_xml['name']);
					self::$parameters->set(implode('.', $param_path) . '.display_name', (isset($file_xml['displayName'])) ? (string)$file_xml['displayName'] : $file_xml['name']);
					self::$parameters->set(implode('.', $param_path) . '.config_file', $file);

					$this->container->addParameters(self::$parameters->toArray());
					self::mergeNodes($this->joined_xml, $file_xml);
				}
			}

			foreach ($this->subentries as $subentry) {
				if ($this->jointype == self::JOINTYPE_MERGE) {
					self::mergeNodes($this->joined_xml, $subentry->get());
				} else {
					throw new RokCommon_Config_Exception(rc__('Unknown Join Type of %s on config', $this->jointype));
				}
			}

		}
		//self::reorderNodes($this->joined_xml);
	}

	/**
	 * @return \RokCommon_XMLElement
	 */
	public function get()
	{
		return $this->joined_xml;
	}


	/**
	 * @param $identifier
	 *
	 * @return \RokCommon_Config_PathInfo[]
	 */
	public function getPathConfigsInfo($identifier)
	{
		if ($this->identifier == $identifier) {
			return $this->path_configs_info;
		} else {
			$ret = array();
			foreach ($this->subentries as $subentry) {
				$ret = $subentry->getPathConfigsInfo($identifier);
				if (!empty($ret)) {
					break;
				}
			}
			return $ret;
		}
	}

	/**
	 * Get an instance of config
	 * @static
	 *
	 * @param $metaconfig_path
	 *
	 * @return \RokCommon_Config
	 * @throws \RokCommon_Config_Exception
	 */
	public static function &getInstance($metaconfig_path)
	{
		if (!isset(self::$parameters)) {
			self::$parameters = new RokCommon_Registry();
		}
		if (!file_exists($metaconfig_path) || !is_file($metaconfig_path) || !is_readable($metaconfig_path)) {
			throw new RokCommon_Config_Exception(rc__('Unable to read file %s', $metaconfig_path));
		}
		$config = new self(self::CONTEXT_PREFIX, $metaconfig_path);
		return $config;
	}

	/**
	 * @param      $parent_identifier
	 * @param      $file
	 *
	 * @param null $xml
	 *
	 * @throws \RokCommon_Config_Exception
	 */
	protected function __construct($parent_identifier, $file = null, $xml = null)
	{
		$this->parent_identifier = $parent_identifier;

		$this->joined_xml = new RokCommon_XMLElement('<config/>');
		$this->container  = RokCommon_Service::getContainer();
		$this->root_file  = $file;
		if (empty($xml)) {
			$xml = RokCommon_Utils_XMLHelper::getXML($file);
		}

		$this->initialize($xml);
		$this->process();
	}

	/**
	 * @param \RokCommon_XMLElement $xml_node
	 *
	 * @throws \RokCommon_Config_Exception
	 */
	protected function initialize(RokCommon_XMLElement $xml_node)
	{
		$this->xml_node = $xml_node;

		// get the name of the entry
		if (!isset($this->xml_node['name'])) {
			throw new RokCommon_Config_Exception(rc__('Meta Config entry in %s does not have a name', $this->parent_identifier));
		}
		$this->name = (string)$this->xml_node['name'];


		// set the identifier name
		$id_parts         = explode(self::ENTRY_SEPERATOR, $this->parent_identifier);
		$id_parts[]       = $this->name;
		$this->identifier = implode(self::ENTRY_SEPERATOR, $id_parts);

		// get the filename of the entry
		if (!isset($this->xml_node['filename'])) {
			throw new RokCommon_Config_Exception(rc__('Meta Config entry %s does not have a filename', $this->identifier));
		}
		$this->filename = (string)$this->xml_node['filename'];


		// get the mode
		if (isset($this->xml_node['mode'])) {
			$this->mode = (string)$this->xml_node['mode'];
		}

		// get the jointype
		if (isset($this->xml_node['jointype'])) {
			$this->jointype = (string)$this->xml_node['jointype'];
		}

		// see if there is a library and add it to the lib path
		$library_paths = $xml_node->xpath('libraries/library');
		if ($library_paths) {
			foreach ($library_paths as $library_path) {
				$resolved_lib_path = RokCommon_Config::replaceTokens((string)$library_path, dirname($this->root_file));
				if (is_dir($resolved_lib_path)) {
					RokCommon_ClassLoader::addPath($resolved_lib_path);
				}
			}
		}

		// get the paths for the config
		$paths = $xml_node->xpath('paths/path');
		if (!$paths) {
			throw new RokCommon_Config_Exception(rc__('Meta Config entry %s must have at least one path.', $this->identifier));
		}

		foreach ($paths as $path_entry) {
			$priority = RokCommon_Composite::DEFAULT_PRIORITY;
			if (isset($path_entry['priority'])) {
				$priority = (string)$path_entry['priority'];
			}
			$path = RokCommon_Config::replaceTokens((string)$path_entry, dirname($this->root_file));
			if (is_dir($path)) {
				// see if there is a testservice entry
				if (isset($path_entry['testservice'])) {
					// see if the testservice extists
					$testservice_name = (string)$path_entry['testservice'];
					$container        = RokCommon_Service::getContainer();

					/** @var $testservice RokCommon_Config_PathTest */
					$testservice = $container->{$testservice_name};
					if (!$container->hasService($testservice_name)) {
						throw new RokCommon_Config_Exception(rc__('Path test service %s does not exist', $testservice_name));
					}
					// see if we can add the
					if ($testservice->isPathAvailable()) {
						$this->addPath($path, $priority);
					}
				} else {
					// add the path if there is no testclass
					$this->addPath($path, $priority);
				}
			} else {
				// TODO log unable to find path
			}
		}

		// add any subconfigs
		$subconfigs = $xml_node->xpath('subconfigs/subconfig');
		if ($subconfigs) {
			foreach ($subconfigs as $subconfig_entry) {
				$subconfig                               = new self($this->identifier, $this->root_file, $subconfig_entry);
				$this->subentries[$subconfig->getName()] = $subconfig;
			}
		}

		$this->context = RokCommon_Composite::get($this->identifier);
	}

	/**
	 * @param \RokCommon_Composite_Context $context
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}

	/**
	 * @return \RokCommon_Composite_Context
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param string $filename
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param  $identifier
	 */
	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;
	}

	/**
	 * @return
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}

	/**
	 * @param  $jointype
	 */
	public function setJointype($jointype)
	{
		$this->jointype = $jointype;
	}

	/**
	 * @return string
	 */
	public function getJointype()
	{
		return $this->jointype;
	}

	/**
	 * @param string $mode
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param \RokCommon_Config $parent
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
	}

	/**
	 * @return \RokCommon_Config
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param array $paths
	 */
	public function setPaths($paths)
	{
		$this->paths = $paths;
	}

	/**
	 * @return array
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * @param array $subentries
	 */
	public function setSubentries($subentries)
	{
		$this->subentries = $subentries;
	}

	/**
	 * @return array
	 */
	public function getSubentries()
	{
		return $this->subentries;
	}

	/**
	 * @param \RokCommon_XMLElement $xml_node
	 */
	public function setXmlNode($xml_node)
	{
		$this->xml_node = $xml_node;
	}

	/**
	 * @return \RokCommon_XMLElement
	 */
	public function getXmlNode()
	{
		return $this->xml_node;
	}

	/**
	 * @param string $path
	 * @param int    $priority
	 */
	public function addPath($path, $priority = RokCommon_Composite::DEFAULT_PRIORITY)
	{
		//self::addConfigPath($this->identifier, $path, $priority);
		RokCommon_Composite::addPackagePath($this->identifier, $path, $priority);
	}


	/**
	 * @static
	 *
	 * @param string $extension
	 * @param string $path
	 * @param int    $priority
	 */
	public static function addConfigPath($extension, $path, $priority = RokCommon_Composite::DEFAULT_PRIORITY)
	{

	}


	/**
	 * Adds a new child SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement  $source  The source element on which to append.
	 * @param   SimpleXMLElement  $new     The new element to append.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  Exception if an error occurs.
	 */
	protected static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		// Add the new child node.
		$node = $source->addChild($new->getName(), trim($new));

		// Add the attributes of the child node.
		foreach ($new->attributes() as $name => $value) {
			$node->addAttribute($name, $value);
		}

		// Add any children of the new node.
		foreach ($new->children() as $child) {
			self::addNode($node, $child);
		}
	}

	/**
	 * Adds a new child SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement  $source  The source element on which to append.
	 * @param   SimpleXMLElement  $new     The new element to append.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected static function mergeNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value) {
			if (isset($source[$name])) {
				$source[$name] = (string)$value;
			} else {
				$source->addAttribute($name, $value);
			}
		}

		// What to do with child elements?
	}

	/**
	 * Merges new elements into a source <fields> element.
	 *
	 * @param   SimpleXMLElement  $source  The source element.
	 * @param   SimpleXMLElement  $new     The new element to merge.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected static function mergeNodes(SimpleXMLElement &$source, SimpleXMLElement $new)
	{
		// The assumption is that the inputs are at the same relative level.
		// So we just have to scan the children and deal with them.

		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value) {
			if (isset($source[$name])) {
				$source[$name] = (string)$value;
			} else {
				$source->addAttribute($name, $value);
			}
		}


		/** @var $child SimpleXMLElement */
		foreach ($new->children() as $child) {
			$type = $child->getName();
			$name = $child['name'];

			// Does this node exist?
			$fields = $source->xpath($type . '[@name="' . $name . '"]');

			if (empty($fields)) {
				// This node does not exist, so add it.
				self::addNode($source, $child);
			} else {
				// This node does exist.
				switch ($type) {
					case 'field':
						self::mergeNode($fields[0], $child);
						break;

					default:
						self::mergeNodes($fields[0], $child);
						break;
				}
			}
		}
	}

	/**
	 * @static
	 *
	 * @param \SimpleXMLElement $node
	 *
	 * @return array|\SimpleXMLElement
	 */
	protected static function reorderNodes(SimpleXMLElement &$node)
	{

		$reordered_node = new SimpleXMLElement('<' . $node->getName() . '/>');
		foreach ($node->attributes() as $name => $value) {
			if (isset($node[$name])) {
				$reordered_node[$name] = (string)$value;
			} else {
				$reordered_node->addAttribute($name, $value);
			}
		}

		/** @var $child SimpleXMLElement */
		foreach ($node->children() as $child) {
			$type = $child->getName();
			switch ($type) {
				case 'fieldset':
					$reordered_child = self::reorderNode($child);
					break;
				default:
					$reordered_child = self::reorderNodes($child);
					break;
			}
			$reordered_node->addChild($type, $reordered_child);


		}

		return $reordered_node;
	}

	/**
	 * @static
	 *
	 * @param SimpleXMLElement $node
	 *
	 * @return array|SimpleXMLElement
	 */
	protected static function reorderNode(SimpleXMLElement &$node)
	{
		$reordered_node = new SimpleXMLElement('<' . $node->getName() . '/>');
		foreach ($node->attributes() as $name => $value) {
			if (isset($node[$name])) {
				$reordered_node[$name] = (string)$value;
			} else {
				$reordered_node->addAttribute($name, $value);
			}
		}

		/** @var $fieldArray SimpleXMLElement[] */
		$fieldArray = array();
		foreach ($node->field as $d) {
			$fieldArray[] = $d;
		}
		usort($fieldArray, array('RokCommon_Config', 'sortXmlNodesByConfigOrdering'));
		foreach ($fieldArray as $ordered_sub_node) {
			$reordered_node->addChild('field', $ordered_sub_node);
		}
		return $reordered_node;
	}

	/**
	 * @static
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	protected static function sortXmlNodesByConfigOrdering($a, $b)
	{
		$va = (isset($a['configordering'])) ? (int)$a['configordering'] : self::ORDERING_DEFAULT;
		$vb = (isset($b['configordering'])) ? (int)$b['configordering'] : self::ORDERING_DEFAULT;
		if ($va === $vb) {
			return 0;
		}
		return ($va < $vb) ? -1 : 1;
	}


	/**
	 * @param $string
	 *
	 * @param $file_directory
	 *
	 * @return mixed
	 */
	public static function replaceTokens($string, $file_directory)
	{
		$contianer = RokCommon_Service::getContainer();
		/** @var $platform_info RokCommon_IPlatformInfo */
		$platform_info = $contianer->platforminfo;
		$string        = RokCommon_Template::replace('CURRENT_PATH', $file_directory, $string);
		$string        = RokCommon_Template::replace('ROOT_PATH', $platform_info->getRootPath(), $string);
		$string        = RokCommon_Template::replace('TEMPLATE_PATH', $platform_info->getDefaultTemplatePath(), $string);
		return $string;
	}

	/**
	 * @param string $parent_identifier
	 */
	public function setParentIdentifier($parent_identifier)
	{
		$this->parent_identifier = $parent_identifier;
	}

	/**
	 * @return string
	 */
	public function getParentIdentifier()
	{
		return $this->parent_identifier;
	}
}
