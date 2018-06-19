<?php
/**
 * @version	  $Id: Filter.php 28645 2015-07-15 16:56:02Z james $
 * @author	  RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license	  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_EasyBlog_Filter extends RokSprocket_Provider_AbstractJoomlaPlatformFilter
{
	/*
	* @return void
	*/

	function providerVersion() {
		$provider_xml = JFactory::getXML(JPATH_ADMINISTRATOR .'/components/com_easyblog/easyblog.xml');
		$provider_version = (string)$provider_xml->version;
		return $provider_version;
	}

	protected function setBaseQuery()
	{
		$provider_version = $this->providerVersion();
		if ($provider_version < "5.0.0") {
			$this->query->select('a.id, a.title, a.content, a.intro, a.excerpt, a.category_id, a.private'
				. ', a.published, a.created, a.created_by, a.modified, a.frontpage, a.language, a.hits'
				. ', a.publish_up, a.publish_down, a.image, a.language, a.copyrights, a.source, a.robots')
				->from('#__easyblog_post as a');
		}
		elseif ($provider_version >= "5.0.0") {
			$this->query->select('a.id, a.title, a.content, a.intro, a.excerpt, pc.category_id, a.access'
				. ', a.published, a.created, a.created_by, a.modified, a.frontpage, a.language, a.hits'
				. ', a.publish_up, a.publish_down, a.image, a.language, a.copyrights, a.posttype, a.robots, a.doctype')
				->from('#__easyblog_post as a');
		}
		// $this->query->select('CASE WHEN a.private = 0 THEN "public"
		// WHEN a.private = 1 THEN "private"
		// END AS item_access');
			$this->query->select('f.content_id');
			$this->query->join('LEFT', '#__easyblog_featured AS f ON f.content_id = a.id');

			$this->query->select('c.title AS category_title, c.private');
			if ($provider_version < "5.0.0") {
				$this->query->join('LEFT', '#__easyblog_category AS c ON c.id = a.category_id');
			}
			elseif ($provider_version >= "5.0.0") {
			$this->query->join('LEFT', '#__easyblog_post_category AS pc ON pc.post_id = a.id and pc.primary = 1');
			$this->query->join('LEFT', '#__easyblog_category AS c ON c.id = pc.category_id');
			}
			$this->query->select('ua.name AS author_name');
			$this->query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

			$this->query->select('ua.name AS author_name');
			$this->query->join('LEFT', '#__easyblog_users AS eu ON eu.id = ua.id');

			$this->query->select('ROUND( SUM(v.value) / COUNT(v.id), 0 ) AS rating, COUNT(v.uid) as rating_count');
			$this->query->join('LEFT', '#__easyblog_ratings AS v ON v.uid = a.id');

			$this->query->select('l.title AS language_title');
			$this->query->join('LEFT', '#__languages AS l ON a.language = l.lang_code');

			$this->query->select('m.keywords AS metakey, m.description AS metadesc');
			$this->query->join('LEFT', '#__easyblog_meta AS m ON (m.content_id = a.id AND m.type = "post")');

			$this->query->select('CONCAT_WS(",", t.title) AS tags');
			$this->query->join('LEFT', '#__easyblog_post_tag AS pt ON pt.post_id = a.id');
			$this->query->join('LEFT', '#__easyblog_tag AS t ON t.id = pt.tag_id');

			$this->query->select('COUNT(cc.id) AS comment_count');
			$this->query->join('LEFT', '#__easyblog_comment AS cc ON cc.post_id = a.id');

			//acl access stuff
	//		  $this->query->select('CASE WHEN c.private = 2 THEN CONCAT_WS(",", ca.content_id)
	//			WHEN c.private = 0 THEN "public"
	//			WHEN c.private = 1 THEN "private"
	//			END AS category_access');
			$this->query->select('CONCAT_WS(",", ca.content_id) AS category_access');
			$this->query->join('LEFT', '#__easyblog_category_acl AS ca ON (ca.category_id = c.id AND ca.type = "group")');

			$this->query->join('LEFT', '#__easyblog_category_acl_item AS cai ON (cai.id = ca.acl_id AND cai.action = "view")');

			$this->query->group('a.id');
	}

	/**
	 *
	 * @return void
	 */
	protected function setAccessWhere()
	{
		$user = JFactory::getUser();

		$provider_version = $this->providerVersion();

		if ($user->guest) {
			//item is public && category is public
			if ($provider_version < "5.0.0") {
				$this->access_where[] = '((c.private = 0 AND a.private = 0) OR (1 IN (CONCAT_WS(",", ca.content_id))))';
			}
			elseif ($provider_version >= "5.0.0") {
				$this->access_where[] = '((c.private = 0 AND a.access = 0) OR (1 IN (CONCAT_WS(",", ca.content_id))))';
			}
		} else {
			//registered users have all rights at item level must check category level rights
			//category is private or has user groups
			foreach ($user->getAuthorisedGroups() as $grp) {
				$or[] = '(' . $grp . ' IN (CONCAT_WS(",", ca.content_id)))';
			}
			$this->access_where[] = '((c.private < 2) OR (' . implode(' OR ', $or) . '))';
		}
		if (!$this->showUnpublished) {
			// Show both the published and unpublished articles
			if ((!$user->authorise('core.edit.state', 'com_easyblog')) && (!$user->authorise('core.edit', 'com_easyblog'))) {
				$this->access_where[] = '(a.published = 1 or a.published = 2)';
				// Hide any articles that are not in the published date range
				$now = JFactory::getDate()->toSql();
				$nullDate = $this->db->getNullDate();
				$this->access_where[] = '(a.publish_up = ' . $this->db->Quote($nullDate) . ' OR a.publish_up <= ' . $this->db->Quote($now) . ')';
				$this->access_where[] = '(a.publish_down = ' . $this->db->Quote($nullDate) . ' OR a.publish_down >= ' . $this->db->Quote($now) . ')';
			}

		}

		if ($provider_version < "5.0.0") {
			$this->access_where[] = '(a.published != -2)'; // Never show trashed
		}
		elseif ($provider_version >= "5.0.0") {
			$this->access_where[] = '(a.state = 0)'; // never show trashed or archived items.
		}
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
	 *
	 * @return void
	 */
	protected function id($data)
	{
		if ($data) {
			foreach($data as &$item) {
				$item = (int) $item;
			}
		}

		$this->article_where[] = 'a.id IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function article($data)
	{
		if ($data) {
			foreach($data as &$item) {
				$item = (int) $item;
			}
		}

		$this->article_where[] = 'a.id IN (' . implode(',', $data) . ')';
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function author($data)
	{
		$this->filter_where[] = 'a.created_by IN (' . implode(',', $data) . ')';
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
	 *
	 * @return void
	 */
	protected function featured($data)
	{
		if ($data[0] == 'yes') {
		   $this->filter_where[] = '(f.content_id > 0)';
		} else if ($data[0] == 'no') {
		   $this->filter_where[] = '(f.content_id IS NULL)';
		}
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function frontpage($data)
	{
		$this->booleanMatch('a.frontpage', $data);
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
	 *
	 * @return void
	 */
	protected function published($data)
	{
		$this->booleanMatch('a.published', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function item_privacy($data)
	{
		$provider_version = $this->providerVersion();
		if ($provider_version < "5.0.0") {
			foreach ($data as $match) {
				if ($match == 'public') {
					$this->filter_where[] = 'a.private = 0';
				} else if ($match == 'private') {
					$this->filter_where[] = 'a.private = 1';
				}
			}
		}
		elseif ($provider_version >= "5.0.0") {
			foreach ($data as $match) {
				if ($match == 'public') {
					$this->filter_where[] = 'a.access = 0';
				} else if ($match == 'private') {
					$this->filter_where[] = 'a.access = 1';
				}
			}
		}
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected function category_privacy($data)
	{
		foreach ($data as $match) {
			if ($match == 'public') {
				$this->filter_where[] = 'c.private = 0';
			} else if ($match == 'private') {
				$this->filter_where[] = 'c.private = 1';
			} else {
				$this->filter_where[] = '(c.private = 2) AND (' . $match . ' IN (CONCAT_WS(",", ca.content_id)))';
			}
		}
	}


	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function title($data)
	{
		$this->textMatch('a.title', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function rating($data)
	{
		// $this->numberMatch('AVG(v.value)', $data);
		$this->numberMatch('(select avg(vv.value) from #__easyblog_ratings AS vv where vv.uid = a.id)', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function hits($data)
	{
		$this->numberMatch('a.hits', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function language($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$wheres[] = 'a.language = ' . $this->db->quote($this->db->escape($match, true));
		}
		$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function createdDate($data)
	{
		$this->dateMatch('a.created', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function modifiedDate($data)
	{
		$this->dateMatch('a.modified', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function articletext($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$match = trim($match);
			if (!empty($match)) {
				$wheres[] = 'a.intro like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
			}
		}
		if (!empty($wheres)) {
			$this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
		}
	}

	/**
	 * @param $data
	 */
	protected
	function tag($data)
	{
		$wheres = array();
		foreach ($data as $match) {
			$match = trim($match);
			if (!empty($match)) {
				$wheres[] = 'CONCAT_WS(",", t.title) like ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
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
		$category_ids = array();
		foreach ($data as $category_id) {
			$category_ids[] = $category_id;
			foreach (self::getChildren($category_id) as $child_category) {
				$category_ids[] = $child_category;
			}
		}
		$this->filter_where[] = 'a.category_id IN (' . implode(',', $category_ids) . ')';
	}

	/**
	 * @static
	 *
	 * @param	   $id
	 * @param bool $recursive
	 *
	 * @return array
	 */
	protected static function getChildren($id, $recursive = true)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.parent_id');
		$query->from('#__easyblog_category AS a');
		$query->where('a.parent_id =' . $id);

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
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function sort_title($data)
	{
		$this->normalSortBy('a.title', $data);
	}


	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function sort_category($data)
	{
		$this->normalSortBy('category_title', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function sort_createddate($data)
	{
		$this->normalSortBy('a.created', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function sort_modifieddate($data)
	{
		$this->normalSortBy('a.modified', $data);
	}


	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function sort_author($data)
	{
		$this->normalSortBy('author_name', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function sort_rating($data)
	{
		$this->normalSortBy('rating', $data);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
	protected
	function sort_hits($data)
	{
		$this->normalSortBy('a.hits', $data);
	}
}
