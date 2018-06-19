<?php
/**
 * @version   $Id: Filter.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

class RokCommon_Filter
{
    const ROOT_TYPE_INTERFACE = 'RokCommon_Filter_ITypeRoot';

    const DEFAULT_ROOT_TYPE = 'RokCommon_Filter_Type_Root';
    /**
     * @var SimpleXMLElement
     */
    protected $xmlnode;


    protected $types = array();

    protected $selectRenderer = 'html.renderer.select';

    /**
     * @var RokCommon_Filter_Type_Root
     */
    protected $root;

    public function __construct(SimpleXMLElement &$xmlnode)
    {
        $this->xmlnode = $xmlnode;

        $roottype = self::DEFAULT_ROOT_TYPE;
        if (isset($this->xmlnode['roottype'])) {
            $roottype = (string)$this->xmlnode['roottype'];

            if (!class_exists($roottype, true)) {
                throw new RokCommon_Exception(rc__('Cannot find class %s', $roottype));
            }

            $rtclass = new ReflectionClass($roottype);
            if (!$rtclass->implementsInterface(self::ROOT_TYPE_INTERFACE)) {
                throw new RokCommon_Exception(rc__('%s does not implement the %s interface', $roottype, self::ROOT_TYPE_INTERFACE));
            }
        }

        $this->root = new $roottype;

        $filters = $this->xmlnode->xpath('filter');
        foreach ($filters as $filternode) {
            $this->root->addSelection($filternode);
            $filter_type_class = (string)$filternode['type'];
            $this->types       = new $filter_type_class($filternode);
        }
    }

    public function getJson()
    {
        $chunks = $this->root->getChunks();
        return RokCommon_JSON::encode($chunks);
    }

    public function renderLine(array $data, $parentname)
    {
        return $this->root->getFieldRender($data, $parentname);
    }


    public function setSelectRendererService($renderer)
    {
        $this->selectRenderer = $renderer;
    }

    public function getRootType()
    {
        return $this->root->getType();
    }
}
