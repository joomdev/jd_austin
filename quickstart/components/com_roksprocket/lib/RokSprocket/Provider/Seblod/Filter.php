<?php
/**
 * @version   $Id: Filter.php 11320 2013-06-07 22:30:23Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Seblod_Filter extends RokSprocket_Provider_AbstractJoomlaPlatformFilter
{
	/**
	 *
	 * @return void
	 */
	protected function setBaseQuery()
	{
		$this->query->select('a.id, a.title, a.alias, a.introtext, a.`fulltext`, a.catid' . ', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.featured, a.language, a.hits' . ', a.publish_up, a.publish_down, a.images, a.urls, a.language, a.metakey, a.metadesc, a.metadata')->from('#__content as a');

		$this->query->select('s.id as core_id, s.cck, s.pk, s.pkb, s.storage_location, s.storage_table, s.author_id, s.parent_id, s.date_time');
		$this->query->join('LEFT', '#__cck_core AS s ON s.pk = a.id');
//		$this->query->where('s.storage_location = "joomla_article"');

		$this->query->select('st.name, st.title AS type_title');
		$this->query->join('LEFT', '#__cck_core_types AS st ON st.`name` = s.cck');

		$this->query->join('LEFT', '#__cck_core_type_field AS stf ON stf.typeid = st.id');

		$this->query->select('sf.name, sf.type, sf.storage_table, sf.storage_field');
		$this->query->join('LEFT', '#__cck_core_fields AS sf ON sf.id = stf.fieldid');

		$this->query->select('CONCAT(ssf.match_value) as searchable');
		$this->query->join('LEFT', '#__cck_core_search_field AS ssf ON ssf.fieldid = stf.fieldid');

		$this->query->select('c.title AS category_title, c.alias AS category_alias');
		$this->query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		$this->query->select('ua.name AS author_name');
		$this->query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		$this->query->select('um.name AS last_modified_by');
		$this->query->join('LEFT', '#__users AS um ON um.id = a.modified_by');

		$this->query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count');
		$this->query->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

		$this->query->select('vl.title AS access_title');
		$this->query->join('LEFT', '#__viewlevels AS vl ON a.access = vl.id');

		$this->query->select('l.title AS language_title');
		$this->query->join('LEFT', '#__languages AS l ON a.language = l.lang_code');

		$this->query->group('a.id');
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function seblod_application_type($data)
	{
		$this->numberMatch('st.id', $data);
	}


	/**
	 *
	 * @return void
	 */
	protected function setAccessWhere()
	{
		$user                 = JFactory::getUser();
		$this->access_where[] = 'a.access IN(' . implode(',', $user->getAuthorisedViewLevels()) . ')';
		if (!$this->showUnpublished) {
			if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content'))) {
				$this->access_where[] = '(a.state = 1 or a.state = 2)';
                // Hide any articles that are not in the published date range
				$now                  = JFactory::getDate()->toSql();
				$nullDate             = $this->db->getNullDate();
				$this->access_where[] = '(a.publish_up = ' . $this->db->Quote($nullDate) . ' OR a.publish_up <= ' . $this->db->Quote($now) . ')';
				$this->access_where[] = '(a.publish_down = ' . $this->db->Quote($nullDate) . ' OR a.publish_down >= ' . $this->db->Quote($now) . ')';

			}
		}
		$this->access_where[] = '(a.state != -2)';
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
	 * @return void
	 */
	protected function coretype($data)
	{
		$this->stringMatch('st.name', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function customfield($data)
	{
		$this->custommatch($data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function id($data)
	{
		$this->article_where[] = 'a.id IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function article($data)
	{
		$this->article_where[] = 'a.id IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 * @return void
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
	 * @return void
	 */
	protected function modifiedby($data)
	{
		$this->filter_where[] = ' a.modified_by IN (' . implode(',', $data) . ')';
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
	 * @return void
	 */
	protected function category($data)
	{
		$category_ids = array();

		jimport('joomla.application.categories');
		foreach ($data as $category_id) {
			$category_ids[] = $category_id;
			$categories     = JCategories::getInstance('Content');
			/** @var $category JCategoryNode */
			$category = $categories->get($category_id);
			/** @var $category JCategoryNode */
			if (!empty($categories)) {
				$category = $categories->get($category_id);
				if ($category && $category->hasChildren()) {
					foreach ($category->getChildren(true) as $child_category) {
						$category_ids[] = $child_category->id;
					}
				}
			}

		}
		if (!empty($category_ids)) {
			$this->filter_where[] = 'a.catid IN (' . implode(',', $category_ids) . ')';
		}

	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function access($data)
	{
		$this->filter_where[] = 'a.access IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function featured($data)
	{
		$this->booleanMatch('a.featured', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function published($data)
	{
		$this->booleanMatch('a.state', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function title($data)
	{
		$this->textMatch('a.title', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function alias($data)
	{
		$this->textMatch('a.alias', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function rating($data)
	{
		$this->numberMatch('ROUND(v.rating_sum / v.rating_count, 0)', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function hits($data)
	{
		$this->numberMatch('a.hits', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function language($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$wheres[] = 'a.language = ' . $this->db->quote($this->db->escape($match, true));
		}
		$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function createdDate($data)
	{
		$this->dateMatch('a.created', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function modifiedDate($data)
	{
		$this->dateMatch('a.modified', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function articletext($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$match = trim($match);
			if (!empty($match)) {
				$wheres[] = 'a.introtext like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
			}
		}
		if (!empty($wheres)) {
			$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
		}
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function customMatch($data)
	{
		$wheres = array();

		foreach ($data as $options) {
			foreach ($options as $key => $option) {

				$key_parts     = explode("||", $key);
				$storage_name  = $key_parts[0];
				$storage_field = $key_parts[1];

				$wheres[] = 'sf.name' . ' = "' . $storage_name . '"';

				foreach ($option as $type => $match) {
					switch ($type) {
						case 'matches':
							break;
						case 'contains':
							$wheres[] = 's.' . $storage_field . ' like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
							break;
						case 'beginswith':
							$wheres[] = 's.' . $storage_field . ' like ' . $this->db->quote($this->db->escape($match, true) . '%');
							break;
						case 'endswith':
							$wheres[] = 's.' . $storage_field . ' like ' . $this->db->quote('%' . $this->db->escape($match, true));
							break;
						case 'is':
							$wheres[] = 's.' . $storage_field . ' = ' . $this->db->quote($this->db->escape($match, true));
							break;
						default:
							$wheres[] = 's.' . $storage_field . ' = "' . $match . '"';
					}
				}
			}
		}
		$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function searchablefield($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$match = trim($match);
			if (!empty($match)) {
				$wheres[] = 'CONCAT(ssf.matchvalue) like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
			}
		}
		if (!empty($wheres)) {
			$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
		}
	}


	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_title($data)
	{
		$this->normalSortBy('a.title', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_alias($data)
	{
		$this->normalSortBy('a.alias', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_category($data)
	{
		$this->normalSortBy('category_title', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_createddate($data)
	{
		$this->normalSortBy('a.created', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_modifieddate($data)
	{
		$this->normalSortBy('a.modified', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_modifiedby($data)
	{
		$this->normalSortBy('last_modified_by', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_author($data)
	{
		$this->normalSortBy('author_name', $data);
	}

	/**
	 * @param $data
	 * @return void
	 */
	protected function sort_rating($data)
	{
		$this->normalSortBy('rating', $data);
	}

	/**
	 * @param $data
	 * @return void
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
		$this->normalSortBy('a.ordering', $data);
	}
}
