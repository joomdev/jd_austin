<?php
/**
 * @version	$Id: Filter_Type_AbstractArticle.php 10887 2013-05-30 06:31:57Z btowles $
 * @author	 RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider_Filter_Type_AbstractArticle extends RokCommon_Filter_Type
{
	/**
	 * @param string $name
	 * @param null   $value
	 *
	 * @return string
	 */
	protected function getJavascript($name = self::JAVASCRIPT_NAME_VARIABLE, $value = null)
	{
		$script = array();
		$script[] = "(function(){";
		$script[] = $this->getJSelectArticle();
		$script[] = "%.modal%.addEvent('mouseenter', function(){ this.store('href', this.retrieve('href', this.get('href'))); this.set('href', this.retrieve('href') + '&' + RokSprocket.content.getProviderSubmit().keyvalue); });";
		$script[] = "%.modal%.addEvent('click', function(){ jSelectUserID = this; });";
		$script[] = "SqueezeBox.assign(%.modal%, {parse: 'rel'});";
		$script[] = "});";

		$this->javascript = implode("\n", $script);

		return $this->javascript;
	}

	protected function getJSelectArticle(){
		$script = array();
		$script[] = "if (typeof jSelectUser_Sprocket == 'undefined'){";
		$script[] = '	window.jSelectUserID = null;';
		$script[] = '	window.jSelectUser_Sprocket = function(id, title) {';
		$script[] = "		var lnk = document.getElement(jSelectUserID),";
		$script[] = "			parent = lnk.getParent('.chunk'),";
		$script[] = "			item = parent.getElement('[data-key]'),";
		$script[] = "			other = parent.getElement('[data-other=true]'),";
		$script[] = "			value = item.get('value');";
		$crript[] = "";
		$script[] = '		if (value != id) {';
		$script[] = "			item.set('value', id);";
		$script[] = "			other.set('value', title);";
		$script[] = "			item.fireEvent((Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input');";
		$script[] = '		}';
		$script[] = "";
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';
		$script[] = "";
		$script[] = "	window.addEvent('domready', function(){";
		$script[] = "		 document.getElements('.modal').addEvent('mouseenter', function(){ this.store('href', this.retrieve('href', this.get('href'))); this.set('href', this.retrieve('href') + '&' + RokSprocket.content.getProviderSubmit().keyvalue); });";
		$script[] = "		 document.getElements('.modal').addEvent('click', function(){ jSelectUserID = this; });";
		$script[] = "	});";
		$script[] = "};";

		return implode("\n", $script);
	}
}
