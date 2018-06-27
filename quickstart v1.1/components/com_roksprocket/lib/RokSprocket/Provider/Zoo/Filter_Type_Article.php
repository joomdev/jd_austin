<?php
/**
 * @version	$Id: Filter_Type_Article.php 10887 2013-05-30 06:31:57Z btowles $
 * @author	 RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_Filter_Type_Article extends RokSprocket_Provider_Filter_Type_AbstractArticle
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
		return rc__('ROKSPROCKET_FILTER_ZOO_ARTICLE_RENDER', $this->getTypeDescription($this->getChunkType()));
	}

	public function getChunkType()
	{
		return trim((string)$this->xmlnode['name']);
	}

	public function render($name, $type, $values)
	{
		$value = (isset($values[$type]) ? $values[$type] : '');
		return rc__('ROKSPROCKET_FILTER_ZOO_ARTICLE_RENDER', $this->getInput($name, $value));
	}

	protected function getInput($name = RokCommon_Filter_Type::JAVASCRIPT_NAME_VARIABLE, $value = '')
	{

		$id = $this->generateIdFromName($name);


		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		//		  		// Build the script.
		//		  		$script = array();
		//		  		$script[] = '	function jSelectArticle_'.$id.'(id, title, catid, object) {';
		//		  		$script[] = '		document.id("'.$id.'_id").value = id;';
		//		  		$script[] = '		document.id("'.$id.'_name").value = title;';
		//		  		$script[] = '		SqueezeBox.close();';
		//		  		$script[] = '	}';

		// Add the script to the document head.
		//JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));


		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_roksprocket&amp;view=zooitems&amp;layout=model&amp;tmpl=component&amp;function=jSelectUser_Sprocket';

		$db = JFactory::getDBO();
		$db->setQuery('SELECT name' . ' FROM #__zoo_item' . ' WHERE id = ' . (int)$value);
		$title = $db->loadResult();


		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}


		// Initialize some field attributes.
		$attr = $this->xmlnode['class'] ? ' class="' . (string)$this->xmlnode['class'] . '"' : '';
		$attr .= $this->xmlnode['size'] ? ' size="' . (int)$this->xmlnode['size'] . '"' : '';

		if (empty($title)) {
			$title = JText::_('COM_CONTENT_CHANGE_ARTICLE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The active item id field.
		if (0 == (int)$value) {
			$value = '';
		} else {
			$value = (int)$value;
		}

		// Build the script.
		$script = str_replace('%ID%', $id, $this->getJSelectArticle());

		// Add the script to the document head.
		if ($id != '|name|' && !self::$js_loaded){
			RokCommon_Header::addInlineScript($script);
			self::$js_loaded = true;
		}

		$html[] = ' <input type="text" data-other="true" disabled="disabled" value="' . $title . '"' . ' ' . $attr . ' />';
		$html[] = '	  <a class="modal" title="' . JText::_('COM_CONTENT_CHANGE_ARTICLE') . '"' . ' href="' . $link . '&amp;' . JSession::getFormToken() . '=1"' . ' rel="{handler: \'iframe\', size: {x: 900, y: 500}}">';
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
}
