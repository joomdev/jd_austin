<?php
/**
 * @version   $Id: Root.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Filter_Type_Root extends RokCommon_Filter_Type implements RokCommon_Filter_ITypeRoot
{
    /**
     * @var string
     */
    protected $type = 'root';

    /**
     * @var bool
     */
    protected $isselector = true;

    /**
     * @var bool
     */
    protected $isroot = true;

    /**
     * @param $xmlnode
     */
    public function addSelection($xmlnode)
    {

        $name  = trim((string)$xmlnode['name']);
        $type  = trim((string)$xmlnode['type']);
        $label = trim((string)$xmlnode['label']);

        $this->selection_types[$name]  = $type;
        $this->selection_labels[$name] = $label;

        // inistalize the selection
        //TODO check that its a valid class type
        $selection               = new $type($xmlnode);
        $this->selections[$name] = $selection;
    }

    public function getChunkSelectionRender()
    {
        return rc__('ROKCOMMON_FILTER_ROOT_RENDER', $this->getTypeDescription());
    }

    public function render($name, $type, $values)
    {
        return rc__('ROKCOMMON_FILTER_ROOT_RENDER', parent::render($name, $type, $values));
    }
}
