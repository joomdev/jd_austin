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
$document->addStyleSheet(JUri::base(true).'/modules/'.$module->module.'/css/default.min.css');
$id = 'mod_skill-'.$module->id;
$document->addScriptDeclaration("jQuery(document).ready(function($){ $('.skillset.$id .counting').counterUp({time: 1000}); });");
?>
<section class="skillset default <?= $id ?>" <?php if($bgImage) : ?> style="background-image: url(<?php echo $bgImage ?>)" <?php endif; ?>>
  <div class="container">
    <div class="row">
      <?php if($module->showtitle) : ?>
      <div class="col-12 title-section text-center mb-5">
        <h2 style="color: <?= $skillColor ?>"><?php echo $module->title ?></h2>
      </div>
      <?php endif; ?>

      <?php foreach($skills as $item) : ?>
        <div class="skill col-12 col-sm-12 col-md-6 col-lg-<?php echo $col ?>" style="color: <?= $skillColor ?>">
          <div class="icon"><i class="<?php echo $item['skillicon'] ?>"></i></div>
          <div class="counting"><?php echo $item['skillcount'] ?></div>
          <div class="description"><?php echo $item['skillname'] ?></div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>
