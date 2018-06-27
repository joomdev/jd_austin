<?php
/**
 * @version   $Id: AbstractJoomlaPlatformFilter.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider_AbstractJoomlaPlatformFilter implements RokSprocket_Provider_Filter_IProcessor
{
    /**
     * @var SimpleXMLElement
     */
    protected $xml;

    /**
     * @var JDatabaseQuery
     */
    protected $query;

    /**
     * @var JDatabase
     */
    protected $db;

    /**
     * @var array
     */
    protected $article_where = array();

    /**
     * @var array
     */
    protected $filter_where = array();

    /**
     * @var array
     */
    protected $access_where = array();

    /**
     * @var array
     */
    protected $displayed_where = array();

    /**
     * @var array
     */
    protected $sort_order = array();

    /** @var bool */
    protected $showUnpublished = false;

    /**
     * @var int
     */
    protected $moduleId;

    /**
     * @var array
     */
    protected $displayedIds = array();

    /**
     * @var bool
     */
    protected $manualSort = false;

    /**
     * @var string
     */
    protected $manualAppend = 'after';


    /**
     */
    public function __construct()
    {
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
        $this->setBaseQuery();
    }


    /**
     * @abstract
     * @return mixed
     */
    abstract protected function setBaseQuery();

    /**
     * @abstract
     * @return mixed
     */
    abstract protected function setAccessWhere();

    /**
     * @abstract
     * @return mixed
     */
    abstract protected function setDisplayedWhere();


    /**
     * @param array $filters
     * @param array $sort_filters
     */
    public function process(array $filters, array $sort_filters = array(), $showUnpublished = false)
    {
        $this->showUnpublished = $showUnpublished;

        $this->setAccessWhere();

        $this->setDisplayedWhere();

        foreach ($filters as $filtertype => $fitlerdata) {
            if (!method_exists($this, $filtertype)) {
                //throw new RokSprocket_Exception(rc__('Unknown Filter %s', $filtertype));
            } else {
                $this->$filtertype($fitlerdata);
            }
        }

        foreach ($sort_filters as $sort_filtertype => $sort_fitlerdata) {
            $sort_function = 'sort_' . $sort_filtertype;
            if (!method_exists($this, 'sort_' . $sort_filtertype)) {
                //throw new RokSprocket_Exception(rc__('Unknown Filter %s', $filtertype));
            } else {
                $this->$sort_function($sort_fitlerdata);
            }
        }


        if (!empty($this->access_where)) {
            $access_where = sprintf('(%s)', implode(' AND ', $this->access_where));
            $article_where_parts[] = $access_where;
            $filter_where_parts[] = $access_where;
        }

        if (!empty($this->displayed_where)) {
            $displayed_where = sprintf('(%s)', implode(' AND ', $this->displayed_where));
            $article_where_parts[] = $displayed_where;
            $filter_where_parts[] = $displayed_where;
        }

        if (!empty($this->article_where)) {
            $article_where_parts[] = implode(' AND ', $this->article_where);
            $this->query->where(sprintf('(%s)', implode(' AND ', $article_where_parts)), 'OR');
        }
        if (!empty($this->filter_where)) {
            $filter_where_parts[] = implode(' AND ', $this->filter_where);
            $this->query->where(sprintf('(%s)', implode(' AND ', $filter_where_parts)), 'OR');
        }

        if (empty($this->article_where) && empty($this->filter_where)) {
            $this->query->where('0=1');
        }

        if ($this->manualSort) {
            $this->query->join('LEFT OUTER', sprintf('(select rsi.provider_id, rsi.order from #__roksprocket_items as rsi where module_id = %d) rsi on a.id = rsi.provider_id', $this->moduleId));
            array_unshift($this->sort_order, 'rsi.order');
            if ($this->manualAppend == 'after') {
                array_unshift($this->sort_order, 'IF(ISNULL(rsi.order),1,0)');
            }
        }
        foreach ($this->sort_order as $sort) {
            $this->query->order($sort);
        }
    }

    /**
     * @return \JDatabaseQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param $field
     * @param $data
     */
    protected function dateMatch($field, $data)
    {
        $wheres = array();

        foreach ($data as $options) {
            foreach ($options as $type => $match) {
                if (!empty($match)) {
                    switch ($type) {
                        case 'withinlast':
                            switch ($match['range']) {
                                case 'weeks':
                                    $range = 'WEEK';
                                    break;
                                case 'months':
                                    $range = 'MONTH';
                                    break;
                                case 'years':
                                    $range = 'YEAR';
                                    break;
                                case 'days':
                                default:
                                    $range = 'DAY';
                                    break;
                            }
                            $wheres[] = 'DATEDIFF(NOW(),' . $field . ') < DATEDIFF(NOW(), DATE_SUB(NOW(),INTERVAL ' . (int)$match['value'] . ' ' . $range . '))';
                            break;
                        case 'exactly':
                            $wheres[] = 'DATEDIFF(' . $this->db->quote($this->db->escape($match, true)) . ',' . $field . ') = 0';
                            break;
                        case 'before':
                            $wheres[] = 'DATEDIFF(' . $this->db->quote($this->db->escape($match, true)) . ',' . $field . ') > 0';
                            break;
                        case 'after':
                            $wheres[] = 'DATEDIFF(' . $this->db->quote($this->db->escape($match, true)) . ',' . $field . ') < 0';
                            break;
                        case 'today':
                            $wheres[] = 'DATE(' . $field . ') = DATE(NOW())';
                            break;
                        case 'yesterday':
                            $wheres[] = 'DATE(' . $field . ') = DATE_SUB(CURDATE(), INTERVAL -1 DAY)';
                            break;
                        case 'thisweek':
                            $wheres[] = '(YEAR(' . $field . ') = YEAR(CURDATE()) AND WEEK(' . $field . ') = WEEK(CURDATE()))';
                            break;
                        case 'thismonth':
                            $wheres[] = '(YEAR(' . $field . ') = YEAR(CURDATE()) AND MONTH(' . $field . ') = MONTH(CURDATE()))';
                            break;
                        case 'thisyear':
                            $wheres[] = 'YEAR(' . $field . ') = YEAR(CURDATE())';
                            break;

                    }
                }
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }


    /**
     * @param $field
     * @param $data
     */
    protected function numberMatch($field, $data)
    {
        $wheres = array();

        foreach ($data as $options) {
            foreach ($options as $type => $match) {
                if (!empty($match)) {
                    switch ($type) {
                        case 'equals':
                            $wheres[] = $field . '=' . (float)$this->db->escape($match, true);
                            break;
                        case 'greaterthan':
                            $wheres[] = $field . ' > ' . (float)$this->db->escape($match, true);
                            break;
                        case 'lessthan':
                            $wheres[] = $field . ' < ' . (float)$this->db->escape($match, true);
                            break;
                        case 'isnot':
                            $wheres[] = $field . ' != ' . (float)$this->db->escape($match, true);
                            break;

                    }
                }
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }

    /**
     * @param $field
     * @param $data
     */
    protected function textMatch($field, $data)
    {
        $wheres = array();

        foreach ($data as $options) {
            foreach ($options as $type => $match) {
                $match = trim($match);
                if (!empty($match)) {
                    switch ($type) {
                        case 'matches':
                            break;
                        case 'contains':
                            $wheres[] = $field . ' like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
                            break;
                        case 'beginswith':
                            $wheres[] = $field . ' like ' . $this->db->quote($this->db->escape($match, true) . '%');
                            break;
                        case 'endswith':
                            $wheres[] = $field . ' like ' . $this->db->quote('%' . $this->db->escape($match, true));
                            break;
                        case 'is':
                            $wheres[] = $field . ' = ' . $this->db->quote($this->db->escape($match, true));
                            break;

                    }
                }
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }

    /**
     * @param $field
     * @param $data
     */
    protected function booleanMatch($field, $data)
    {
        $options = array();
        foreach ($data as $entry) {
            $options[$entry] = ($entry == 'yes') ? 1 : 0;
        }
        $this->filter_where[] = $field . ' IN (' . implode(',', $options) . ')';
    }

    /**
     * @param $field
     * @param $data
     */
    protected function stringMatch($field, $data)
    {
        $wheres = array();
        foreach ($data as $match) {
            $match = trim($match);
            if (!empty($match)) {
                $wheres[] = $field . ' LIKE ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }

    /**
     * @param $field
     * @param $data
     */
    protected function normalSortBy($field, $data)
    {
        $sort = $field;
        $sort .= ($data[0] == 'descending') ? ' DESC' : ' ASC';
        $this->sort_order[] = $sort;
    }

    /**
     * @param boolean $manualSort
     */
    public function setManualSort($manualSort)
    {
        $this->manualSort = $manualSort;
    }

    /**
     * @param int $moduleId
     */
    public function setModuleId($moduleId)
    {
        $this->moduleId = $moduleId;
    }

    /**
     * @param $displayedIds
     */
    public function setDisplayedIds($displayedIds)
    {
        $this->displayedIds = $displayedIds;
    }

    /**
     * @param string $manualAppend
     */
    public function setManualAppend($manualAppend)
    {
        $this->manualAppend = $manualAppend;
    }
}
