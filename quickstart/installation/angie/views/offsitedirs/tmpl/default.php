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

$this->loadHelper('select');

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('angie/js/offsitedirs.js');
$url = 'index.php';
$document->addScriptDeclaration(<<<ENDSRIPT
var akeebaAjax = null;
$(document).ready(function(){
	akeebaAjax = new akeebaAjaxConnector('$url');
});
ENDSRIPT
);

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps', array('helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-installers.html#angie-common-offsite'));
?>

<div class="modal hide fade" id="restoration-dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="restoration-btn-modalclose">&times;</button>
		<h3><?php echo AText::_('OFFSITEDIRS_HEADER_COPY') ?></h3>
	</div>
	<div class="modal-body">
		<div id="restoration-progress">
			<div class="progress progress-striped active">
				<div class="bar" id="restoration-progress-bar" style="width: 40%;"></div>
			</div>
		</div>
		<div id="restoration-success">
			<div class="alert alert-success">
				<?php echo AText::_('OFFSITEDIRS_HEADER_SUCCESS'); ?>
			</div>
			<p>
				<?php echo AText::_('OFFSITEDIRS_MSG_SUCCESS'); ?>
			</p>
			<button type="button" onclick="databaseBtnSuccessClick(); return false;" class="btn btn-success">
				<span class="icon-white icon-check"></span>
				<?php echo AText::_('OFFSITEDIRS_BTN_SUCCESS'); ?>
			</button>
		</div>
		<div id="restoration-error">
			<div class="alert alert-error">
				<?php echo AText::_('OFFSITEDIRS_HEADER_ERROR'); ?>
			</div>
			<div class="well well-small" id="restoration-lbl-error">

			</div>
		</div>
	</div>
</div>

<?php if ($this->number_of_substeps): ?>
<h1><?php echo AText::sprintf('OFFSITEDIRS_HEADER_MASTER', $this->substep['target']) ?></h1>
<?php endif; ?>

<div class="row-fluid">
	<div class="span6">
		<h3><?php echo AText::_('OFFSITEDIRS_FOLDER_DETAILS');?></h3>

		<div class="form-horizontal">
			<div class="control-group">
				<label class="control-label" for="virtual_folder"><?php echo AText::_('OFFSITEDIRS_VIRTUAL_FOLDER') ?></label>
				<div class="controls">
					<input type="text" id="virtual_folder" class="input-xxlarge" disabled="disabled" value="<?php echo $this->substep['virtual']?>"/>
				</div>
			</div>

            <div class="control-group">
                <label class="control-label" for="target_folder"><?php echo AText::_('OFFSITEDIRS_TARGET_FOLDER')?></label>
                <div class="controls">
                    <input type="text" id="target_folder" class="input-xxlarge" value="<?php echo $this->substep['target']?>"/>
                </div>
            </div>
		</div>
	</div>
</div>
