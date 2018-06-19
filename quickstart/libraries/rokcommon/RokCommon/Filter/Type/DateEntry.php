<?php
/**
 * @version   $Id: DateEntry.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Filter_Type_DateEntry extends RokCommon_Filter_Type
{
    /**
     * @var string
     */
    protected $type = 'dateentry';

    public function getChunkRender()
    {
        return $this->getInput();
    }

    public function render($name, $type, $values)
    {
        $value = (isset($values[$type]) ? $values[$type] : '');
        return rc__('ROKCOMMON_FILTER_DATEENTRY_RENDER', $this->getInput($name, $value));
    }

    public function getChunkSelectionRender()
    {
        return rc__('ROKCOMMON_FILTER_DATEENTRY_RENDER', $this->getTypeDescription());
    }

    public function getInput($name = RokCommon_Filter_Type::JAVASCRIPT_NAME_VARIABLE, $value = '')
    {
        return '<input type="text" name="' . $name . '" class="' . $this->type . '" data-key="' . $this->type .'" value="' . $value . '"/>';
    }
}
