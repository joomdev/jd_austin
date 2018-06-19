<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

$this->container->session->disableSave();
?>
<div class="row-fluid">
	<div class="span3"></div>
	<div class="well span6">
		<form class="form-signin" action="index.php" method="post">
			<input type="hidden" name="view" value="password" />
			<input type="hidden" name="task" value="unlock" />

			<h2 class="form-signin-heading">
				<?php echo AText::_('PASSWORD_HEADER_LOCKED'); ?>
			</h2>
			<input type="password" name="password" id="password" class="input-block-level" placeholder="<?php echo AText::_('PASSWORD_FIELD_PASSWORD_LABEL') ?>" />
			<button class="btn btn-large btn-primary" type="submit">
				<span class="icon-white icon-lock"></span>
				<?php echo AText::_('PASSWORD_BTN_UNLOCK') ?>
			</button>
		</form>
	</div>
	<div class="span3"></div>
</div>

<?php
$script = <<<ENDSCRIPT
$(document).ready(function(){
	$('#password').focus();
});

ENDSCRIPT;

/** @var $this AView */

$document = $this->container->application->getDocument();

$x = $document->addScriptDeclaration($script);
