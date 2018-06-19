<?php
/**
 * @version   $Id: Chunk_Selection.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKCOMMON') or die;

/**
 * @RokCommon_JSON_Annotation_JSONDefaultKey('id')
 */
class RokCommon_Filter_Chunk_Selection
{
    public function __construct($id, $render)
    {
        $this->id = $id;
        $this->render = $render;
    }

    /**
     * @var string
     * @RokCommon_JSON_Annotation_JSONEncodeIgnore
     */
    public $id;

    /**
     * @var string
     */
    public $render;


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
}
