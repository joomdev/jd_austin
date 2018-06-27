<?php
/**
 * @version   $Id: imagepicker.php 30492 2016-12-09 10:08:35Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */



class RokCommon_Form_Field_ImagePicker extends RokCommon_Form_AbstractField
{
    protected $type = 'ImagePicker';
    protected static $assets_loaded = false;
    protected $options = array();
    protected $data;
    protected $isCustom = true;

	function getInput(){
		//JHTML::_('behavior.modal');
		//$this->_loadAssets();
		$this->_setOptions();
		$container = RokCommon_Service::getContainer();
		/** @var RokSprocket_IProvider $provider */
		$provider = $container->getParameter('roksprocket.current_provider');
		$provider->filterPerItemTypes($this->type, $this->fieldname, $this->options);

		$this->value = str_replace("'", '"', str_replace('\\', '', $this->value));
		$link = $this->options['mediamanager']['attributes']['value'];

		if (!$this->value){
			$link = $this->options['mediamanager']['attributes']['value'];
			$this->value = '{"type":"mediamanager","path":"","preview":"","link":"'.$link.'"}';
		}

		if (preg_match("/^-([a-z]{1,})-$/", $this->value)){
			if ($this->value == '-primary-') $this->value = '-article-';
			$this->isCustom = false;
		} else if (!preg_match("/[{]/", $this->value)){
			$this->value = '{"type":"mediamanager","path":"'.$this->value.'","preview":"","link":"'.$link.'"}';
		}

		$this->data = json_decode($this->value);
		if (empty($this->data->path) && !preg_match("/^-([a-z]{1,})-$/", $this->value)) $this->value = "";
		if (isset($this->data->link)) $link = $this->data->link;

		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . ' imagepicker'.((count($this->options) == 1) ? ' single' : '').'"' : ' class="imagepicker"';
		$placeholder = $this->element['placeholder'] ? ' placeholder="' .(string)$this->element['placeholder']  .'"' : '';

		if (isset($this->data->path)){
			$preview = !empty($this->data->preview) ? $this->data->preview : $this->data->path;
			if (!preg_match("/^https?:\/\//", $preview) && substr($preview, 0, 1) != '/'){
				//$preview = JURI::root(true) . '/' . $preview;
			}

			$tipTitle = '<div class=\'imagepicker-tip-preview\'><img src=\''.$preview.'\' /></div>';
			if (isset($this->data->width)) $tipTitle .= '<div class=\'imagepicker-tip-size\'>'.$this->data->width.' &times; '.$this->data->height.'</div>';
			$tipTitle .= '<div class=\'imagepicker-tip-path\'>'.$this->data->path.'</div>';
			$path = $this->data->path;
			if (!strlen($path)) $tipTitle = '';
		} else {
			$path = '';
			$tipTitle = "";
		}

		$html = array();

		$html[] = '<div class="imagepicker-wrapper'.(!$this->isCustom ? ' peritempicker-noncustom' : '').'" data-imagepicker="true" data-imagepicker-id="'.$this->id.'" data-imagepicker-name="'.$this->name.'">';
		$html[] = '		<input data-imagepicker-display="true" data-original-title="'.rc__($tipTitle).'" type="text" value="'.$path.'" '.$class.$placeholder.' />';
		$html[] = '		<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'.htmlspecialchars($this->value).'" />';
		$html[] = $this->_getDropdown();
		$html[] = '		<a href="'.$link.'" class="modal imagepicker" title="Select Image" rel="{handler: \'iframe\', size: {x: 790, y: 450}}"><i class="icon tool picker"></i></a>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	public function _setOptions(){
		$options = array();

		if (!isset($this->element['article-defaults'])){
			// Defaults List
			$options['-none-'] = array(
				'name' => rc__('JNONE'),
				'attributes' => array(
					'value' => '-none-',
					'icon' => false,
					'data-value' => '-none-'
				)
			);

			$options['-default-'] = array(
				'name' => rc__('JDEFAULT'),
				'attributes' => array(
					'value' => '-default-',
					'icon' => false,
					'data-value' => '-default-'
				)
			);

			$options['-article-'] = array(
				'name' => rc__('ROKSPROCKET_ARTICLE_IMAGE'),
				'attributes' => array(
					'value' => '-article-',
					'icon' => false,
					'data-value' => '-article-'
				)
			);

			// Divider
			$options['divider'] = array(
				'name' => '',
				'attributes' => array(
					'class' => 'divider',
					'data-divider' => 'true'
				)
			);
		}


		// Default Joomla Media Manager
		$options['mediamanager'] = array(
			'name' => 'MediaManager',
			'attributes' => array(
				'value' => 'index.php?option=com_media&view=images&layout=default&tmpl=component&e_name=' . $this->id,
				'icon' => 'mediamanager',
				'data-value' => 'mediamanager'
			)
		);


		// List of Supported components picker such as RokGallery here
		if ($this->_isInstalled('com_rokgallery')){
			$options['rokgallery'] = array(
				'name' => 'RokGallery',
				'attributes' => array(
					'value' => 'index.php?option=com_rokgallery&view=gallerypicker&tmpl=component&show_menuitems=0&inputfield=' . $this->id,
					'icon' => 'rokgallery',
					'data-value' => 'rokgallery'
				)
			);
		}

		$this->options = $options;

		return $options;
	}

	public function _isInstalled($component = false){

		$installed = false;
		if (file_exists(JPATH_SITE.'/components/'.$component)) {
			$component = JComponentHelper::getComponent($component, true);
			$installed = $component->enabled;
		}
		return $installed;
	}

	public function _getDropdown(){
		$output = $list = array();
		$displayValue = "mediamanager";
		$options = $this->options;

		if (isset($this->value) && !array_key_exists($this->value, $options)) $this->value = '-title-';
		if (isset($this->value) && !array_key_exists($this->value, $options)) $this->value = '-none-';

		foreach($options as $option){
			$attributes = $option['attributes'];
			$class = (isset($attributes['class']) ? $attributes['class'] : "") . (isset($attributes['disabled']) ? " disabled" : "");
			$class = (strlen($class) ? 'class="'. $class . '"' : "");
			$icon = (isset($attributes['icon']) ? $attributes['icon'] : "");

			$divider = (isset($attributes['data-divider']))? $attributes['data-divider'] : "";
			$dataValue = (isset($attributes['data-value']))? $attributes['data-value'] : '';
			$value = (isset($attributes['value']))? $attributes['value'] : '';

			if (isset($this->data->type) && $this->data->type == $dataValue){
				$displayValue = $dataValue;
			}

			if (strlen($icon)) $icon_html = '<i data-dynamic="false" class="icon media '.$attributes['icon'].'"></i>';
			else $icon_html = "";

			$list[] = '		<li '.$class.' data-dynamic="false" data-icon="media '.$icon.'" data-text="" data-value="'.$value.'">';
			if (!$divider) $list[] = '			<a href="#">'.$icon_html.'<span>'.$option['name'].'</span></a>';
			$list[] = '		</li>';
		}

		// rendering output
		$class = "media " . $displayValue;

		if (count($options) > 1){
			$output[] = '<div class="sprocket-dropdown imagepicker">';
			$output[] = '	<a href="#" class="btn dropdown-toggle" data-toggle="dropdown">';
			if (strlen($icon))
				$output[] = '		<i data-dynamic="false" class="icon '.$class.'"></i> ';
			$output[] = ' 		<span class="name">'.(!$this->isCustom ? $this->options[$this->value]['name'] : '').'</span>';
			$output[] = ' 		<span class="caret"></span>';
			$output[] = '	</a>';
			$output[] = '	<ul class="dropdown-menu">';

			$output[] = implode("\n", $list);

			$output[] = '	</ul>';
			$output[] = '	<div class="dropdown-original">';
		} else {
			$output[] = '<div class="sprocket-dropdown imagepicker">';
			$output[] = '	<div class="single-layout btn dropdown-toggle"><i class="icon '.$class.'"></i></div>';
			$output[] = '</div>';
		}

		// original select
		$output[] = '		<select data-chosen="skip" class="chzn-done" '.((count($options) == 1) ? ' style="display: none;"' : '').'>';
		foreach($options as $option){
			$attributes = $option['attributes'];
			$divider = (isset($attributes['data-divider']))? $attributes['data-divider'] : "";
			$dataValue = (isset($attributes['data-value']))? $attributes['data-value'] : '';
			$value = (isset($attributes['value']))? $attributes['value'] : '';

			$selected = ((isset($this->data->type) && $dataValue == $this->data->type) || $dataValue == $this->value) ? ' selected="selected" ' : "";
			$output[] = '			<option value="' . $value . '" '.$selected.'>' . $option['name'] . '</option>';
		}
		$output[] = '		</select>';

		if (count($options) > 1){
			$output[] = '	</div>';
			$output[] = "</div>";
		}

		return implode("\n", $output);
	}

	/*public function _loadAssets(){
		if (!self::$assets_loaded){
			$type = strtolower($this->type);
			$assets = JURI::root() . 'components/' . JRequest::getString('option') . '/fields/' . $type . '/';

			$js =  $assets . 'js/' . $type . '.js';
			RokCommon_Header::addScript($js);
			RokCommon_Header::addInlineScript($this->attachJavaScript());

			self::$assets_loaded = true;
		}
	}

	protected function attachJavaScript(){
		$js = array();
		$js[] = "window.addEvent('domready', function(){";
		$js[] = "	RokSprocket.articles.addEvent('onModelSuccess', function(response){";
		$js[] = "		var imagepickers = document.getElements('.articles [data-imagepicker]');";
		$js[] = "		RokSprocket.imagepicker.attach(imagepickers);";
		$js[] = "		SqueezeBox.assign(imagepickers.getElement('a.modal'), {parse: 'rel'});";
		$js[] = "	});";
		$js[] = "});";

		return implode("\n", $js);
	}*/

}
