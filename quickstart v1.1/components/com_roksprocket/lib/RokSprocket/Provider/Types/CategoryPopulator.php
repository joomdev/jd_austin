<?php
/**
 * @version   $Id: CategoryPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Types_CategoryPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        $args = array(
         	'type'                     => 'post',
         	'child_of'                 => 0,
         	'orderby'                  => 'name',
         	'order'                    => 'ASC',
         	'hide_empty'               => 1,
         	'hierarchical'             => 1,
         	'taxonomy'                 => 'category',
         	'pad_counts'               => true );

         $categories = get_categories( $args );

         $children = array ();
         if ($categories) {
             foreach ($categories as $v) {
                 $v->title = $v->cat_name;
                 $v->parent_id = $v->category_parent;
                 $v->id = $v->cat_ID;
                 $pt = $v->category_parent;
                 $list = @$children[$pt]?$children[$pt]: array ();
                 array_push($list, $v);
                 $children[$pt] = $list;
             }
         }

         //treecurse function from functions.php
         $list = treerecurse($children);

         $mitems = array();
         foreach ($list as $item) {
             $mitems[$item->id] = $item->treename;
         }
         return $mitems;
     }
}
