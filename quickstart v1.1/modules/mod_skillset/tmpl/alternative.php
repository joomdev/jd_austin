<?php
# @Author: SPEDI srl
# @Date:   23-01-2018
# @Email:  sviluppo@spedi.it
# @Last modified by:   SPEDI srl
# @Last modified time: 25-01-2018
# @License: GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
# @Copyright: Copyright (C) SPEDI srl

defined('_JEXEC') or die;

/* style */
$document->addStyleSheet(JUri::base(true).'/modules/'.$module->module.'/css/alternative.min.css');
$id = 'mod_skill-'.$module->id;
$document->addScriptDeclaration("jQuery(document).ready(function($){ $('.skillset.$id .counting').counterUp({time: 1000}); });");
?>
<section class="skillset alternative wrapper <?= $id ?>">
  <div class="container">
    <div class="row">
      <div class="col-6">
        <?php if($module->showtitle) : ?>
        <h2><?php echo $module->title ?></h2>
        <?php endif; ?>
        <?php if($description) : ?>
          <p><?php echo $description ?></p>
        <?php endif; ?>
      </div>
      <div class="col-6">
        <?php foreach($skills as $item) : ?>
        <div class="skill pl-3 mb-3" style="border-color: <?= $skillColor ?>">
          <small class="text-uppercase"><?php echo $item['skillname'] ?></small>
          <div class="counting"><?php echo $item['skillcount'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
