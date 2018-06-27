<?php


// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 *
 */
class RokSprocketModelZooItems extends JModelList
{

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.name',
//                'type', 'a.type',
//                'application', 'a.application_id', 'application_title',
                'alias', 'a.alias',
                'catid', 'catid', 'category_title',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'priority', 'a.priority',
                'hits', 'a.hits',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     * @since    1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $session = JFactory::getSession();

        // Adjust the context to support modal layouts.
        if ($layout = JFactory::getApplication()->input->getCmd('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
        $this->setState('filter.access', $access);

        $authorId = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $authorId);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
        $this->setState('filter.category_id', $categoryId);

        $applicationyId = $this->getUserStateFromRequest($this->context . '.filter.application_id', 'filter_application_id');
        $this->setState('filter.application_id', $applicationyId);

        $type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
        $this->setState('filter.type', $type);

        // List state information.
        parent::populateState('a.name', 'asc');
    }

    /**
     * @param string $id
     * @return string
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.category_id');
//        $id .= ':' . $this->getState('filter.application_id');
//        $id .= ':' . $this->getState('filter.type');
        $id .= ':' . $this->getState('filter.author_id');

        return parent::getStoreId($id);
    }


    /**
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        $app_type = JFactory::getApplication()->input->getString('zoo_application_type');
        $textfield = JFactory::getApplication()->input->getString('zoo_articletext_field');
        $appid = substr($app_type,0,strpos($app_type, '_'));
        $type = substr($app_type,strpos($app_type, '_')+1);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.application_id, a.type, a.name, a.alias' .
                    ', a.state, a.access, a.created, a.created_by, a.priority, a.hits' .
                    ', a.publish_up, a.publish_down'
            )
        );
        $query->from('#__zoo_item AS a');
        if($type){
            $query->where('a.type = "' . (string)$type . '"');
        }

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the categories.
        $query->select('c.name AS category_title, c.id as catid');
        $query->join('LEFT', '#__zoo_category_item AS ci ON ci.item_id = a.id');
        $query->join('LEFT', '#__zoo_category AS c ON c.id = ci.category_id');

        // Join over the applications.
        $query->select('ap.name AS application_title');
        $query->join('LEFT', '#__zoo_application AS ap ON ap.id = a.application_id');
        if($appid){
            $query->where('a.application_id = ' . (int)$appid);
        }

        // Join over the users for the author.
        $query->select('ua.name AS author_name');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('a.access = ' . (int)$access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int)$published);
        }
        elseif ($published === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by a single or group of categories.
        $baselevel = 1;
        $categoryId = $this->getState('filter.category_id');
        if (is_numeric($categoryId)) {
            $query->where('c.id = ' . (int)$categoryId);
        }
        elseif (is_array($categoryId)) {
            JArrayHelper::toInteger($categoryId);
            $categoryId = implode(',', $categoryId);
            $query->where('a.catid IN (' . $categoryId . ')');
        }

        // Filter by application
//        $applicationId = $this->getState('filter.application_id');
//        if (is_numeric($applicationId)) {
//            $query->where('a.application_id = ' . (int)$applicationId);
//        }

        // Filter by type
//        $typeId = $this->getState('filter.type');
//        if (is_string($typeId) && $typeId !='') {
//            $query->where('a.type = "' . (string)$typeId .'"');
//        }

        // Filter on the level.
        if ($level = $this->getState('filter.level')) {
            $query->where('c.level <= ' . ((int)$level + (int)$baselevel - 1));
        }

        // Filter by author
        $authorId = $this->getState('filter.author_id');
        if (is_numeric($authorId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int)$authorId);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            }
            elseif (stripos($search, 'author:') === 0) {
                $search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');
        if ($orderCol == 'category_title') {
            $orderCol = 'c.name ';
        }

        //sqlsrv change
        if ($orderCol == 'access_level')
            $orderCol = 'ag.title';
//        if ($orderCol == 'application_title')
//            $orderCol = 'ap.name';
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        // echo nl2br(str_replace('#__','jos_',$query));
        return $query;
    }


    /**
     * @return mixed
     */
    public function getAuthors()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__zoo_item AS c ON c.created_by = u.id');
        $query->group('u.id, u.name');
        $query->order('u.name');

        // Setup the query
        $db->setQuery($query->__toString());

        // Return the result
        return $db->loadObjectList();
    }

    /**
     * @return mixed
     */
    public function getApplications()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('ap.id AS value, ap.name AS text');
        $query->from('#__zoo_application AS ap');
        $query->order('ap.name');

        // Setup the query
        $db->setQuery($query->__toString());

        // Return the result
        return $db->loadObjectList();
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('i.type AS value, i.type AS text');
        $query->from('#__zoo_item AS i');
        $query->group('i.type');
        $query->order('i.type');

        // Setup the query
        $db->setQuery($query->__toString());

        // Return the result
        return $db->loadObjectList();
    }

    /**
     * @param null $row
     * @param bool $hideUnpublished
     * @return array|mixed
     */
    function getCategories($row = NULL, $hideUnpublished = true)
    {
        if($row==null){
            $row = new stdClass();
        }

        $db = JFactory::getDBO();
        if (isset($row->id)) {
            $idCheck = ' AND id != ' . ( int )$row->id;
        }
        else {
            $idCheck = null;
        }
        if (!isset($row->parent)) {
            $row->parent = 0;
        }
        $query = "SELECT m.* FROM #__zoo_category m WHERE id > 0 {$idCheck}";

        if ($hideUnpublished) {
            $query .= " AND published=1 ";
        }

        $query .= " ORDER BY parent, ordering";
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems) {
            foreach ($mitems as $v) {
                $v->title = $v->name;
                $v->parent_id = $v->parent;
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();
        foreach ($list as $item) {
            $item->treename = JString::str_ireplace('&#160;&#160;-', ' -', $item->treename);
            $item->treename = JString::str_ireplace('&#160;&#160;', ' -', $item->treename);

            if (!$item->published) $item->treename .= ' [**' . JText::_('K2_UNPUBLISHED_CATEGORY') . '**]';

            $mitems[] = JHtml::_('select.option', $item->id, $item->treename);
        }
        return $mitems;
    }


    /**
     * @return mixed
     */
    public function getItems()
    {
        $items = parent::getItems();
        $app = JFactory::getApplication();
        if ($app->isSite()) {
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();

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
