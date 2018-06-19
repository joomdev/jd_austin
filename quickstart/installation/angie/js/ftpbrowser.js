/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

function ftpBrowserClass(baseurl)
{
	this.url = baseurl;
	
	this.navTo = function(directory)
	{
		var url = this.url + '&directory=' + encodeURIComponent(directory);
		window.location = url;
	}
	
	this.useThis = function(path)
	{
		window.parent.useFTPDirectory(path);
	}
}
