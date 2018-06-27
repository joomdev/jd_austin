<?php
/**
 * @version   $Id: PickList.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Filter_Type_PickList extends RokCommon_Filter_Type
{
    /**
     * @var string
     */
    protected $type = 'picklist';

    /**
     * @param null|SimpleXMLElement $xmlnode
     */
    public function __construct(SimpleXMLElement &$xmlnode = null)
    {
        parent::__construct($xmlnode);
    }

    /**
     * @return string
     */
    public function getChunkRender()
    {
        return $this->getSelectList();
    }

    /**
     * @return string
     */
    public function getChunkSelectionRender()
    {
        return rc__('ROKCOMMON_FILTER_PICKLIST_RENDER', $this->getTypeDescription($this->getChunkType()));
    }

    /**
     * @return string
     */
    public function getChunkType()
    {
        return trim((string)$this->xmlnode['name']);
    }

    /**
     * @param $name
     * @param $type
     * @param $values
     * @return string
     */
    public function render($name, $type, $values)
    {
        $value = (isset($values[$type]) ? $values[$type] : '');
        return rc__('ROKCOMMON_FILTER_PICKLIST_RENDER', $this->getSelectList($name, $value));
    }

    /**
     * @param string $name
     * @param null $value
     * @return string
     */
    protected function getSelectList($name = RokCommon_Filter_Type::JAVASCRIPT_NAME_VARIABLE, $value = null)
    {
        $select_options = $this->getOptions();

        $options = array();
        $attribs = array('class'=> $this->type . ' chzn-done','data-key'=>trim((string)$this->xmlnode['name']));
        foreach ($select_options as $option_value => $option_label) {
            $option    = new RokCommon_HTML_Select_Option($option_value, $option_label, $value == $option_value);
            $options[] = $option;
        }
        $service = $this->selectRenderer;
        /** @var $renderer RokCommon_HTML_ISelect */
        $renderer = $this->container->{$service};
        return $renderer->getList($name, $options, $attribs);
    }

    /**
     * @return array
     * @throws RokCommon_Exception
     */
    protected function getOptions()
    {
        $options = array();

        if (isset($this->xmlnode['populator'])) {
            $populator_class = trim((string)$this->xmlnode['populator']);

            if (!class_exists($populator_class, true)) {
                throw new RokCommon_Exception(rc__('Cannot find class %s', $populator_class));
            }

            $rtclass = new ReflectionClass($populator_class);
            if (!$rtclass->implementsInterface('RokCommon_Filter_IPicklistPopulator')) {
                throw new RokCommon_Exception(rc__('%s does not implement the %s interface', $populator_class, 'RokCommon_Filter_IPicklistPopulator'));
            }

            /** @var $populator  RokCommon_Filter_IPicklistPopulator */
            $populator         = new $populator_class();
            $populator_options = $populator->getPicklistOptions();

            if (is_array($populator_options)) {
                $options = array_diff_key($options, $populator_options) + $populator_options;
            }
        }

        $option_node = $this->xmlnode->xpath('option');
        foreach ($option_node as $option) {
            $options[trim((string)$option['value'])] = trim((string)$option);
        }

        return $options;
    }
}
