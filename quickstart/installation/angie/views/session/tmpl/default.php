<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();
?>
<div class="alert">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php echo AText::_('SESSION_LBL_MAINMESSAGE'); ?>
</div>

<p>
	<?php echo AText::_('SESSION_LBL_INTRO'); ?>
	<?php echo ($this->hasFTP) ? AText::_('SESSION_LBL_INTROFTP') : '' ?>
</p>

<?php if ($this->hasFTP): ?>
<h3>
	<?php echo AText::_('SESSION_LBL_FTPINFORMATION'); ?>
</h3>

<form class="form-horizontal span12" name="sessionForm" action="index.php" method="POST">
	<input type="hidden" name="view" value="session" />
	<input type="hidden" name="task" value="fix" />

	<div class="control-group">
		<label class="control-label" for="hostname">
			<?php echo AText::_('SESSION_FIELD_HOSTNAME_LABEL'); ?>
		</label>
		<div class="controls">
			<input type="text" class="input-large" name="hostname" id="hostname" value="<?php echo $this->state->hostname ?>" />
			<span class="help-block">
				<?php echo AText::_('SESSION_FIELD_HOSTNAME_DESC'); ?>
			</span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="port">
			<?php echo AText::_('SESSION_FIELD_PORT_LABEL'); ?>
		</label>
		<div class="controls">
			<input type="text" class="input-large" name="port" id="port" value="<?php echo $this->state->port ?>" />
			<span class="help-block">
				<?php echo AText::_('SESSION_FIELD_PORT_DESC'); ?>
			</span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="username">
			<?php echo AText::_('SESSION_FIELD_USERNAME_LABEL'); ?>
		</label>
		<div class="controls">
			<input type="text" class="input-large" name="username" id="username" value="<?php echo $this->state->username ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="password">
			<?php echo AText::_('SESSION_FIELD_PASSWORD_LABEL'); ?>
		</label>
		<div class="controls">
			<input type="password" class="input-large" name="password" id="password" value="<?php echo $this->state->password ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="directory">
			<?php echo AText::_('SESSION_FIELD_DIRECTORY_LABEL'); ?>
		</label>
		<div class="controls">
			<div class="input-append">
				<input type="text" class="input-large" name="directory" id="directory" value="<?php echo $this->state->directory ?>" />
				<button type="button" class="btn add-on" id="ftpbrowser" onclick="openFTPBrowser();">
					<span class="icon-folder-open"></span>
					<?php echo AText::_('SESSION_BTN_BROWSE'); ?>
				</button>
			</div>
			<span class="help-block">
				<?php echo AText::_('SESSION_FIELD_DIRECTORY_DESC'); ?>
			</span>
		</div>
	</div>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary btn-large">
			<span class="icon-white icon-plane"></span>
			<?php echo AText::_('SESSION_BTN_APPLY'); ?>
		</button>
	</div>
</form>

<div id="browseModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="browseModalLabel">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="browseModalLabel"><?php echo AText::_('GENERIC_FTP_BROWSER');?></h3>
	</div>
	<div class="modal-body">
		<iframe id="browseFrame" src="index.php?view=ftpbrowser" width="100%" height="300px"></iframe>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">
			<?php echo AText::_('SESSION_BTN_CANCEL') ?>
		</button>
	</div>
</div>

<script type="text/javascript">
function openFTPBrowser()
{
	var hostname = $('#hostname').val();
	var port = $('#port').val();
	var username = $('#username').val();
	var password = $('#password').val();
	var directory = $('#directory').val();

	if ((port <= 0) || (port >= 65536))
	{
		port = 21;
	}

	var url = 'index.php?view=ftpbrowser&tmpl=component'
		+ '&hostname=' + encodeURIComponent(hostname)
		+ '&port=' + encodeURIComponent(port)
		+ '&username=' + encodeURIComponent(username)
		+ '&password=' + encodeURIComponent(password)
		+ '&directory=' + encodeURIComponent(directory);

		document.getElementById('browseFrame').src = url;

	$('#browseModal').modal({
		keyboard: false
	});
}

function useFTPDirectory(path)
{
	$('#directory').val(path);
	$('#browseModal').modal('hide');
}
</script>
<?php endif; ?>
