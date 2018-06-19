<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer For Joomla!
 */

defined('_AKEEBA') or die();

/** @var $this AngieViewSetup */

$document = $this->container->application->getDocument();

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('platform/js/setup.js');

$url = 'index.php';

$document->addScriptDeclaration(<<<ENDSRIPT
var akeebaAjax = null;
$(document).ready(function(){
	akeebaAjax = new akeebaAjaxConnector('$url');
});
ENDSRIPT
);

$this->loadHelper('select');

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps', array('helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-joomla-setup.html'));
?>
<form name="setupForm" action="index.php" method="post">
	<input type="hidden" name="view" value="setup" />
	<input type="hidden" name="task" value="apply" />

	<div class="row-fluid">
		<!-- Site parameters -->
		<div class="span6">
			<h3><?php echo AText::_('SETUP_HEADER_SITEPARAMS') ?></h3>
			<div class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="sitename">
						<?php echo AText::_('SETUP_LBL_SITENAME'); ?>
					</label>
					<div class="controls">
						<input type="text" id="sitename" name="sitename" value="<?php echo $this->stateVars->sitename ?>" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LBL_SITENAME_HELP') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="siteemail">
						<?php echo AText::_('SETUP_LBL_SITEEMAIL'); ?>
					</label>
					<div class="controls">
						<input type="text" id="siteemail" name="siteemail" value="<?php echo $this->stateVars->siteemail ?>" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LBL_SITEEMAIL_HELP') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="emailsender">
						<?php echo AText::_('SETUP_LBL_EMAILSENDER'); ?>
					</label>
					<div class="controls">
						<input type="text" id="emailsender" name="emailsender" value="<?php echo $this->stateVars->emailsender ?>" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LBL_EMAILSENDER_HELP') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="livesite">
						<?php echo AText::_('SETUP_LBL_LIVESITE'); ?>
					</label>
					<div class="controls">
						<input type="text" id="livesite" name="livesite" value="<?php echo $this->stateVars->livesite ?>" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LBL_LIVESITE_HELP') ?>"></span>
						<?php if (substr(PHP_OS, 0, 3) == 'WIN'): ?>
						<span class="help-block alert alert-warning">
							<span class="icon icon-warning-sign"></span>
							<?php echo AText::_('SETUP_LBL_LIVESITE_WINDOWS_WARNING') ?>
						</span>
						<?php endif; ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="force_ssl">
						<?php echo AText::_('SETUP_LABEL_FORCESSL'); ?>
					</label>
					<div class="controls">
						<?php echo AngieHelperSelect::forceSSL($this->stateVars->force_ssl); ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LABEL_FORCESSL_TIP') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="cookiedomain">
						<?php echo AText::_('SETUP_LBL_COOKIEDOMAIN'); ?>
					</label>
					<div class="controls">
						<input type="text" id="cookiedomain" name="cookiedomain" value="<?php echo $this->stateVars->cookiedomain ?>" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LBL_COOKIEDOMAIN_HELP') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="cookiepath">
						<?php echo AText::_('SETUP_LBL_COOKIEPATH'); ?>
					</label>
					<div class="controls">
						<input type="text" id="cookiepath" name="cookiepath" value="<?php echo $this->stateVars->cookiepath ?>" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LBL_COOKIEPATH_HELP') ?>"></span>
					</div>
				</div>
            <?php if (version_compare($this->container->session->get('jversion'), '3.2', 'ge')):?>
                <div class="control-group">
                    <label class="control-label" for="mailonline">
                        <?php echo AText::_('SETUP_LBL_MAILONLINE'); ?>
                    </label>
                    <div class="controls">
                        <label class="radio inline">
                            <input type="radio" <?php echo !$this->stateVars->mailonline ? 'checked="checked"' : ''?> name="mailonline" value="0" />
                            <?php echo AText::_('GENERIC_LBL_NO')?>
                        </label>

                        <label class="radio inline">
                            <input type="radio" <?php echo $this->stateVars->mailonline ? 'checked="checked"' : ''?> name="mailonline" value="1" />
                            <?php echo AText::_('GENERIC_LBL_YES')?>
                        </label>
                    </div>
                </div>
            <?php endif; ?>
				<div class="control-group">
					<div class="controls">
						<label class="checkbox">
							<input type="checkbox" id="usesitedirs" name="usesitedirs" />
							<?php echo AText::_('SETUP_LBL_USESITEDIRS'); ?>
							<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
								  title="<?php echo AText::_('SETUP_LBL_USESITEDIRS_HELP') ?>"></span>
						</label>
					</div>
				</div>
			</div>
		</div>
		<div class="span6">
            <h3><?php echo AText::_('SETUP_HEADER_SERVERCONFIG')?></h3>
            <p class="well well-small"><?php echo AText::_('SETUP_SERVERCONFIG_DESCR') ?></p>
            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" value="1" id="removephpini" name="removephpini" <?php echo $this->removePhpiniOptions['disabled'] ?> <?php echo $this->removePhpiniOptions['checked'] ?> />
                        <?php echo AText::_('SETUP_LBL_SERVERCONFIG_REMOVEPHPINI'); ?>
                        <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                              title="<?php echo AText::_($this->removePhpiniOptions['help']) ?>"></span>
                    </label>
                </div>
            </div>
            <?php if ($this->htaccessSupported):?>
            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" value="1" id="replacehtaccess" name="replacehtaccess" <?php echo $this->replaceHtaccessOptions['disabled'] ?> <?php echo $this->replaceHtaccessOptions['checked'] ?> />
						<?php echo AText::_('SETUP_LBL_SERVERCONFIG_REPLACEHTACCESS'); ?>
                        <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                              title="<?php echo AText::_($this->replaceHtaccessOptions['help']) ?>"></span>
                    </label>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($this->webConfSupported):?>
            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" value="1" id="replacewebconfig" name="replacewebconfig" <?php echo $this->replaceWeconfigOptions['disabled']?> <?php echo $this->replaceWeconfigOptions['checked']?> />
						<?php echo AText::_('SETUP_LBL_SERVERCONFIG_REPLACEWEBCONFIG'); ?>
                        <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                              title="<?php echo AText::_($this->replaceWeconfigOptions['help']) ?>"></span>
                    </label>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($this->htaccessSupported):?>
            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" value="1" id="removehtpasswd" name="removehtpasswd" <?php echo $this->removeHtpasswdOptions['disabled']?> <?php echo $this->removeHtpasswdOptions['checked'] ?> />
						<?php echo AText::_('SETUP_LBL_SERVERCONFIG_REMOVEHTPASSWD'); ?>
                        <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                              title="<?php echo AText::_($this->removeHtpasswdOptions['help']) ?>"></span>
                    </label>
                </div>
            </div>
            <?php endif; ?>
        </div>
	</div>
	<div class="row-fluid">
        <!-- FTP options -->
		<?php if($this->hasFTP): ?>
            <div class="span6">
                <h3>
					<?php echo AText::_('SETUP_HEADER_FTPPARAMS') ?>
                    <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                          title="<?php echo AText::_('SETUP_LABEL_FTPENABLE_HELP') ?>"></span>
                </h3>
                <div class="form-horizontal">

                    <div class="text-center" style="margin-bottom: 20px">
                    <span id="showFtpOptions" class="btn btn-primary" style="display: <?php echo $this->stateVars->ftpenable ? 'none' : 'inline'; ?>">
                        <?php echo AText::_('SETUP_LABEL_FTPENABLE')?>
                    </span>
                        <span id="hideFtpOptions" class="btn btn-primary" style="display: <?php echo $this->stateVars->ftpenable ? 'inline' : 'none'; ?>">
                        <?php echo AText::_('SETUP_LABEL_FTPDISABLE')?>
                    </span>
                    </div>

                    <input type="hidden" id="enableftp" name="enableftp" value="<?php echo $this->stateVars->ftpenable; ?>" />

                    <div id="ftpLayerHolder" style="display: <?php echo $this->stateVars->ftpenable ? 'inline' : 'none'; ?>">
                        <div class="control-group">
                            <label class="control-label" for="ftphost">
								<?php echo AText::_('SETUP_LABEL_FTPHOST'); ?>
                            </label>
                            <div class="controls">
                                <input type="text" id="ftphost" name="ftphost" value="<?php echo $this->stateVars->ftphost ?>" />
                                <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                                      title="<?php echo AText::_('SETUP_LABEL_FTPHOST_HELP') ?>"></span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="ftpport">
								<?php echo AText::_('SETUP_LABEL_FTPPORT'); ?>
                            </label>
                            <div class="controls">
                                <input type="text" id="ftpport" name="ftpport" value="<?php echo empty($this->stateVars->ftpport) ? '21' : $this->stateVars->ftpport ?>" />
                                <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                                      title="<?php echo AText::_('SETUP_LABEL_FTPPORT_HELP') ?>"></span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="ftpuser">
								<?php echo AText::_('SETUP_LABEL_FTPUSER'); ?>
                            </label>
                            <div class="controls">
                                <input type="text" id="ftpuser" name="ftpuser" value="<?php echo $this->stateVars->ftpuser ?>" />
                                <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                                      title="<?php echo AText::_('SETUP_LABEL_FTPUSER_HELP') ?>"></span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="ftppass">
								<?php echo AText::_('SETUP_LABEL_FTPPASS'); ?>
                            </label>
                            <div class="controls">
                                <input type="password" id="ftppass" name="ftppass" value="<?php echo $this->stateVars->ftppass ?>" />
                                <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                                      title="<?php echo AText::_('SETUP_LABEL_FTPPASS_HELP') ?>"></span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="ftpdir">
								<?php echo AText::_('SETUP_LABEL_FTPDIR'); ?>
                            </label>
                            <div class="controls">
                                <div class="input-append">
                                    <input type="text" id="ftpdir" name="ftpdir" value="<?php echo $this->stateVars->ftpdir ?>" />
                                    <button type="button" class="btn add-on" id="ftpbrowser" onclick="openFTPBrowser();">
                                        <span class="icon-folder-open"></span>
										<?php echo AText::_('SESSION_BTN_BROWSE'); ?>
                                    </button>
                                </div>
                                <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                                      title="<?php echo AText::_('SETUP_LABEL_FTPDIR_HELP') ?>"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		<?php endif; ?>
		<?php if (isset($this->stateVars->superusers)): ?>
		<!-- Super Administrator settings -->
		<div class="span6">
			<h3><?php echo AText::_('SETUP_HEADER_SUPERUSERPARAMS') ?></h3>
			<div class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="superuserid">
						<?php echo AText::_('SETUP_LABEL_SUPERUSER'); ?>
					</label>
					<div class="controls">
						<?php echo AngieHelperSelect::superusers(); ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LABEL_SUPERUSER_HELP') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="superuseremail">
						<?php echo AText::_('SETUP_LABEL_SUPERUSEREMAIL'); ?>
					</label>
					<div class="controls">
						<input type="text" id="superuseremail" name="superuseremail" value="" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LABEL_SUPERUSEREMAIL_HELP') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="superuserpassword">
						<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORD'); ?>
					</label>
					<div class="controls">
						<input type="password" id="superuserpassword" name="superuserpassword" value="" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORD_HELP2') ?>"></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="superuserpasswordrepeat">
						<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORDREPEAT'); ?>
					</label>
					<div class="controls">
						<input type="password" id="superuserpasswordrepeat" name="superuserpasswordrepeat" value="" />
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
							  title="<?php echo AText::_('SETUP_LABEL_SUPERUSERPASSWORDREPEAT_HELP') ?>"></span>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
    <div class="row-fluid">
        <!-- Fine-tuning -->
        <div class="span6">
            <h3><?php echo AText::_('SETUP_HEADER_FINETUNING') ?></h3>
            <div class="form-horizontal">
                <div class="control-group">
                    <label class="control-label" for="siteroot">
					    <?php echo AText::_('SETUP_LABEL_SITEROOT'); ?>
                    </label>
                    <div class="controls">
                        <input type="text" disabled="disabled" id="siteroot" value="<?php echo $this->stateVars->site_root_dir ?>" />
                        <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                              title="<?php echo AText::_('SETUP_LABEL_SITEROOT_HELP') ?>"></span>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="tmppath">
					    <?php echo AText::_('SETUP_LABEL_TMPPATH'); ?>
                    </label>
                    <div class="controls">
                        <input type="text" id="tmppath" name="tmppath" value="<?php echo $this->stateVars->tmppath ?>" />
                        <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                              title="<?php echo AText::_('SETUP_LABEL_TMPPATH_HELP') ?>"></span>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="logspath">
					    <?php echo AText::_('SETUP_LABEL_LOGSPATH'); ?>
                    </label>
                    <div class="controls">
                        <input type="text" id="logspath" name="logspath" value="<?php echo $this->stateVars->logspath ?>" />
                        <span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                              title="<?php echo AText::_('SETUP_LABEL_LOGSPATH_HELP') ?>"></span>
                    </div>
                </div>
            </div>
        </div>
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
<?php if (isset($this->stateVars->superusers)): ?>
setupSuperUsers = <?php echo json_encode($this->stateVars->superusers); ?>;
$(document).ready(function(){
	setupSuperUserChange();
	setupDefaultTmpDir = '<?php echo addcslashes($this->stateVars->default_tmp, '\\') ?>';
	setupDefaultLogsDir = '<?php echo addcslashes($this->stateVars->default_log, '\\') ?>';
});
<?php endif; ?>

</script>
