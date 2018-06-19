<?php
/**
 * @version   $Id: AbstractWordpressPlatformFilter.php 21664 2014-06-19 19:53:13Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider_AbstractWordpressPlatformFilter implements RokSprocket_Provider_Filter_IProcessor
{
	/**
	 * @var SimpleXMLElement
	 */
	protected $xml;

	/**
	 * @var array
	 */
	protected $base_query = array();

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
	protected $article_where = array();

	/**
	 * @var array
	 */
	protected $filter_where = array();

	/**
	 * @var array
	 */
	protected $base_where = array();

	/**
	 * @var array
	 */
	protected $query_where = array();

	/**
	 * @var array
	 */
	protected $sort_order = array();

	/**
	 * @var array
	 */
	protected $group_by = array();

	/**
	 * @var array
	 */
	protected $query_order = array();

	/** @var bool */
	protected $showUnpublished = false;

	/**
	 * @var int
	 */
	protected $widgetId;

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
	 * @param bool  $showUnpublished
	 *
	 * @return void
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

		if (!empty($this->displayed_where)) {
            $displayed_where          = sprintf('(%s)', implode(' AND ', $this->displayed_where));
			$article_where_parts[] = $displayed_where;
			$filter_where_parts[]  = $displayed_where;
		}

        if (!empty($this->access_where)) {
            $access_where          = sprintf('(%s)', implode(' AND ', $this->access_where));
            $article_where_parts[] = $access_where;
            $filter_where_parts[]  = $access_where;
        }

		if (!empty($this->base_where)) {
			$base_where = sprintf('(%s)', implode(' AND ', $this->base_where));
		}

		if (!empty($this->article_where)) {
			$article_where_parts[] = implode(' AND ', $this->article_where);
			if (!empty($base_where)) $article_where_parts[] = $base_where;
			$this->query_where[] = sprintf('(%s)', implode(' AND ', $article_where_parts));
		}
		if (!empty($this->filter_where)) {
			$filter_where_parts[] = implode(' AND ', $this->filter_where);
			if (!empty($base_where)) $filter_where_parts[] = $base_where;
			$this->query_where[] = sprintf('(%s)', implode(' AND ', $filter_where_parts));
		}

		if (empty($this->article_where) && empty($this->filter_where)) {
			$this->query_where[] = '0=1';
		}
	}

	/**
	 * @return array
	 */
	public function getQuery()
	{
		global $wpdb;

		if ($this->manualSort)
		{
			$this->base_query .= sprintf(' LEFT OUTER JOIN (select rsi.provider_id, rsi.order from ' . $wpdb->prefix . 'roksprocket_items as rsi where widget_id = %d) rsi on p.id = rsi.provider_id', $this->widgetId);
			array_unshift($this->sort_order, 'rsi.order');
			if ($this->manualAppend == 'after'){
				array_unshift($this->sort_order,'IF(ISNULL(rsi.order),1,0)');
			}
		}

		if (count($this->query_where)) {
			$this->base_query .= ' WHERE ' . sprintf('(%s)', implode(' OR ', $this->query_where));
		}
		if (count($this->group_by)) {
			$this->base_query .= ' GROUP BY ' . implode(', ', $this->group_by);
		}

		if (count($this->sort_order)) {
			$this->base_query .= ' ORDER BY ' . implode(', ', $this->sort_order);
		}
		return $this->base_query;
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
							$wheres[] = 'DATEDIFF("' . esc_sql($match) . '",' . $field . ') = 0';
							break;
						case 'before':
							$wheres[] = 'DATEDIFF("' . esc_sql($match) . '",' . $field . ') > 0';
							break;
						case 'after':
							$wheres[] = 'DATEDIFF("' . esc_sql($match) . '",' . $field . ') < 0';
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
							$wheres[] = $field . '=' . (float)esc_sql($match);
							break;
						case 'greaterthan':
							$wheres[] = $field . ' > ' . (float)esc_sql($match);
							break;
						case 'lessthan':
							$wheres[] = $field . ' < ' . (float)esc_sql($match);
							break;
						case 'isnot':
							$wheres[] = $field . ' != ' . (float)esc_sql($match);
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
							$wheres[] = $field . ' like "%' . esc_sql($match) . '%"';
							break;
						case 'beginswith':
							$wheres[] = $field . ' like "' . esc_sql($match) . '%"';
							break;
						case 'endswith':
							$wheres[] = $field . ' like "%' . esc_sql($match) . '%"';
							break;
						case 'is':
							$wheres[] = $field . ' = "' . esc_sql($match) . '%"';
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
				$wheres[] = $field . ' LIKE "%' . esc_sql($match) . '%"';
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
	 * @param int $widgetId
	 */
	public function setWidgetId($widgetId)
	{
		$this->widgetId = $widgetId;
	}

    public function setDisplayedIds($displayedIds)
    	{
    		$this->displayedIds = $displayedIds;
    	}


	/**
	 * @param boolean $manualSort
	 */
	public function setManualSort($manualSort)
	{
		$this->manualSort = $manualSort;
	}


	/**
	 * @param string $manualAppend
	 */
	public function setManualAppend($manualAppend)
	{
		$this->manualAppend = $manualAppend;
	}

}
