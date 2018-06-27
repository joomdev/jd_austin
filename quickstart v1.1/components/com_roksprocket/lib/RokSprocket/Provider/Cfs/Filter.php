<?php
/**
 * @version   $Id: Filter.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Cfs_Filter extends RokSprocket_Provider_AbstractWordpressPlatformFilter
{
    /**
     *
     */
    protected function setBaseQuery()
    {
        global $wpdb;
        $this->base_query = '';
        $this->base_query .= 'SELECT p.ID as post_id, p.post_author, p.post_date, p.post_content, p.post_title, p.post_excerpt, p.post_status, p.post_password';
        $this->base_query .= ', p.post_name, p.post_name, p.post_modified, p.guid, p.post_parent, p.menu_order, p.post_type, p.comment_count';
        $this->base_query .= ', u.user_nicename, u.display_name';
        $this->base_query .= ', pm.meta_value AS thumbnail_id';
        $this->base_query .= ', CONCAT_WS(",", t.name) AS tags';
        $this->base_query .= ', CONCAT_WS(",", t2.name) AS categories, CONCAT_WS(",", t2.term_id) AS category_ids';
        $this->base_query .= ', CONCAT_WS(",", t3.term_id) AS taxonomy_ids';

        $this->base_query .= ' FROM ' . $wpdb->prefix . 'posts as p';

        //join over postmeta to get thumbnail
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'postmeta as pm ON (pm.post_id = p.ID AND pm.meta_key = "_thumbnail_id")';

        //join over taxonomy to get tags
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships as tr ON tr.object_id = p.ID';
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy as tx ON (tx.term_taxonomy_id = tr.term_taxonomy_id AND tx.taxonomy = "post_tag")';
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'terms as t ON t.term_id = tx.term_id';

        //join over taxonomy to get categories
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships as tr2 ON tr2.object_id = p.ID';
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy as tx2 ON (tx2.term_taxonomy_id = tr2.term_taxonomy_id AND tx2.taxonomy = "category")';
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'terms as t2 ON t2.term_id = tx2.term_id';

        //join over taxonomy regardless of type for filter on terms
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships as tr3 ON tr3.object_id = p.ID';
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy as tx3 ON tx3.term_taxonomy_id = tr3.term_taxonomy_id';
        $this->base_query .= ' LEFT JOIN ' . $wpdb->prefix . 'terms as t3 ON t3.term_id = tx3.term_id';

        //join over users
        $this->base_query .= ' LEFT JOIN ' . $wpdb->base_prefix . 'users as u ON u.ID = p.post_author';

        //only posts and pages
        $this->base_where[] = '(p.post_type = "post" OR p.post_type = "page")';
        $this->base_where[] = '(p.post_status != "auto-draft" AND p.post_status != "inherit")';

        //group by id
        $this->group_by[] = 'p.ID';
    }

    /**
     *
     */
    protected function setAccessWhere()
    {
        if (!$this->showUnpublished) {
            if ((current_user_can('edit_post')) && (current_user_can('edit_page'))) {
                $this->access_where[] = ' (p.post_status = "publish" OR p.post_status = "private")';
            }
        }
    }

    /**
     *
     */
    protected function setDisplayedWhere(){
        if (!empty($this->displayedIds) ) {
            $this->displayed_where[] = 'p.ID NOT IN (' . implode(',', $this->displayedIds) . ')';
        }
    }

    /**
     * @param $data
     */
    protected function id($data)
    {
        $this->article_where[] = 'p.ID IN (' . implode(',', $data) . ')';
    }

    /**
     * @param $data
     */
    protected function article($data)
    {
        $this->article_where[] = 'p.ID IN (' . implode(',', $data) . ')';
    }

    /**
    * @param $data
    */
   protected function posttype($data)
   {
       foreach ($data as $match) {
           $match = trim($match);
           if (!empty($match)) {
               $wheres[] = 'p.post_type = "' . $match . '"';
           }
       }
       if (!empty($wheres)) {
           $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
       }
   }

    /**
     * @param $data
     */
    protected function author($data)
    {
        $this->filter_where[] = 'p.post_author IN (' . implode(',', $data) . ')';
    }

    /**
     * @param $data
     */
    protected function modifiedby($data)
    {
        $this->filter_where[] = 'pm._edit_last IN (' . implode(',', $data) . ')';
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
                $wheres[] = 't.name = "' . $match . '"';
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }

    /**
     * @param $data
     */
    protected function category($data)
    {
        $wheres = array();
        foreach ($data as $match) {
            $match = trim($match);
            if (!empty($match)) {
                $wheres[] = $match . ' IN (IF(CONCAT_WS(",", t2.term_id) IS NULL,0,t2.term_id))';
            }
            foreach (self::getChildren($match) as $child_category) {
                $wheres[] = $child_category . ' IN (IF(CONCAT_WS(",", t2.term_id) IS NULL,0,t2.term_id))';
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }

    /**
     * @static
     *
     * @param      $id
     * @param bool $recursive
     *
     * @return array
     */
    protected static function getChildren($id)
    {

        $args = array(
            'type' => 'post',
            'child_of' => $id,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => 1,
            'hierarchical' => 1,
            'taxonomy' => 'category',
            'pad_counts' => true);

        $children = get_categories($args);

        $items = array();
        if (count($children)) {
            foreach ($children as $child) {
                $items[] = $child->cat_ID;
            }
        }
        return $items;
    }

    /**
     * @param $data
     */
    protected function access($data)
    {
        //$this->filter_args['perm'] = $data;
    }

    /**
     * @param $data
     */
    protected function password($data)
    {
        if ($data[0] == "no") {
            $this->filter_where[] = '(p.post_password = "" || p.post_password IS NULL)';
        } else {
            $this->filter_where[] = '(p.post_password != "" && p.post_password IS NOT NULL)';
        }
    }

    /**
     * @param $data
     */
    protected function status($data)
    {
        foreach ($data as $match) {
            $match = trim($match);
            if (!empty($match)) {
                $wheres[] = 'p.post_status = "' . $match . '"';
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }

    /**
     * @param $data
     */
    protected function title($data)
    {
        $this->textMatch('p.post_title', $data);
    }

    /**
     * @param $data
     */
    protected function slug($data)
    {
        $this->textMatch('p.post_name', $data);
    }


    /**
     * @param $data
     */
    protected function name($data)
    {
        $this->textMatch('p.post_name', $data);
    }

    /**
     * @param $data
     */
    protected function comments($data)
    {
        $this->numberMatch('p.comment_count', $data);
    }

    /**
     * @param $data
     */
    protected function createdDate($data)
    {
        $this->dateMatch('p.post_date', $data);
    }

    /**
     * @param $data
     */
    protected function modifiedDate($data)
    {
        $this->dateMatch('p.post_modified', $data);
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
                $wheres[] = 'p.post_content LIKE ' . $this->db->quote('%' . $this->db->escape($match, true) . '%');
            }
        }
        if (!empty($wheres)) {
            $this->filter_where[] = '(' . implode(' OR ', $wheres) . ')';
        }
    }

    protected function group($data)
    {
        foreach ($data as $id) {

            $rules = get_post_meta($id);
            $rules = $rules['cfs_rules'][0];
            $rules = unserialize($rules);

            foreach ($rules as $key => $val) {
                switch ($key) {
                    case 'post_types':
                        foreach ($key['values'] as $value) {
                            $wheres[] = 'p.post_type ' . $key['operator'][0] . ' ' . $value;
                        }
                        break;
                    case 'user_roles':
                        foreach ($key['values'] as $value) {
                            //$wheres[] = 'p.post_type ' . $key['operator'][0] . ' ' . $value;
                        }
                        break;
                    case 'term_ids':
                        foreach ($key['values'] as $value) {
                            if ($key['operator'][0] == "==") {
                                $wheres[] = $value . ' IN (IF(CONCAT_WS(",", t3.term_id) IS NULL,0,t3.term_id))';
                            } else {
                                $wheres[] = $value . ' NOT IN (IF(CONCAT_WS(",", t3.term_id) IS NULL,0,t3.term_id))';
                            }
                        }
                        break;
                    case 'post_ids':
                        if ($key['operator'][0] == "==") {
                            $wheres[] = 'p.ID IN (' . implode(',', $key['values']) . ')';
                        } else {
                            $wheres[] = 'p.ID NOT IN (' . implode(',', $key['values']) . ')';
                        }
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * @param $data
     */
    protected function sort_title($data)
    {
        $this->normalSortBy('p.post_title', $data);
    }

    /**
     * @param $data
     */
    protected function sort_slug($data)
    {
        $this->normalSortBy('p.post_name', $data);
    }

    /**
     * @param $data
     */
    //	protected function sort_category($data)
    //	{
    //        $this->normalSortBy('category_name', $data);
    //	}

    /**
     * @param $data
     */
    protected function sort_createddate($data)
    {
        $this->normalSortBy('p.post_date', $data);
    }

    /**
     * @param $data
     */
    protected function sort_modifieddate($data)
    {
        $this->normalSortBy('p.post_modified', $data);
    }

    /**
     * @param $data
     */
    protected function sort_modifiedby($data)
    {
        $this->normalSortBy('pm._edit_last', $data);
    }

    /**
     * @param $data
     */
    protected function sort_author($data)
    {
        $this->normalSortBy('p.post_author', $data);
    }

    /**
     * @param $data
     */
    protected function sort_comments($data)
    {
        $this->normalSortBy('p.comment_count', $data);
    }
}
