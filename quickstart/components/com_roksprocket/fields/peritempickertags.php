<?php
/**
 * @version   $Id: peritempickertags.php 11812 2013-06-27 17:49:30Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('JPATH_PLATFORM') or die;
jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
jimport('joomla.application.component.helper');
JFormHelper::loadFieldClass('list');


class JFormFieldPerItemPickerTags extends JFormField
{
    protected $type = 'PerItemPickerTags';
    protected static $assets_loaded = false;
    protected $options = array();
    protected $data;
    protected $isCustom = true;

    /**
     * @var RokCommon_Service_Container
     */
    protected $container;

    public function __construct($form = null)
    {
        parent::__construct($form);
        $this->container =  RokCommon_Service::getContainer();
    }

    protected function getLabel()
    {
        if ($this->container->hasParameter('roksprocket.current_provider'))
        {
            /** @var RokSprocket_IProvider $provider */
            $provider = $this->container->getParameter('roksprocket.current_provider');
            if (!$provider->allowFieldRender($this->type, $this->fieldname, $this->value)) return '';
        }
        return parent::getLabel();
    }


    function getInput(){
        JHtml::_('behavior.modal');
        //$this->_loadAssets();
        $this->_setOptions();

        if ($this->container->hasParameter('roksprocket.current_provider'))
        {
            /** @var RokSprocket_IProvider $provider */
            $provider = $this->container->getParameter('roksprocket.current_provider');
            $provider->filterPerItemTypes($this->type, $this->fieldname, $this->options);
        }

        if (preg_match("/^-([a-z]{1,})-$/", $this->value)){
            if ($this->value == '-article-' && preg_match("/_title$/", $this->id)) $this->value = '-title-';
            $this->isCustom = false;
        }

        if (!$this->value) $this->value = array();
        if (!is_array($this->value)){
            if (strpos($this->value, ',') === false) $this->value = array($this->value);
            else $this->value = explode(",", preg_replace("/\s/", "", $this->value));
        }

        $class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . ' peritempickertags'.((count($this->options) == 1) ? ' single' : '').'"' : ' class="peritempickertags"';
        $placeholder = $this->element['placeholder'] ? ' placeholder="' .(string)$this->element['placeholder']  .'"' : '';

        $html = array();

        //$cleaned_value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
        $html[] = '<div class="peritempickertags-wrapper'.(!$this->isCustom ? ' peritempickertags-noncustom' : '').'" data-peritempickertags="true" data-peritempickertags-id="'.$this->id.'" data-peritempickertags-name="'.$this->name.'">';
        $html[] = '<div class="tags" data-tags>';

        $html[] = '   <ul class="tags-holder" data-tags-holder>';
        $html[] =         $this->_createTags();
        $html[] = '       <li class="tags-input main-input"><input type="text" data-tags-maininput style="width: 15px;" /></li>';
        $html[] = '   </ul>';

        $html[] = '   <input type="hidden" id="'.$this->id.'" name="'.$this->name.'" '.$class.' value="'.implode(", ", $this->value).'" data-tags-input data-peritempickertags-display="true" />';
        //$html[] = ' <input data-peritempickertags-display="true" data-original-title="'.$cleaned_value.'" type="text" value="'.$cleaned_value.'" '.$class.$placeholder.' />';

        $html[] = '</div>';



        //$html[] = '     <input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'.$cleaned_value.'" />';
        $html[] = $this->_getDropdown();
        $html[] = '</div>';

        return implode("\n", $html);
    }

    protected function _createTags(){
        $output = array();
        /*if (strpos($this->value, ',') === false) $values = array($this->value);
        else $values = explode(",", preg_replace("/\s/", "", $this->value));*/

        foreach($this->value as $index => $value){
            $output[] = '   <li class="tags-box" data-tags-box="'.$value.'"><span class="tags-title">'. $value .'</span><span class="tags-remove" data-tags-remove>&times;</span></li>';
        }

        return implode("\n", $output);
    }

    public function _setOptions(){
        $options = array();

        foreach($this->element->children() as $option){
            $name = rc__((string) $option);
            $value = (string) $option['value'];

            if ($value == 'divider') $options['divder'] = array("name" => '', 'attributes' => array('class' => 'divider', 'data-divider' => true));
            else $options[$value] = array("name" => $name, 'attributes' => array('value' => $value, 'data-value' => $value, 'icon' => false));
        }

        $this->options = $options;

        return $options;
    }

    public function _getDropdown(){
        $output = $list = array();
        $options = $this->options;
        $s_value = implode(',', $this->value);

        if (isset($this->value) && !array_key_exists($s_value, $options)) $s_value = '-article-';
        if (isset($this->value) && !array_key_exists($s_value, $options)) $s_value = '-none-';

        foreach($options as $option){
            $attributes = $option['attributes'];
            $class = (isset($attributes['class']) ? $attributes['class'] : "") . (isset($attributes['disabled']) ? " disabled" : "");
            $class = (strlen($class) ? 'class="'. $class . '"' : "");

            $divider = (isset($attributes['data-divider']))? $attributes['data-divider'] : "";
            $dataValue = (isset($attributes['data-value']))? $attributes['data-value'] : '';
            $value = (isset($attributes['value']))? $attributes['value'] : '';

            $list[] = '     <li '.$class.' data-dynamic="false" data-text="" data-value="'.$value.'">';
            if (!$divider) $list[] = '          <a href="#"><span>'.$option['name'].'</span></a>';
            $list[] = '     </li>';
        }

        // rendering output

        if (count($options) > 1){
            $output[] = '<div class="sprocket-dropdown imagepicker">';
            $output[] = '   <a href="#" class="btn dropdown-toggle" data-toggle="dropdown">';
            $output[] = '       <span class="name">'.(!$this->isCustom && isset($this->options[$s_value]) ? $this->options[$s_value]['name'] : '').'</span>';
            $output[] = '       <span class="caret"></span>';
            $output[] = '   </a>';
            $output[] = '   <ul class="dropdown-menu">';

            $output[] = implode("\n", $list);

            $output[] = '   </ul>';
            $output[] = '   <div class="dropdown-original">';
        } else {
            $output[] = '<div class="sprocket-dropdown imagepicker">';
            $output[] = '   <div class="single-layout btn dropdown-toggle">'.$this->options[$s_value]['name'].'</div>';
            $output[] = '</div>';
        }

        // original select
        $output[] = '       <select data-chosen="skip" class="chzn-done" '.((count($options) == 1) ? ' style="display: none;"' : '').'>';

        foreach($options as $option){
            $attributes = $option['attributes'];
            $divider = (isset($attributes['data-divider']))? $attributes['data-divider'] : "";
            $dataValue = (isset($attributes['data-value']))? $attributes['data-value'] : '';
            $value = (isset($attributes['value']))? $attributes['value'] : '';

            $selected = ((isset($this->data->type) && $dataValue == $this->data->type) || $dataValue == $s_value || ($dataValue == '' && !preg_match("/^-([a-z]{1,})-$/", $s_value))) ? ' selected="selected" ' : "";
            $output[] = '           <option value="' . $value . '" '.$selected.'>' . $option['name'] . '</option>';
        }
        $output[] = '       </select>';

        if (count($options) > 1){
            $output[] = '   </div>';
            $output[] = "</div>";
        }

        return implode("\n", $output);
    }

    /*public function _loadAssets(){
        if (!self::$assets_loaded){
            $type = strtolower($this->type);
            $assets = JURI::root() . 'components/' . JFactory::getApplication()->input->getString('option') . '/fields/' . $type . '/';

            $js =  $assets . 'js/' . $type . '.js';
            RokCommon_Header::addScript($js);
            RokCommon_Header::addInlineScript($this->attachJavaScript());

            self::$assets_loaded = true;
        }
    }

    protected function attachJavaScript(){
        $js = array();
        $js[] = "window.addEvent('domready', function(){";
        $js[] = "   RokSprocket.articles.addEvent('onModelSuccess', function(response){";
        $js[] = "       var peritempickers = document.getElements('.articles [data-peritempicker]');";
        $js[] = "       RokSprocket.peritempicker.attach(peritempickers);";
        $js[] = "   });";
        $js[] = "});";

        return implode("\n", $js);
    }*/
}

?>
