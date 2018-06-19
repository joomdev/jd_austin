<?php
defined('ROKCOMMON') or die;
class RokCommon_Form_Field_Tags extends RokCommon_Form_AbstractField
{
	protected $type = 'Tags';

	public function getInput()
	{

		//$document = JFactory::getDocument();
		//$document->addStyleSheet(substr(JURI::base(true), 0, -14) . '/templates/rt_voxel/admin/forms/fields/tags/css/tags.css');
		//$document->addScript(substr(JURI::base(true), 0, -14) . '/templates/rt_voxel/admin/forms/fields/tags/js/resizable-textbox.js');
		//$document->addScript(substr(JURI::base(true), 0, -14) . '/templates/rt_voxel/admin/forms/fields/tags/js/tags.js');

		$class = isset($this->element['class']) ? 'class="'.$this->element['class'].'"' : '';

		if (!$this->value) $this->value = array();
		if (!is_array($this->value)) $this->value = explode(",", preg_replace("/\s/", "", $this->value));


		$output = array();

		$output[] = '<div class="tags" data-tags>';

		$output[] = '	<ul class="tags-holder" data-tags-holder>';
		$output[] = 		$this->_createTags();
		$output[] = '		<li class="tags-input main-input"><input type="text" data-tags-maininput style="width: 15px;" /></li>';
		$output[] = '	</ul>';

		$output[] = '	<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" '.$class.' value="'.implode(", ", $this->value).'" data-tags-input />';

		$output[] = '</div>';
		return implode("\n", $output);
	}

	protected function _createTags(){
		$output = array();

		foreach($this->value as $index => $value){
			$output[] = '	<li class="tags-box" data-tags-box="'.$value.'"><span class="tags-title">'. $value .'</span><span class="tags-remove" data-tags-remove>&times;</span></li>';
		}

		return implode("\n", $output);
	}
}
