<?php
/**
 * @version   $Id: SelectField.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Seblod_SelectField extends RokCommon_Filter_Type_PickList
{
    /**
     * @var string
     */
    protected $type = 'selectfield';

    /**
     * @var array
     */
    protected $selection_options = array();

    /**
     * @var
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isselector = false;


    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $selection_options
     */
    public function setSelectionOptions(array $selection_options)
    {
        $this->selection_options = $selection_options;
    }

    /**
     * @return string
     */
    public function getChunkSelectionRender()
    {
        return rc__('ROKSPROCKET_FILTER_SEBLOD_SELECTFIELD_RENDER', $this->getTypeDescription($this->getChunkType()));
    }

    /**
     * @return mixed
     */
    public function getChunkType()
    {
        return $this->name;
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
        return rc__('ROKSPROCKET_FILTER_SEBLOD_SELECTFIELD_RENDER', $this->getSelectList($name, $value));
    }
}
