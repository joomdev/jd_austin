<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var $this AngieViewDatabase */

$document = $this->container->application->getDocument();

$this->loadHelper('select');

$document->addScript('angie/js/json.js');
$document->addScript('angie/js/ajax.js');
$document->addScript('angie/js/database.js');

$url             = 'index.php';
$dbPassMessage   = AText::_('DATABASE_ERR_COMPLEXPASSWORD');
$dbPassMessage   = str_replace(array("\n", "'"), array('\\n', '\\\''), $dbPassMessage);
$dbPrefixMessage = AText::_('DATABASE_ERR_UPPERCASEPREFIX');
$dbPrefixMessage = str_replace(array("\n", "'"), array('\\n', '\\\''), $dbPrefixMessage);
$dbuserEscaped   = addcslashes($this->db->dbuser, '\'\\');
$dbpassEscaped   = addcslashes($this->db->dbpass, '\'\\');


$document->addScriptDeclaration(<<<JS
var akeebaAjax = null;

function angieRestoreDefaultDatabaseOptions()
{
	// Before setting to an empty string we have to a non-empty string because Chrome is dumb!
	$('#dbuser').val('IGNORE ME');
	$('#dbpass').val('IGNORE ME');
	// And now the real value, at last
	$('#dbuser').val('$dbuserEscaped');
	$('#dbpass').val('$dbpassEscaped');
}

$(document).ready(function(){
	akeebaAjax = new akeebaAjaxConnector('$url');

	databasePasswordMessage = '$dbPassMessage';
	databasePrefixMessage = '$dbPrefixMessage';
	
	setTimeout('angieRestoreDefaultDatabaseOptions();', 500);
});
JS
);

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps', array('helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-installers.html#angie-common-database'));
?>

<div class="modal hide fade" id="restoration-dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="restoration-btn-modalclose">&times;</button>
		<h3><?php echo AText::_('DATABASE_HEADER_DBRESTORE') ?></h3>
	</div>
	<div class="modal-body">
		<div id="restoration-progress">
			<div class="progress progress-striped active">
				<div class="bar" id="restoration-progress-bar" style="width: 40%;"></div>
			</div>
			<table width="100%" class="table">
				<tbody>
					<tr>
						<td width="50%"><?php echo AText::_('DATABASE_LBL_RESTORED') ?></td>
						<td>
							<span id="restoration-lbl-restored"></span>
						</td>
					</tr>
					<tr>
						<td><?php echo AText::_('DATABASE_LBL_TOTAL') ?></td>
						<td>
							<span id="restoration-lbl-total"></span>
						</td>
					</tr>
					<tr>
						<td><?php echo AText::_('DATABASE_LBL_ETA') ?></td>
						<td>
							<span id="restoration-lbl-eta"></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="restoration-success">
			<div class="alert alert-success">
				<?php echo AText::_('DATABASE_HEADER_SUCCESS'); ?>
			</div>
			<p>
				<?php echo AText::_('DATABASE_MSG_SUCCESS'); ?>
			</p>
			<button type="button" onclick="databaseBtnSuccessClick(); return false;" class="btn btn-success">
				<span class="icon-white icon-check"></span>
				<?php echo AText::_('DATABASE_BTN_SUCCESS'); ?>
			</button>
		</div>
		<div id="restoration-error">
			<div class="alert alert-error">
				<?php echo AText::_('DATABASE_HEADER_ERROR'); ?>
			</div>
			<div class="well well-small" id="restoration-lbl-error">

			</div>
		</div>
	</div>
</div>

<?php if ($this->number_of_substeps): ?>
	<?php if ($this->substep == 'site.sql'): ?>
<h1><?php echo AText::_('DATABASE_HEADER_MASTER_MAINDB') ?></h1>
	<?php else: ?>
<h1><?php echo AText::sprintf('DATABASE_HEADER_MASTER', $this->substep) ?></h1>
	<?php endif; ?>
<?php endif; ?>

<div class="row-fluid">
	<div class="span6">
		<h3><?php echo AText::_('DATABASE_HEADER_CONNECTION');?></h3>

		<div class="form-horizontal">
			<div class="control-group">
				<label class="control-label" for="dbtype">
					<?php echo AText::_('DATABASE_LBL_TYPE') ?>
				</label>
				<div class="controls">
					<?php echo AngieHelperSelect::dbtype($this->db->dbtype, $this->db->dbtech) ?>
					<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_TYPE_HELP') ?>"></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="dbhost">
					<?php echo AText::_('DATABASE_LBL_HOSTNAME') ?>
				</label>
				<div class="controls">
					<input type="text" id="dbhost" placeholder="<?php echo AText::_('DATABASE_LBL_HOSTNAME') ?>" value="<?php echo $this->db->dbhost ?>" />
					<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_HOSTNAME_HELP') ?>"></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="dbuser">
					<?php echo AText::_('DATABASE_LBL_USERNAME') ?>
				</label>
				<div class="controls">
					<input type="text" id="dbuser" placeholder="<?php echo AText::_('DATABASE_LBL_USERNAME') ?>" value="<?php echo $this->db->dbuser ?>" />
					<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_USERNAME_HELP') ?>"></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="dbpass">
					<?php echo AText::_('DATABASE_LBL_PASSWORD') ?>
				</label>
				<div class="controls">
					<input type="password" id="dbpass" placeholder="<?php echo AText::_('DATABASE_LBL_PASSWORD') ?>" value="<?php echo $this->db->dbpass ?>" />
					<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_PASSWORD_HELP') ?>"></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="dbname">
					<?php echo AText::_('DATABASE_LBL_DBNAME') ?>
				</label>
				<div class="controls">
					<input type="text" id="dbname" placeholder="<?php echo AText::_('DATABASE_LBL_DBNAME') ?>" value="<?php echo $this->db->dbname ?>" />
					<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_DBNAME_HELP') ?>"></span>
				</div>
			</div>

            <?php if ($this->large_tables):?>
            <p class="alert alert-block">
                <?php echo AText::sprintf('DATABASE_WARN_LARGE_COLUMNS', $this->large_tables, floor($this->large_tables) + 1)?>
            </p>
            <?php endif;?>
		</div>
	</div>

	<div id="advancedWrapper" class="span6">
		<h3><?php echo AText::_('DATABASE_HEADER_ADVANCED'); ?></h3>

		<div class="form-horizontal">
			<div class="control-group">
				<label class="control-label" for="existing">
					<?php echo AText::_('DATABASE_LBL_EXISTING') ?>
				</label>
				<div class="controls">
					<input type="hidden" id="existing" value="<?php echo $this->db->existing ?>" />
					<div class="btn-group" id="existing-container">
						<button type="button" class="btn" id="existing-drop"><?php echo AText::_('DATABASE_LBL_EXISTING_DROP') ?></button>
						<button type="button" class="btn" id="existing-backup"><?php echo AText::_('DATABASE_LBL_EXISTING_BACKUP') ?></button>
					</div>
					<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_EXISTING_HELP') ?>"></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="prefix">
					<?php echo AText::_('DATABASE_LBL_PREFIX') ?>
				</label>
				<div class="controls">
					<input type="text" id="prefix" placeholder="<?php echo AText::_('DATABASE_LBL_PREFIX') ?>" value="<?php echo $this->db->prefix ?>" />
					<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_PREFIX_HELP') ?>"></span>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox help-tooltip" for="foreignkey">
						<input type="checkbox" id="foreignkey" <?php echo $this->db->foreignkey ? 'checked="checked"' : '' ?> />
						<?php echo AText::_('DATABASE_LBL_FOREIGNKEY') ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_FOREIGNKEY_HELP') ?>"></span>
					</label>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox help-tooltip" for="noautovalue">
						<input type="checkbox" id="noautovalue" <?php echo $this->db->noautovalue ? 'checked="checked"' : '' ?> />
						<?php echo AText::_('DATABASE_LBL_NOAUTOVALUE') ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_NOAUTOVALUE_HELP') ?>"></span>
					</label>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox help-tooltip" for="replace">
						<input type="checkbox" id="replace" <?php echo $this->db->replace ? 'checked="checked"' : '' ?> />
						<?php echo AText::_('DATABASE_LBL_REPLACE') ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_REPLACE_HELP') ?>"></span>
					</label>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox help-tooltip" for="utf8db">
						<input type="checkbox" id="utf8db" <?php echo $this->db->utf8db ? 'checked="checked"' : '' ?> />
						<?php echo AText::_('DATABASE_LBL_FORCEUTF8DB') ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_FORCEUTF8DB_HELP') ?>"></span>
					</label>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox help-tooltip" for="utf8tables">
						<input type="checkbox" id="utf8tables" <?php echo $this->db->utf8db ? 'checked="checked"' : '' ?> />
						<?php echo AText::_('DATABASE_LBL_FORCEUTF8TABLES') ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_FORCEUTF8TABLES_HELP') ?>"></span>
					</label>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox help-tooltip" for="utf8mb4">
						<input type="checkbox" id="utf8mb4" <?php echo $this->db->utf8mb4 ? 'checked="checked"' : '' ?> />
						<?php echo AText::_('DATABASE_LBL_UTF8MB4DETECT') ?>
						<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
						  title="<?php echo AText::_('DATABASE_LBL_UTF8MB4DETECT_HELP') ?>"></span>
					</label>
				</div>
			</div>

            <h3><?php echo AText::_('DATABASE_HEADER_FINETUNING') ?></h3>

            <div class="alert">
                <?php echo AText::_('DATABASE_MSG_FINETUNING'); ?>
            </div>

            <div class="control-group">
                <label class="control-label" for="maxexectime">
                    <?php echo AText::_('DATABASE_LBL_MAXEXECTIME') ?>
                </label>
                <div class="controls">
                    <input class="input-mini" type="text" id="maxexectime" placeholder="<?php echo AText::_('DATABASE_LBL_MAXEXECTIME') ?>" value="<?php echo $this->db->maxexectime ?>" />
			<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                  title="<?php echo AText::_('DATABASE_LBL_MAXEXECTIME_HELP') ?>"></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="throttle">
                    <?php echo AText::_('DATABASE_LBL_THROTTLEMSEC') ?>
                </label>
                <div class="controls">
                    <input class="input-mini" type="text" id="maxexectime" placeholder="<?php echo AText::_('DATABASE_LBL_THROTTLEMSEC') ?>" value="<?php echo $this->db->throttle ?>" />
			<span class="help-tooltip icon-question-sign" data-toggle="tooltip" data-html="true" data-placement="top"
                  title="<?php echo AText::_('DATABASE_LBL_THROTTLEMSEC_HELP') ?>"></span>
                </div>
            </div>
		</div>
	</div>
</div>
