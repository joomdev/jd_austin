<?php
# @Author: SPEDI srl
# @Date:   23-01-2018
# @Email:  sviluppo@spedi.it
# @Last modified by:   SPEDI srl
# @Last modified time: 25-01-2018
# @License: GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
# @Copyright: Copyright (C) SPEDI srl

defined('_JEXEC') or die;

JLoader::register('ModSkillsetHelper', __DIR__ . '/helper.php');

/* general params */
$document     = JFactory::getDocument();
$bootstrap    = $params->get('spskill-bootstrap');
$jquery       = $params->get('spskill-jquery');
$bgImage      = $params->get('spskill-bg-image');
$bgColor      = $params->get('spskill-bg-color');
$skillColor   = $params->get('spskill-skill-color');
$description  = $params->get('spskill-description');
$pattern      = $params->get('spskill-pattern');
if($bgColor){
  $bgColor = ModSkillsetHelper::hexToRGB($bgColor);
  $document->addStyleDeclaration(' .skillset::before{background-color: rgba('.$bgColor.', 0.6);}'."\n");
}
if($pattern){
  $document->addStyleDeclaration(' .skillset::after{background-image: url("'.JUri::base(true).'/modules/'.$module->module.'/images/pattern.png")}'."\n");
}

/* script */
if($jquery)
  JHtml::_('jquery.framework');
$document->addScript(JUri::base(true).'/modules/'.$module->module.'/dist/jquery.counterup.min.js');

/* skill params */
$k = 0;
for($c = 1; $c < 5; $c++){
  if($params->get('spskill-name-'.$c) != ''){
    $k++;
    $skills[] = array('skillname'  => $params->get('spskill-name-'.$c),
                      'skillicon'  => $params->get('spskill-icon-'.$c),
                      'skillcount' => $params->get('spskill-count-'.$c)
                     );
  }
}
/* colonne */
$col = 12 / $k;

/* layout */
require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
