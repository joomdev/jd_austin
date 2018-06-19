<?php
/**
 * @version   $Id: Section.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Options_Section
{
    /**
     * @const string
     */
    const CONTEXT_PREFIX = 'options_';

    /**
     *
     */
    const JOIN_MODE_MERGE = 'merge';

    /**
     *
     */
    const SEARCH_MODE_CHILDIRS = 'childdirs';

    /**
     *
     */
    const SEARCH_MODE_BASEDIRS = 'basedir';

    /**
     * @var RokCommon_Service_Container
     */
    protected $container;

    /**
     * @var RokCommon_Logger
     */
    protected $logger;

    /**
     * @var RokCommon_XMLElement
     */
    protected $xml;

    /**
     * @var string
     */
    protected $joinMode;


    /**
     * @var string
     */
    protected $searchMode;


    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $filename;


    /**
     * @param string $identifier the identifier for the options
     * @param string $filename
     * @param string $joinmode
     * @param string $searchmode
     */
    public function __construct($identifier, $filename, $joinmode = self::JOIN_MODE_MERGE, $searchmode = self::SEARCH_MODE_BASEDIRS)
    {
        $this->identifier = $identifier;
        $this->container  = RokCommon_Service::getContainer();
        $this->logger     = $this->container->logger;
        $this->joinMode   = $joinmode;
        $this->searchMode = $searchmode;
        $this->xml        = new RokCommon_XMLElement('<config/>');
        $this->filename   = $filename;
    }

    /**
     * @param string $path
     * @param int    $priority
     */
    public function addPath($path, $priority = RokCommon_Composite::DEFAULT_PRIORITY)
    {
        RokCommon_Composite::addPackagePath(self::CONTEXT_PREFIX . $this->identifier, $path, $priority);
    }


    /**
     * @return RokCommon_XMLElement
     * @throws RokCommon_Config_Exception
     */
    public function getXml()
    {
        $context = RokCommon_Composite::get(self::CONTEXT_PREFIX . $this->identifier);
        if ($context) {
            if ($this->searchMode == self::SEARCH_MODE_BASEDIRS) {
                $ret = $context->getAll($this->filename);
            } elseif ($this->searchMode == self::SEARCH_MODE_CHILDIRS) {
                $ret = $context->getAllSubFiles($this->filename);
            } else {
                throw new RokCommon_Config_Exception(rc__('Unknown mode of %s on config', $this->searchMode));
            }

            ksort($ret, SORT_NUMERIC);
            foreach ($ret as $priority => $files) {
                foreach ($files as $file) {
                    $file_xml = RokCommon_Utils_XMLHelper::getXML($file, true);
                    RokCommon_Options::mergeNodes($this->xml, $file_xml);
                }
            }
        }
        return $this->xml;
    }

    public function reset()
    {
        $this->xml = new RokCommon_XMLElement('<config/>');
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
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
     * @param string $joinMode
     */
    public function setJoinMode($joinMode)
    {
        $this->joinMode = $joinMode;
    }

    /**
     * @return string
     */
    public function getJoinMode()
    {
        return $this->joinMode;
    }

    /**
     * @param string $searchMode
     */
    public function setSearchMode($searchMode)
    {
        $this->searchMode = $searchMode;
    }

    /**
     * @return string
     */
    public function getSearchMode()
    {
        return $this->searchMode;
    }
}

