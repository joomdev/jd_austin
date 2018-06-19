<?php
/**
 * @version   $Id: XMLHelper.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


class RokCommon_Utils_XMLHelper
{
    /**
     * Reads a XML file.
     *
     * @param   string   $data    Full path and file name.
     * @param   boolean  $isFile  true to load a file or false to load a string.
     *
     * @return  mixed    JXMLElement on success or false on error.
     * @since   11.1
     *
     * @todo    This may go in a separate class - error reporting may be improved.
     * @see     JXMLElement
     */
    public static function getXML($data, $isFile = true)
    {

        // Disable libxml errors and allow to fetch error information as needed
        libxml_use_internal_errors(true);

        if ($isFile) {
            // Try to load the XML file
            $xml = simplexml_load_file($data, 'RokCommon_XMLElement');
        } else {
            // Try to load the XML string
            $xml = simplexml_load_string($data, 'RokCommon_XMLElement');
        }

        // todo log or output an error if bad XML
        //    		if (empty($xml)) {
        //    			// There was an error
        //    			JError::raiseWarning(100, JText::_('JLIB_UTIL_ERROR_XML_LOAD'));
        //
        //    			if ($isFile) {
        //    				JError::raiseWarning(100, $data);
        //    			}
        //
        //    			foreach (libxml_get_errors() as $error)
        //    			{
        //    				JError::raiseWarning(100, 'XML: '.$error->message);
        //    			}
        //    		}

        return $xml;
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
	public static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
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
	public static function mergeNode(SimpleXMLElement $source, SimpleXMLElement $new)
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
	public static function mergeNodes(SimpleXMLElement $source, SimpleXMLElement $new)
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
}
