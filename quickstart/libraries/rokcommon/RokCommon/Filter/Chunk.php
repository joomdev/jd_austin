<?php
/**
 * @version   $Id: Chunk.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;

/**
 * @RokCommon_JSON_Annotation_JSONDefaultKey('id')
 */
class RokCommon_Filter_Chunk
{
    /**
     * @var string
     * @RokCommon_JSON_Annotation_JSONEncodeIgnore
     */
    public $id;

    /**
     * @var bool
     */
    public $selector = false;


    /**
     * @var bool
     */
    public $root = false;


    /**
     * @var string
     */
	public $render;

    /**
     * @var string
     */
	public $javascript;


    /**
     * @var RokCommon_Filter_Type_Option[]
     */
	public $selections = array();

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $javascript
     */
    public function setJavascript($javascript)
    {
        $this->javascript = $javascript;
    }

    /**
     * @return string
     */
    public function getJavascript()
    {
        return $this->javascript;
    }

    /**
     * @param $selections
     *
     */
    public function setSelections($selections)
    {
        $this->selections = $selections;
    }

    /**
     * @return array|RokCommon_Filter_Type_Options[]
     */
    public function getSelections()
    {
        return $this->selections;
    }

    public function getAsOption()
    {

    }

    /**
     * @param RokCommon_Filter_Chunk_Selection $selection
     */
    public function addSelection(RokCommon_Filter_Chunk_Selection $selection)
    {
        $this->selections[$selection->getId()] = $selection;
    }

    /**
     * @param boolean $parent
     */
    public function setSelector($parent)
    {
        $this->selector = $parent;
    }

    /**
     * @return boolean
     */
    public function isSelector()
    {
        return $this->selector;
    }

    /**
     * @return boolean
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @return boolean
     */
    public function isParent()
    {
        return $this->selector;
    }

    /**
     * @param string $render
     */
    public function setRender($render)
    {
        $this->render = $render;
    }

    /**
     * @return string
     */
    public function getRender()
    {
        return $this->render;
    }

    /**
     * @param boolean $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return boolean
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return boolean
     */
    public function isRoot()
    {
        return $this->root;
    }
}
