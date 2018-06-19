/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/**
 * Initialisation of the page
 */
$(document).ready(function(){
	// Enable tooltips
	$('.help-tooltip').tooltip();
	
	$('#removeInstallation').click(function(e){
		finaliseRemoveInstallation();
		return false;
	});
});

/**
 * Try removing the installation directory using an AJAX request
 * 
 * @returns void
 */
function finaliseRemoveInstallation()
{
	// Set up the request
	var data = {
		'view':			'finalise',
		'task':			'cleanup',
		'format':		'json'
	};
	
	// Start the restoration
	akeebaAjax.callJSON(data, finaliseParseMessage, finaliseError);
}

/**
 * Parse the installation directory cleanup message
 * 
 * @param    mixed  msg  The message received from the server
 * 
 * @returns void
 */
function finaliseParseMessage(msg)
{
	if (msg == true)
	{
		$('#success-dialog').modal({keyboard: false, backdrop: 'static'});
	}
	else
	{
		$('#error-dialog').modal({keyboard: true, backdrop: 'static'});
	}
}

/**
 * Handles error messages during the installation directory cleanup
 * 
 * @param   string  error_message
 * 
 * @returns void
 */
function finaliseError(error_message)
{
	$('#error-dialog').modal({keyboard: true, backdrop: 'static'});
}
