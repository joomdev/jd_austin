/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

function offsitedirsRunRestoration(key)
{
	// Prime the request data
	var data = {
		'view':		'offsitedirs',
		'task':		'move',
		'format':	'json',
		'key':		key,
		'info':		{}
	};
	
	// Get the form data and add them to the dbinfo request array
	data.info.target	= $('#target_folder').val();

	// Set up the modal dialog
	$('#restoration-btn-modalclose').hide(0);
	$('#restoration-dialog .modal-body > div').hide(0);
	$('#restoration-progress-bar').css('width', '0%');
	$('#restoration-lbl-restored').text('');
	$('#restoration-lbl-total').text('');
	$('#restoration-progress').show(0);
	
	// Open the restoration's modal dialog
	$('#restoration-dialog').modal({keyboard: false, backdrop: 'static'});

    // Start the restoration
    setTimeout(function(){akeebaAjax.callJSON(data, databaseParseRestoration, databaseErrorRestoration);}, 1000);
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
        $('#restoration-progress-bar').css('width', '100%');

        setTimeout(function(){
            $('#restoration-dialog .modal-body > div').hide(0);
            $('#restoration-progress-bar').css('width', '0');
            $('#restoration-success').show(0);
        }, 500);

        return;
    }
}

function databaseBtnSuccessClick(e)
{
	window.location = $('.navbar-inner .btn-group a.btn-warning').attr('href');
}
