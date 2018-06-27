<?php
/**
 * @version   $Id: filters.php 14427 2013-10-10 21:29:18Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

class JFormFieldFilters extends JFormField
{
    protected $type = 'Filters';
	protected static $base_js_loaded = false;

    protected $filter;

    public function __construct($form = null)
    {
        parent::__construct($form);
    }


    protected function getInput()
    {
        $container = RokCommon_Service::getContainer();
        $empty_button_text = rc__('Create New Filter');

	    if (isset($this->element['filterfileparam'])){
	        $filter_filename = $container->getParameter(($this->element['filterfileparam']));
	    }
	    else if ($this->element['filterfile']) {
		    $filter_filename = (string)$this->element['filterfile'];
	    }
        $filter_file = $container[(string)$this->element['filterlocationparam']] . '/' . $filter_filename;
        if (!file_exists($filter_file)) {
            throw new RokSprocket_Exception(rc__('Unable to find filter file %s', $filter_file));
        }
	    $xmlfile = simplexml_load_file($filter_file);
        $this->filter = new RokCommon_Filter($xmlfile);

        if (isset($this->element['emptybuttontext']))
        {
            $empty_button_text = rc__((string)$this->element['emptybuttontext']);
        }

		if(!self::$base_js_loaded)
		{
			RokCommon_Header::addInlineScript('
		                var RokSprocketFilters = {
		                    filters: {},
		                    template: \'<li><span data-filter-container="true"></span> <span class="controls"> <i class="icon tool minus" data-filter-action="removeRow"></i> <i class="icon tool plus" data-filter-action="addRow"></i></span></li>\'
		                };
		            ');
			self::$base_js_loaded = true;
		}




        $html = array();
        /*
            After everything fine, i'll handle via js and domready the call to filters ajax model
            Something along these lines:

                model: 'Filters',
                action: 'getData',
                params: JSON.encoded(
                    [{
                        id1: {pathrefs: .., file: ..}
                    }],
                    [{
                        id2: {pathrefs: .., file: ..}
                    }],
                    [{
                        id3: {pathrefs: .., file: ..}
                    }],
                    ...
                )
        */


/*        // OLD Script
	      RokCommon_Header::addInlineScript('
            window.addEvent(\'load\', function(){
                RokSprocket.filters.addDataSet(\'' . $this->id . '\', {
                    pathsref: \''. (string)$this->element['filterlocationparam'] .'\',
                    file: \'' . (string)$this->element['filterfile'] .'\',
                    template: \'<li><span data-filter-container="true"></span> <span class="controls"> <i class="icon tool minus" data-filter-action="removeRow"></i> <i class="icon tool plus" data-filter-action="addRow"></i></span></li>\'
                });
            });
        ');*/

	    RokCommon_Header::addInlineScript("
	 			            RokSprocketFilters.filters['".$this->id."'] = {
	 			                pathsref: '". (string)$this->element['filterlocationparam'] . "',
	 			                file: '" . $filter_filename ."'
	 			            }");


        $classes   = explode(' ', $this->element['class']);
        $classes[] = 'roksprocket-filters';
        if (!is_array($this->value)) $classes[] = 'empty';
        $classes = implode(' ', $classes);

        $html[] = '<ul class="' . $classes . '" data-filter="' . $this->id . '" data-filter-name="' . $this->name . '">';
        $html[] = '     <li class="create-new"><div class="btn btn-primary" data-filter-action="addRow">'.$empty_button_text.'</div></li>';

        if (is_array($this->value)) {
            foreach ($this->value as $rownum => $row) {
                $firstRow = ($rownum == 1) ? ' class="first"' : '';
                RokCommon_Utils_ArrayHelper::fromObject($row);
                $html[] = '     <li data-row="true"' . $firstRow . '><span data-filter-container="true">' . $this->filter->renderLine($row, $this->name . '[' . $rownum . ']') . '</span><span class="controls"><i data-filter-action="removeRow" class="icon tool minus"></i><i data-filter-action="addRow" class="icon tool plus"></i></span></li>';
            }
        }
        $html[] = '	</ul>';
        if ($this->element['notice'] && strlen($this->element['notice'])) $html[] = '<div data-cookie="'.$this->id.'" class="roksprocket-filters-description alert alert-info"><a class="close" data-dismiss="alert">&times;</a>' . JText::_($this->element['notice']) . '</div>';

        return implode("\n", $html);
    }

    protected function getLabel()
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

    protected function getTitle()
    {
        return $this->getLabel();
    }

    protected function getJSON()
    {
        return $this->filter->getJSON();
    }
}

