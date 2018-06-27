<?php
/**
 * @version   $Id: Filter.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_ContentBuilder_Filter extends RokSprocket_Provider_AbstractJoomlaPlatformFilter
{
    /**
     *
     */
    protected function setBaseQuery()
    {
        $this->query->select('ca.article_id, ca.type, ca.reference_id, ca.record_id, ca.form_id');
        $this->query->from('#__contentbuilder_articles as ca');

        $this->query->select('a.id, a.title, a.alias, a.introtext, a.`fulltext`, a.catid'
            . ', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.featured, a.language, a.hits'
            . ', a.publish_up, a.publish_down, a.images, a.urls, a.language, a.metakey, a.metadesc, a.metadata');

        $this->query->join('LEFT', '#__content AS a ON a.id = ca.article_id');

        $this->query->join('LEFT', '#__contentbuilder_records AS cr ON (cr.record_id = ca.record_id AND cr.reference_id = ca.reference_id)');

        $this->query->join('LEFT', '#__contentbuilder_forms AS cf ON cf.id = ca.form_id');

        $this->query->select('ce.type as field_type');
        $this->query->join('LEFT', '#__contentbuilder_elements AS ce ON ce.form_id = cf.id');

        $this->query->select('s.name AS storage_table');
        $this->query->join('LEFT', '#__contentbuilder_storages AS s ON s.id = cr.reference_id');

        $this->query->select('CONCAT_WS(",", sf.name) AS field_names');
        $this->query->join('LEFT', '#__contentbuilder_storage_fields AS sf ON sf.storage_id = s.id');

        //regular joomla stuff
	    $this->query->select('c.title AS category_title, c.alias AS category_alias');
        $this->query->join('LEFT', '#__categories AS c ON c.id = a.catid');

        $this->query->select('ua.name AS author_name');
        $this->query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        $this->query->select('um.name AS last_modified_by');
        $this->query->join('LEFT', '#__users AS um ON um.id = a.modified_by');

        $this->query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count AS rating_count');
        $this->query->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

        $this->query->select('vl.title AS access_title');
        $this->query->join('LEFT', '#__viewlevels AS vl ON a.access = vl.id');

        $this->query->select('l.title AS language_title');
        $this->query->join('LEFT', '#__languages AS l ON a.language = l.lang_code');

        //stuff not really used but might be later, none used for acl that I can tell
        $this->query->join('LEFT', '#__contentbuilder_resource_access AS cra ON cra.element_id = ce.id');

        $this->query->join('LEFT', '#__contentbuilder_users AS cu ON cu.userid = ua.id');

        $this->query->join('LEFT', '#__contentbuilder_registered_users AS cru ON cru.user_id = ua.id');

        $this->query->group('sf.storage_id');
        $this->query->group('a.id');
    }

    /**
     *
     * @return void
     */
    protected function setAccessWhere()
    {
        $user = JFactory::getUser();
        $this->access_where[] = 'a.access IN(' . implode(',', $user->getAuthorisedViewLevels()) . ')';
        if (!$this->showUnpublished) {
            // Show both the published and unpublished articles
            if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content'))) {
                $this->access_where[] = '(a.state = 1 or a.state = 2)';
                // Hide any articles that are not in the published date range
                $now = JFactory::getDate()->toSql();
                $nullDate = $this->db->getNullDate();
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
     *
     * @return void
     */
    protected function id($data)
    {
        $this->article_where[] = 'a.id IN (' . implode(',', $data) . ')';
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function article($data)
    {
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
   	 *
   	 * @return void
   	 */
   	protected function authoralias($data)
   	{
   		$this->textMatch('a.created_by_alias', $data);
   	}

    /**
     * @param $data
     *
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
    protected function field($data)
    {
        $this->filter_where[] = 'sf.id IN (' . implode(',', $data) . ')';
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function storage($data)
    {
        $this->filter_where[] = 's.id IN (' . implode(',', $data) . ')';
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function category($data)
    {
        $category_ids = array();

        jimport('joomla.application.categories');
        foreach ($data as $category_id) {
            $category_ids[] = $category_id;
            $categories = JCategories::getInstance('Content');
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
     *
     * @return void
     */
    protected function access($data)
    {
        $this->filter_where[] = 'a.access IN (' . implode(',', $data) . ')';
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function featured($data)
    {
        $this->booleanMatch('a.featured', $data);
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
        $this->booleanMatch('a.state', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function title($data)
    {
        $this->textMatch('a.title', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function alias($data)
    {
        $this->textMatch('a.alias', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function rating($data)
    {
        $this->numberMatch('ROUND(v.rating_sum / v.rating_count, 0)', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function hits($data)
    {
        $this->numberMatch('a.hits', $data);
    }

    /**
     * @param $data
     *
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
     *
     * @return void
     */
    protected function createdDate($data)
    {
        $this->dateMatch('a.created', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function modifiedDate($data)
    {
        $this->dateMatch('a.modified', $data);
    }

    /**
     * @param $data
     *
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
     *
     * @return void
     */
    protected function sort_title($data)
    {
        $this->normalSortBy('a.title', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_alias($data)
    {
        $this->normalSortBy('a.alias', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_category($data)
    {
        $this->normalSortBy('category_title', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_createddate($data)
    {
        $this->normalSortBy('a.created', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_modifieddate($data)
    {
        $this->normalSortBy('a.modified', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_modifiedby($data)
    {
        $this->normalSortBy('last_modified_by', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_author($data)
    {
        $this->normalSortBy('author_name', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_rating($data)
    {
        $this->normalSortBy('rating', $data);
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function sort_hits($data)
    {
        $this->normalSortBy('a.hits', $data);
    }
}

