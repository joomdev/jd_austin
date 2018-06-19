/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

var setupSuperUsers = {};
var setupDefaultTmpDir = '';
var setupDefaultLogsDir = '';

/**
 * Initialisation of the page
 */
$(document).ready(function(){
	// Enable tooltips
	$('.help-tooltip').tooltip();

	$('div.navbar div.btn-group a:last').click(function(e){
		document.forms.setupForm.submit();
		return false;
	});

	$('#usesitedirs').click(function(e){
		setupOverrideDirectories();
	});

    $('#showFtpOptions').click(function(){
        $(this).hide();
        $('#hideFtpOptions').show();
        $('#ftpLayerHolder').show();
        $('#enableftp').val(1);
    });

    $('#hideFtpOptions').click(function(){
        $(this).hide();
        $('#showFtpOptions').show();
        $('#ftpLayerHolder').hide();
        $('#enableftp').val(0);
    });
});


function setupSuperUserChange(e)
{
	var saID = $('#superuserid').val();
	var params = {};

	$.each(setupSuperUsers, function(idx, sa){
		if(sa.id == saID)
		{
			params = sa;
		}
	});

	$('#superuseremail').val('');
	$('#superuserpassword').val('');
	$('#superuserpasswordrepeat').val('');
	$('#superuseremail').val(params.email);
}

function openFTPBrowser()
{
	var hostname = $('#ftphost').val();
	var port = $('#ftpport').val();
	var username = $('#ftpuser').val();
	var password = $('#ftppass').val();
	var directory = $('#fptdir').val();

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
	$('#ftpdir').val(path);
	$('#browseModal').modal('hide');
}

function setupOverrideDirectories()
{
	$('#tmppath').val(setupDefaultTmpDir);
	$('#logspath').val(setupDefaultLogsDir);
}
