/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

$(document).ready(function(){
	$('.nav-collapse a').addClass('disabled');
	setTimeout(mainStart, 500);
});

function mainStart()
{
	request_data = {
		'view':		'main',
		'task':		'detectversion',
		'format':	'json'
	};
	akeebaAjax.callJSON(request_data, mainGotJoomla, mainGotJoomla);
}

function mainGotJoomla(data, textStatus, errorThrown)
{
	setTimeout(mainGetConfig, 1000);
}

function mainGetConfig()
{
	request_data = {
		'view':		'main',
		'task':		'getconfig',
		'format':	'json'
	};
	akeebaAjax.callJSON(request_data, mainGotConfig, mainGotConfig);
}

function mainGotConfig(data)
{
	setTimeout(mainGetPage, 1000);
}

function mainGetPage()
{
	request_data = {
		'view':		'main',
		'task':		'main',
		'layout':	'init',
		'format':	'raw'
	};
	akeebaAjax.callRaw(request_data, mainGotPage, mainGotPage);
}

function mainGotPage(html)
{
	$('#wrap > .container').html(html);
	$('.nav-collapse a').removeClass('disabled');
}
