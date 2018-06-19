<?php
/**
 * @version   $Id: Options.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_Options
{
	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;

	/**
	 * @var RokCommon_Logger
	 */
	protected $logger;

	/**
	 * @var RokCommon_Options_Section[]
	 */
	protected $sections = array();


	/**
	 * @var array
	 */
	protected $files = array();

	/**
	 * @var RokCommon_XMLElement;
	 */
	protected $xml;


	/**
	 */
	public function __construct()
	{
		$this->container = RokCommon_Service::getContainer();
		$this->logger    = $this->container->logger;
		$this->xml       = new RokCommon_XMLElement('<config/>');
	}


	/**
	 * @param RokCommon_Options_Section $section
	 */
	public function addSection(RokCommon_Options_Section &$section)
	{
		$this->sections[$section->getIdentifier()] = $section;
	}


	/**
	 * @return RokCommon_XMLElement
	 */
	public function getJoinedXml()
	{
		foreach ($this->files as $priority => $filegroup) {
			foreach ($filegroup as $file) {
				$this->logger->debug(rc__('Adding file %s.', $file));
				$file_xml = RokCommon_Utils_XMLHelper::getXML($file);
				self::mergeNodes($this->xml, $file_xml);
			}
		}
		foreach ($this->sections as $identifier => &$section) {
			$this->logger->debug(rc__('Adding options section %s.', $identifier));
			$section_xml = $section->getXML();
			self::mergeNodes($this->xml, $section_xml);
		}
		$this->xml = self::sortFields($this->xml);
		return $this->xml;
	}

	/**
	 *
	 */
	public function reset()
	{
		$this->xml = new RokCommon_XMLElement('<config/>');
		foreach ($this->sections as $identifier => &$section) {
			$this->logger->debug(rc__('Resetting options section %s.', $identifier));
			$section->reset();
		}
	}

	/**
	 * @param     $filepath
	 * @param int $priority
	 */
	public function addFile($filepath, $priority = 10)
	{
		$this->files[$priority][] = $filepath;
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
	public static function mergeNodes(SimpleXMLElement &$source, SimpleXMLElement $new)
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
	 * @param SimpleXMLElement $source
	 *
	 * @return string
	 */
	public static function sortFields(SimpleXMLElement &$source)
	{

		$fieldss = $source->children();
		foreach ($fieldss as $fields) {
			$fieldsets = $fields->children();
			foreach ($fieldsets as $fieldset) {
				$fieldset->sortChildren('@optionorder');
			}
		}
		return $source;
	}
}
