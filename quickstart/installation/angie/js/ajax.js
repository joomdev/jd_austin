/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

function akeebaAjaxConnector(ajaxurl)
{
	this.url = ajaxurl;
	
	this.error_callback = this.dummy_error_handler;
	
	this.call = function(data, successCallback, errorCallback, useCaching, timeout, usejson)
	{
		if (useCaching == null)
		{
			useCaching = false;
		}
		
		if (usejson == null)
		{
			usejson = false;
		}
		
		if (timeout == null)
		{
			if (usejson)
			{
				timeout = 600000;
			}
			else
			{
				timeout = 10000;
			}
			
		}
		
		// When we want to disable caching we have to add a unique URL parameter
		if (!useCaching)
		{
			var now = new Date().getTime() / 1000;
			var s = parseInt(now, 10);
			var microtime = Math.round((now - s) * 1000) / 1000;
			data._dontcachethis = microtime;
		}
		
		if(this.url == null)
		{
			this.url = 'index.php';
		}
		
		var structure =
		{
			type: "POST",
			url: this.url,
			cache: false,
			data: data,
			timeout: 600000,
			success: function(msg)
			{
				// Initialize
				var junk = null;
				var message = "";
				
				if (usejson)
				{
					// Get rid of junk before the data
					var valid_pos = msg.indexOf('###');
					if( valid_pos == -1 ) {
						// Valid data not found in the response
						msg = 'Invalid response: ' + msg;
						if(errorCallback == null)
						{
							if(this.error_callback != null)
							{
								this.error_callback(msg);
							}
						}
						else
						{
							errorCallback(msg);
						}
						return;
					} else if( valid_pos != 0 ) {
						// Data is prefixed with junk
						junk = msg.substr(0, valid_pos);
						message = msg.substr(valid_pos);
					}
					else
					{
						message = msg;
					}
					message = message.substr(3); // Remove triple hash in the beginning

					// Get of rid of junk after the data
					var valid_pos = message.lastIndexOf('###');
					message = message.substr(0, valid_pos); // Remove triple hash in the end
				
					try
					{
						var data = JSON.parse(message);
					}
					catch(err)
					{
						var msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
						if(errorCallback == null)
						{
							if(this.error_callback != null)
							{
								this.error_callback(msg);
							}
						}
						else
						{
							errorCallback(msg);
						}
						return;
					}
				}
				else
				{
					var data = msg;
				}
				
				// Call the callback function
				successCallback(data);
			},
			error: function(Request, textStatus, errorThrown)
			{
				var message = '<strong>HTTP Request Error</strong><br/>HTTP Status: '+Request.status+' ('+Request.statusText+')<br/>';
				message = message + 'Internal status: '+textStatus+'<br/>';
				message = message + 'XHR ReadyState: ' + Request.readyState + '<br/>';
				message = message + 'Raw server response:<br/>'+Request.responseText;

				if(errorCallback == null)
				{
					if(this.error_callback != null)
					{
						this.error_callback(message);
					}
				}
				else
				{
					errorCallback(message);
				}
			}
		}

		$.ajax( structure );
	}
	
	this.callJSON = function(data, successCallback, errorCallback, useCaching, timeout)
	{
		this.call(data, successCallback, errorCallback, useCaching, timeout, true);
	}
	
	this.callRaw = function(data, successCallback, errorCallback, useCaching, timeout)
	{
		this.call(data, successCallback, errorCallback, useCaching, timeout, false);
	}
	
	this.dummy_error_handler = function(error)
	{
		alert("An error has occured\n"+error);
	}

}
