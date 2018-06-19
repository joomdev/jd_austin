<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var $this AView */

$document = $this->container->application->getDocument();

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('angie/js/finalise.js');

$js = <<< JS
$(document).ready(function(){
	if ((window.name == 'installer'))
	{
		jQuery('#finaliseKickstart').show();
	}
	else if ((window.name == 'abiinstaller') || (window.name == 'solo_angie_window'))
	{
		jQuery('#finaliseIntegrated').show();
	}
	else
	{
		jQuery('#finaliseStandalone').show();
	}
});
JS;


$document->addScriptDeclaration($js);

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps', array('helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-installers.html#angie-common-finalise'));
?>
<?php
if(isset($this->extra_warning))
{
    echo $this->extra_warning;
}
?>

<?php if ($this->showconfig): ?>
<?php echo $this->loadAnyTemplate('finalise/config'); ?>
<?php else: ?>
<h3>
	<?php echo AText::_('FINALISE_LBL_READY'); ?>
</h3>
<?php endif; ?>

<div id="finaliseKickstart" style="display: none">
	<p>
		<?php echo AText::_('FINALISE_LBL_KICKSTART'); ?>
	</p>
</div>

<div id="finaliseIntegrated" style="display: none">
	<p>
		<?php echo AText::_('FINALISE_LBL_INTEGRATED'); ?>
	</p>
</div>

<div id="finaliseStandalone" style="display: none">
	<p>
		<?php echo AText::_('FINALISE_LBL_STANDALONE'); ?>
	</p>
	<p>
		<button type="button" class="btn btn-large btn-success" id="removeInstallation">
			<span class="icon-white icon-remove"></span>
			<?php echo AText::_('FINALISE_BTN_REMOVEINSTALLATION'); ?>
		</button>
	</p>
</div>

<div id="error-dialog" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="error-btn-modalclose">&times;</button>
		<h3><?php echo AText::_('FINALISE_HEADER_ERROR') ?></h3>
	</div>
	<div class="modal-body" id="error-message">
		<p><?php echo AText::_('FINALISE_LBL_ERROR') ?></p>
	</div>
</div>

<div id="success-dialog" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="success-btn-modalclose">&times;</button>
		<h3><?php echo AText::_('FINALISE_HEADER_SUCCESS') ?></h3>
	</div>
	<div class="modal-body">
		<p>
			<?php echo AText::sprintf('FINALISE_LBL_SUCCESS', 'https://www.akeebabackup.com/documentation/troubleshooter/prbasicts.html') ?>
		</p>
		<a class="btn btn-success" href="<?php echo AUri::base() . '../index.php' ?>">
			<span class="icon-white icon-forward"></span>
			<?php echo AText::_('FINALISE_BTN_VISITFRONTEND'); ?>
		</a>
	</div>
</div>

