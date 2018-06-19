<?php
/**
 * @version	$Id: Filter_Type_Article.php 22593 2014-08-08 14:46:31Z jakub $
 * @author	 RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Cfs_Filter_Type_Article extends RokSprocket_Provider_Filter_Type_AbstractArticle
{
	/**
	 * @var string
	 */
	protected $type = 'article';
	protected static $js_loaded = false;

	public function getChunkRender()
	{
		return $this->getInput();
	}

	public function getChunkSelectionRender()
	{
		return rc__('ROKSPROCKET_FILTER_JOOMLA_ARTICLE_RENDER', $this->getTypeDescription($this->getChunkType()));
	}

	public function getChunkType()
	{
		return trim((string)$this->xmlnode['name']);
	}

	public function render($name, $type, $values)
	{
		$value = (isset($values[$type]) ? $values[$type] : '');
		return rc__('ROKSPROCKET_FILTER_JOOMLA_ARTICLE_RENDER', $this->getInput($name, $value));
	}

	protected function getInput($name = RokCommon_Filter_Type::JAVASCRIPT_NAME_VARIABLE, $value = '')
	{
        global $wpdb;

		$id = $this->generateIdFromName($name);

		// Setup variables for display.
		$html = array();
        $nonce = wp_create_nonce('roksprocket-ajax-nonce');
        $link = site_url().'/wp-admin/admin-ajax.php?action=roksprocket_postlist&nonce='.$nonce.'&provider=cfs&TB_iframe=true&height=425&width=555&modal=false';

        $title = $wpdb->get_var('SELECT post_title FROM '.$wpdb->posts . ' WHERE ID = ' . (int)$value);

		// Initialize some field attributes.
		$attr = $this->xmlnode['class'] ? ' class="' . (string)$this->xmlnode['class'] . '"' : '';
		$attr .= $this->xmlnode['size'] ? ' size="' . (int)$this->xmlnode['size'] . '"' : '';

		if (empty($title)) {
			$title = rc__('COM_CONTENT_CHANGE_ARTICLE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The active article id field.
		if (0 == (int)$value) {
			$value = '';
		} else {
			$value = (int)$value;
		}

//		// Build the script.
//		$script = str_replace('%ID%', $id, $this->getJSelectArticle());
//
//		// Add the script to the document head.
//		if ($id != '|name|' && !self::$js_loaded){
//			RokCommon_Header::addInlineScript($script);
//			self::$js_loaded = true;
//		}

		$html[] = ' <input type="text" data-other="true" disabled="disabled" value="' . $title . '"' . ' ' . $attr . ' />';
        $html[] = '	  <a class="thickbox" title="' . rc__('COM_CONTENT_CHANGE_ARTICLE') . '"' . ' href="' . $link . '">';
		$html[] = '			<i class="icon tool article"></i>';
		$html[] = '	  </a>';

		// Create the real field, hidden, that stored the user id.
		$html[] = '<input type="hidden" name="' . $name . '" id="' . $id . '" data-name="' . $name . '" data-key="' . $this->getChunkType() . '" value="' . (int)$value . '" />';

		return implode("\n", $html);
	}

	protected function generateIdFromName($name)
	{
		$id = $name;
		$id = str_replace('][', '', $id);
		$id = str_replace('[', '', $id);
		$id = str_replace(']', '', $id);
		return $id;
	}

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 *
	 * @since	11.1
	 */
	protected function getGroups()
	{
		return null;
	}

	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  mixed  Array of users to exclude or null to to not exclude them
	 *
	 * @since	11.1
	 */
	protected function getExcluded()
	{
		return null;
	}

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
		$script[] = "%.thickbox%.addEvent('mouseenter', function(){ this.store('href', this.retrieve('href', this.get('href'))); this.set('href', this.retrieve('href') + '&' + RokSprocket.content.getProviderSubmit().keyvalue); });";
		$script[] = "%.thickbox%.addEvent('click', function(){ jSelectUserID = this; });";
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
		$script[] = '		tb_remove();';
		$script[] = '	}';
		$script[] = "";
		$script[] = "	window.addEvent('domready', function(){";
		$script[] = "		 document.getElements('.thickbox').addEvent('mouseenter', function(){ this.store('href', this.retrieve('href', this.get('href'))); this.set('href', this.retrieve('href') + '&' + RokSprocket.content.getProviderSubmit().keyvalue); });";
		$script[] = "		 document.getElements('.thickbox').addEvent('click', function(){ jSelectUserID = this; });";
		$script[] = "	});";
		$script[] = "};";

		return implode("\n", $script);
	}
}
