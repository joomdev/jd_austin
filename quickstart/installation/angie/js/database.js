/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

var databaseKey = null;
var databaseThrottle = 100;
var databasePasswordMessage = '';
var databasePrefixMessage = '';

/**
 * Initialisation of the page
 */
$(document).ready(function(){
	// Enable tooltips
	$('.help-tooltip').tooltip();

	// Existing tables switch handler
	var existing = $('#existing').val();
	$('#existing-' + existing).button('toggle');
	if(existing == 'drop') {
		$('#existing-drop').addClass('btn-danger');
	} else {
		$('#existing-backup').addClass('btn-success');
	}

	$('#existing-drop').click(function(e){
		$('#existing').val('drop');
		$('#existing-backup').button('toggle');
		$('#existing-drop').button('toggle');
		$('#existing-drop').addClass('btn-danger');
		$('#existing-backup').removeClass('btn-success');
	});
	$('#existing-backup').click(function(e){
		$('#existing').val('backup');
		$('#existing-backup').button('toggle');
		$('#existing-drop').button('toggle');
		$('#existing-backup').addClass('btn-success');
		$('#existing-drop').removeClass('btn-danger');
	});

});

/**
 * Begins the database restoration using the filled-in form data
 */
function databaseRunRestoration(key)
{
	// Store the database key (it's used later to step through the restoration)
	databaseKey = key;

	// Prime the request data
	var data = {
		'view':			'dbrestore',
		'task':			'start',
		'format':		'json',
		'key':			databaseKey,
		'dbinfo':		{}
	};

	// Get the form data and add them to the dbinfo request array
	data.dbinfo.dbtype		= $('#dbtype').val();
	data.dbinfo.dbhost		= $('#dbhost').val();
	data.dbinfo.dbuser		= $('#dbuser').val();
	data.dbinfo.dbpass		= $('#dbpass').val();
	data.dbinfo.dbname		= $('#dbname').val();
	data.dbinfo.existing	= $('#existing').val();
	data.dbinfo.prefix		= $('#prefix').val();
	data.dbinfo.foreignkey	= +$('#foreignkey').is(':checked');
	data.dbinfo.noautovalue	= +$('#noautovalue').is(':checked');
	data.dbinfo.replace		= +$('#replace').is(':checked');
	data.dbinfo.utf8db		= +$('#utf8db').is(':checked');
	data.dbinfo.utf8tables	= +$('#utf8tables').is(':checked');
	data.dbinfo.utf8mb4	    = +$('#utf8mb4').is(':checked');
	data.dbinfo.maxexectime	= $('#maxexectime').val();
	data.dbinfo.throttle	= $('#throttle').val();

	databaseThrottle = data.dbinfo.throttle;
	if (databaseThrottle <= 100)
	{
		databaseThrottle = 100;
	}
	else if (databaseThrottle >= 60000)
	{
		databaseThrottle = 60000;
	}

	// Check whether the password contains non-ASCII characters and show a warning
	if (databasePrefixMessage.length && (/[A-Z]{1,}/.test(data.dbinfo.prefix) != false))
	{
		if (!window.confirm(databasePrefixMessage))
		{
			return;
		}
	}

	// Check whether the password contains non-ASCII characters and show a warning
	if (databasePasswordMessage.length && (/^[a-zA-Z0-9- ]*$/.test(data.dbinfo.dbpass) == false))
	{
		if (!window.confirm(databasePasswordMessage))
		{
			return;
		}
	}

	// Set up the modal dialog
	$('#restoration-btn-modalclose').hide(0);
	$('#restoration-dialog .modal-body > div').hide(0);
	$('#restoration-progress-bar').css('width', '0%');
	$('#restoration-lbl-restored').text('');
	$('#restoration-lbl-total').text('');
	$('#restoration-lbl-eta').text('');
	$('#restoration-progress').show(0);

	// Open the restoration's modal dialog
	$('#restoration-dialog').modal({keyboard: false, backdrop: 'static'});

	// Start the restoration
	akeebaAjax.callJSON(data, databaseParseRestoration, databaseErrorRestoration);
}

/**
 * Parses the restoration result message, updates the restoration progress bar
 * and steps through the restoration as necessary.
 */
function databaseParseRestoration(msg)
{
	if (msg.error != '')
	{
		// An error occurred
		databaseErrorRestoration(msg.error);
		return;
	}
	else if (msg.done == 1)
	{
		// The restoration is complete
		$('#restoration-dialog .modal-body > div').hide(0);
		$('#restoration-success').show(0);
		return;
	}
	else
	{
		// Step through the restoration
		$('#restoration-dialog .modal-body > div').hide(0);
		$('#restoration-progress').show(0);
		$('#restoration-progress-bar').css('width', msg.percent + '%');
		$('#restoration-lbl-restored').text(msg.restored);
		$('#restoration-lbl-total').text(msg.total);
		$('#restoration-lbl-eta').text(msg.eta);

		setTimeout(databaseStepRestoration, databaseThrottle);
	}
}

/**
 * Runs one more restoration step via AJAX
 */
function databaseStepRestoration()
{
	var data = {
		'view':			'dbrestore',
		'task':			'step',
		'format':		'json',
		'key':			databaseKey
	};

	akeebaAjax.callJSON(data, databaseParseRestoration, databaseErrorRestoration);
}

/**
 * Handles a restoration error message
 */
function databaseErrorRestoration(error_message)
{
	$('#restoration-btn-modalclose').show(0);
	$('#restoration-dialog .modal-body > div').hide(0);
	$('#restoration-lbl-error').html(error_message);
	$('#restoration-error').show(0);
}

function databaseBtnSuccessClick(e)
{
	window.location = $('.navbar-inner .btn-group a.btn-warning').attr('href');
}
