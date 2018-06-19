<?php
/**
 * @version   $Id: additem.php 11778 2013-06-26 20:29:33Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Form_Field_Additem extends RokCommon_Form_AbstractField
{
    protected $type = 'AddItem';

    protected $filter;

    public function __construct($form = null)
    {
        parent::__construct($form);
    }


    public function getInput()
    {
        $container = RokCommon_Service::getContainer();
        $empty_button_text = rc__('Add New Item');

        if (isset($this->element['emptybuttontext']))
        {
            $empty_button_text = rc__((string)$this->element['emptybuttontext']);
        }


        $html = array();

        $classes   = explode(' ', $this->element['class']);
        $classes[] = 'roksprocket-filters';
        if (!is_array($this->value)) $classes[] = 'empty';
        $classes = implode(' ', $classes);

        $html[] = '<ul class="' . $classes . '" data-additem="' . $this->id . '" data-additem-name="' . $this->name . '">';
        $html[] = '     <li class="create-new"><div class="btn btn-primary" data-additem-action="addItem">'.$empty_button_text.'</div></li>';
        $html[] = ' </ul>';
        if ($this->element['notice'] && strlen($this->element['notice'])) $html[] = '<div data-cookie="'.$this->id.'" class="roksprocket-filters-description alert alert-info"><a class="close" data-dismiss="alert">&times;</a>' . JText::_($this->element['notice']) . '</div>';

        return implode("\n", $html);
    }

    public function getLabel()
    {
        $label = $this->type;

        if (isset($this->element['label']) && !empty($this->element['label']))
        {
            $label = rc__((string)$this->element['label']);
            $description = rc__((string)$this->element['description']);
            return '<label class="sprocket-tip" title="'.$description.'">'.$label.'</label>';
        } else {
            return;
        }

    }

    public function getTitle()
    {
        return $this->getLabel();
    }

    public function getJSON()
    {
        return $this->filter->getJSON();
    }
}

