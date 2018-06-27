<?php
/**
 * @version   $Id: Filter.php 13721 2013-09-24 16:46:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_Filter extends RokSprocket_Provider_AbstractJoomlaPlatformFilter
{
	/**
	 *
	 */
	protected function setBaseQuery()
	{
		//$this->query->select('a.id, a.application_id, a.type, a.name as title, a.alias' . ', a.state as published, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.elements, a.hits' . ', a.publish_up, a.publish_down, a.priority, a.params');
		$this->query->select('a.id');
		$this->query->from('#__zoo_item as a');

		//$this->query->select('CONCAT(",", s.value) AS articletext');
		$this->query->join('LEFT', '#__zoo_search_index AS s ON s.item_id = a.id');

		//$this->query->select('CONCAT_WS(",", t.name) AS tags');
		$this->query->join('LEFT', '#__zoo_tag AS t ON t.item_id = a.id');

		//$this->query->select('COUNT(co.id) AS comment_count');
		$this->query->join('LEFT', '#__zoo_comment AS co ON co.item_id = a.id');

		//$this->query->select('c.name AS category_title, GROUP_CONCAT(DISTINCT ci.category_id) AS cid');
		$this->query->join('LEFT', '#__zoo_category_item AS ci ON ci.item_id = a.id');
		$this->query->join('LEFT', '#__zoo_category AS c ON c.id = ci.category_id');
		//$this->query->select('CASE WHEN (EXISTS (SELECT true FROM #__zoo_category_item WHERE item_id = a.id AND category_id = 0)) THEN 1 ELSE 0 END AS featured');

		//$this->query->select('ua.name AS author_name');
		$this->query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		//$this->query->select('um.name AS last_modified_by');
		$this->query->join('LEFT', '#__users AS um ON um.id = a.modified_by');

		$this->query->select('ROUND(AVG(v.value), 0) AS rating');
		$this->query->join('LEFT', '#__zoo_rating AS v ON a.id = v.item_id');

		//$this->query->select('vl.title AS access_title');
		$this->query->join('LEFT', '#__viewlevels AS vl ON a.access = vl.id');
		$this->query->group('a.id');
	}

	/**
	 * @param $data
	 */
	protected function zoo_application_type($data)
	{
        if(isset($data[0])){
            $data                 = $data[0];
            $appid                = substr($data, 0, strpos($data, '_'));
            $type                 = substr($data, strpos($data, '_') + 1);
            $this->filter_where[] = ('a.application_id = ' . $appid);
            $this->filter_where[] = ('a.type = "' . $type . '"');
        }
	}

	/**
	 *
	 */
	protected function setAccessWhere()
	{
		$user                 = JFactory::getUser();
		$this->access_where[] = 'a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')';
        if (!$this->showUnpublished) {
            // Show both the published and unpublished articles
            if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content'))) {
                $this->access_where[] = '(a.state = 1 or a.state = 2)';
                // Hide any articles that are not in the published date range
                $now                  = JFactory::getDate()->toSql();
                $nullDate             = $this->db->getNullDate();
                $this->access_where[] = '(a.publish_up = ' . $this->db->Quote($nullDate) . ' OR a.publish_up <= ' . $this->db->Quote($now) . ')';
                $this->access_where[] = '(a.publish_down = ' . $this->db->Quote($nullDate) . ' OR a.publish_down >= ' . $this->db->Quote($now) . ')';
            }

        }
        $this->access_where[] = '(a.state != -2)'; // Never show trashed
	}

    /**
     *
     */
    protected function setDisplayedWhere(){
        if (!empty($this->displayedIds) ) {
            $this->displayed_where[] = 'a.id NOT IN (' . implode(',', $this->displayedIds) . ')';
        }
    }

	/**
	 * @param $data
	 */
	protected function application($data)
	{
		$this->stringMatch('a.application_id', $data);
	}

	/**
	 * @param $data
	 */
	protected function type($data)
	{
		$this->stringMatch('a.type', $data);
	}

	/**
	 * @param $data
	 */
	protected function id($data)
	{
		$this->article_where[] = 'a.id IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 */
	protected function article($data)
	{
		$this->article_where[] = 'a.id IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 */
	protected function author($data)
	{
		$this->filter_where[] = 'a.created_by IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function authoralias($data)
	{
		$this->textMatch('a.created_by_alias', $data);
	}


	/**
	 * @param $data
	 */
	protected function modifiedby($data)
	{
		$this->filter_where[] = 'a.modified_by IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 */
	protected function category($data)
	{
		$this->categories($data);
	}

	/**
	 * @param $data
	 */
	protected function access($data)
	{
		$this->filter_where[] = 'a.access IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 */
	protected function featured($data)
	{
		$this->isFeatured('CONCAT_WS(",", ci.category_id)', $data);
	}

	/**
	 * @param $data
	 */
	protected function published($data)
	{
		$this->booleanMatch('a.state', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function publish_up($data)
	{
		$this->dateMatch('a.publish_up', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function publish_down($data)
	{
		$this->dateMatch('a.publish_down', $data);
	}
	/**
	 * @param $data
	 */
	protected function title($data)
	{
		$this->textMatch('a.name', $data);
	}

	/**
	 * @param $data
	 */
	protected function alias($data)
	{
		$this->textMatch('a.alias', $data);
	}

	/**
	 * @param $data
	 */
	protected function rating($data)
	{
		$this->numberMatch('ROUND(AVG(v.value), 0) AS rating', $data);
	}

	/**
	 * @param $data
	 */
	protected function hits($data)
	{
		$this->numberMatch('a.hits', $data);
	}

	/**
	 * @param $data
	 */
	protected function createdDate($data)
	{
		$this->dateMatch('a.created', $data);
	}

	/**
	 * @param $data
	 */
	protected function modifiedDate($data)
	{
		$this->dateMatch('a.modified', $data);
	}

	/**
	 * @param $data
	 */
	protected function articletext($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$match = trim($match);
			if (!empty($match)) {
				$wheres[] = 'searchable like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
			}
		}
		if (!empty($wheres)) {
			$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
		}
	}

	/**
	 * @param $data
	 */
	protected function tag($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$match = trim($match);
			if (!empty($match)) {
				$wheres[] = 'CONCAT_WS(",", t.name) like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
			}
		}
		if (!empty($wheres)) {
			$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
		}
	}


	/**
	 * @param $data
	 */
	protected function searchablefield($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$match = trim($match);
			if (!empty($match)) {
				$wheres[] = 'CONCAT(si.value) like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
			}
		}
		if (!empty($wheres)) {
			$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
		}
	}

	/**
	 * @param $data
	 */
	protected function categories($data)
	{
		if (in_array('-1', $data)) {
			$wheres[] = ('(CONCAT_WS(",", ci.category_id)) = ""');
			$wheres[] = ('(CONCAT_WS(",", ci.category_id)) = 0');
		} else {
			$category_ids = array();
			foreach ($data as $category_id) {
				$category_ids[] = $category_id;
				foreach (self::getChildren($category_id) as $child_category) {
					$category_ids[] = $child_category;
				}
			}
			foreach ($category_ids as $catid) {
				$wheres[] = (int)$catid . ' IN (CONCAT_WS(",", ci.category_id))';
			}
		}
		$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
	}


	/**
	 * @static
	 *
	 * @param      $id
	 * @param bool $recursive
	 *
	 * @return array
	 */
	protected static function getChildren($id, $recursive = true)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.name, a.parent');
		$query->from('#__zoo_category AS a');
		$query->where('a.parent =' . $id);

		$db->setQuery($query);
		$children = $db->loadObjectList();

		$items = array();
		if (count($children)) {
			foreach ($children as $child) {
				$items[] = $child->id;
				if ($recursive) {
					$items = array_merge($items, self::getChildren($child->id));
				}
			}
		}
		return $items;
	}

	/**
	 * @param $field
	 * @param $data
	 */
	protected function isFeatured($field, $data)
	{
		$wheres = array();
		if ($data[0] == 'yes') {
			$wheres[] = '(0 IN ( ' . $field . ' ))';
		} else if ($data[0] == 'no') {
			$wheres[] = '(0 NOT IN ( ' . $field . ' ))';
			$wheres[] = $field . ' = ""';
		}
		$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
	}

	/**
	 * @param $data
	 */
	protected function sort_title($data)
	{
		$this->normalSortBy('a.name', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_alias($data)
	{
		$this->normalSortBy('a.alias', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_category($data)
	{
		$this->normalSortBy('c.name', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_createddate($data)
	{
		$this->normalSortBy('a.created', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_modifieddate($data)
	{
		$this->normalSortBy('a.modified', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_modifiedby($data)
	{
		$this->normalSortBy('um.name', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_author($data)
	{
		$this->normalSortBy('ua.name', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_rating($data)
	{
		$this->normalSortBy('rating', $data);
	}

	/**
	 * @param $data
	 */
	protected function sort_hits($data)
	{
		$this->normalSortBy('a.hits', $data);
	}

    /**
   	 * @param $data
   	 */
   	protected function sort_ordering($data)
   	{
   		$this->normalSortBy('a.priority', $data);
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

}
