<?php


// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 *
 */
class RokSprocketModelK2Items extends JModelList
{

    /**
     * @param array $config
     */
    public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.published',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
			);
		}

		parent::__construct($config);
	}


    /**
     * @param null $ordering
     * @param null $direction
     */
    protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		// Adjust the context to support modal layouts.
		if ($layout = JFactory::getApplication()->input->getCmd('layout')) {
			$this->context .= '.'.$layout;
		}

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$authorId = $app->getUserStateFromRequest($this->context.'.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		$level = $this->getUserStateFromRequest($this->context.'.filter.featured', 'filter_featured', 0, 'int');
		$this->setState('filter.level', $level);

		$language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState('a.title', 'asc');
	}


    /**
     * @param string $id
     * @return string
     */
    protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.category_id');
		$id	.= ':'.$this->getState('filter.author_id');
		$id	.= ':'.$this->getState('filter.language');

		return parent::getStoreId($id);
	}


    /**
     * @return JDatabaseQuery
     */
    protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid' .
				', a.published, a.trash, a.access, a.created, a.created_by, a.ordering, a.featured, a.language, a.hits' .
				', a.publish_up, a.publish_down'
			)
		);
		$query->from('#__k2_items AS a');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.userName AS editor');
		$query->join('LEFT', '#__k2_users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the categories.
		$query->select('c.name AS category_title');
		$query->join('LEFT', '#__k2_categories AS c ON c.id = a.catid');

		// Join over the users for the author.
		$query->select('ua.userName AS author_name');
		$query->join('LEFT', '#__k2_users AS ua ON ua.userID = a.created_by');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
        if(is_numeric($published) && $published==-2){
            $query->where('(a.published = 0 OR a.published = 1)');
            $query->where('a.trash = 1');
        }
		elseif (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
            $query->where('a.trash = 0');
		}
		elseif ($published === '') {
			$query->where('(a.published = 0 OR a.published = 1)');
            $query->where('a.trash = 0');
		}

        $catid = JFactory::getApplication()->input->getInt('catid');
        if ($catid > 0) {
            require_once (JPATH_SITE.'/components/com_k2/models/itemlist.php');
            $categories = K2ModelItemlist::getCategoryTree($catid);
            $sql = @implode(',', $categories);
            $query->where("a.catid IN ({$sql})");
        }

		$categoryId = $this->getState('filter.category_id');
        if ($categoryId > 0) {
            require_once (JPATH_SITE.'/components/com_k2/models/itemlist.php');
            $categories = K2ModelItemlist::getCategoryTree($categoryId);
            $sql = @implode(',', $categories);
            $query->where("a.catid IN ({$sql})");
        }

		// Filter by author
		$authorId = $this->getState('filter.author_id');
		if (is_numeric($authorId)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by '.$type.(int) $authorId);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->escape(substr($search, 7), true).'%');
				$query->where('(ua.userName LIKE '.$search.' OR ua.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = '.$db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.title');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		if ($orderCol == 'a.ordering' || $orderCol == 'category_title') {
			$orderCol = 'c.name '.$orderDirn.', a.ordering';
		}
		//sqlsrv change
		if($orderCol == 'language')
			$orderCol = 'l.title';
		if($orderCol == 'access_level')
			$orderCol = 'ag.title';
		$query->order($db->escape($orderCol.' '.$orderDirn));

		// echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}

    /**
     * @return mixed
     */
    public function getAuthors() {
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__k2_items AS c ON c.created_by = u.id');
		$query->group('u.id, u.name');
		$query->order('u.name');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
	}

    /**
     * @param null $row
     * @param bool $hideTrashed
     * @param bool $hideUnpublished
     * @return array|mixed
     */
    function getCategories($row = NULL, $hideTrashed=true, $hideUnpublished=true){

            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $catid = JFactory::getApplication()->input->getInt('catid');

            $cat_query = 'SELECT m.* FROM #__k2_categories m WHERE id ='.(int)$catid;
            $db->setQuery($cat_query);
            $row = $db->loadRow();

       		if (isset($row->id)) {
       			$query->where('m.id != '.( int )$row->id);
       		}
       		else {
       			$idCheck = null;
       		}
       		if (!isset($row->parent)) {
       			$row->parent = 0;
       		}
       		$query->select("m.*");
            $query->from("#__k2_categories AS m");
            $query->where("id > 0 {$idCheck}");

       		if ($hideUnpublished){
                $query->where("m.published=1 ");
       		}

       		if ($hideTrashed){
                $query->where("m.trash=0");
       		}

            $query->order("m.parent, m.ordering");
       		$db->setQuery($query);
       		$mitems = $db->loadObjectList();
       		$children = array ();
       		if ($mitems) {
       			foreach ($mitems as $v) {
       					$v->title = $v->name;
       					$v->parent_id = $v->parent;
       				$pt = $v->parent;
       				$list = @$children[$pt]?$children[$pt]: array ();
       				array_push($list, $v);
       				$children[$pt] = $list;
       			}
       		}
       		$list = JHtml::_('menu.treerecurse', 0, '', array (), $children, 9999, 0, 0);
       		$mitems = array ();
       		foreach ($list as $item) {
       			$item->treename = JString::str_ireplace('&#160;&#160;-', ' -', $item->treename);
                   $item->treename = JString::str_ireplace('&#160;&#160;', ' -', $item->treename);

       			if($item->trash) $item->treename .= ' [**'.JText::_('K2_TRASHED_CATEGORY').'**]';
       			if(!$item->published) $item->treename .= ' [**'.JText::_('K2_UNPUBLISHED_CATEGORY').'**]';

       			$mitems[] = JHtml::_('select.option', $item->id, $item->treename);
       		}
       		return $mitems;
       	}

    /**
     * @return mixed
     */
    public function getItems()
    	{
    		$items	= parent::getItems();
    		$app	= JFactory::getApplication();
    		if ($app->isSite()) {
    			$user	= JFactory::getUser();
    			$groups	= $user->getAuthorisedViewLevels();

    			for ($x = 0, $count = count($items); $x < $count; $x++) {
    				//Check the access level. Remove articles the user shouldn't see
    				if (!in_array($items[$x]->access, $groups)) {
    					unset($items[$x]);
    				}
    			}
    		}
    		return $items;
    	}

}
