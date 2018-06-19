<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

$data = $this->input->getData();
/** @var AngieModelSteps $stepsModel */
$stepsModel = AModel::getAnInstance('Steps', 'AngieModel', array(), $this->container);
$this->input->setData($data);
$crumbs = $stepsModel->getBreadCrumbs();
$i = 0;
?>

<?php if ((isset($helpurl) && !empty($helpurl)) || (isset($videourl) && !empty($videourl))): ?>
<div class="alert alert-info">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php if (isset($helpurl) && !empty($helpurl)): ?>
	<?php echo AText::_('GENERIC_LBL_WHATTODONEXT'); ?>
	<a href="<?php echo $helpurl ?>" class="btn btn-info btn-small" target="_blank">
		<span class="icon-white icon-book"></span>
		<?php echo AText::_('GENERIC_BTN_RTFM'); ?>
	</a>
	<?php endif; ?>
	<?php if (isset($videourl) && !empty($videourl)): ?>
	<a href="<?php echo $videourl ?>" class="btn btn-inverse btn-small" target="_blank">
		<span class="icon-white icon-facetime-video"></span>
		<?php echo AText::_('GENERIC_BTN_VIDEO'); ?>
	</a>
	<?php endif; ?>
</div>
<?php endif; ?>

<ul class="breadcrumb">
<?php $found_active = false; foreach ($crumbs as $crumb): $i++; if ($crumb['active']) { $found_active = true; } ?>
  <li <?php echo $crumb['active'] ? 'class="active"' : '' ?>>
	  <?php echo AText::_('GENERIC_CRUMB_' . $crumb['name']) ?>
	  <?php if((($crumb['substeps'] - $crumb['active_substep']) > 0) && $found_active): ?>
	  <span class="label label-important">
		  <?php if ($crumb['active']): ?>
		  <?php echo $crumb['substeps'] - $crumb['active_substep'] ?>
		  <?php else: ?>
		  <?php echo $crumb['substeps'] ?>
		  <?php endif; ?>
	  </span>
	  <?php endif; ?>
	  <?php if($i < count($crumbs)): ?>
	  <span class="divider icon-chevron-right"></span>
	  <?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>
