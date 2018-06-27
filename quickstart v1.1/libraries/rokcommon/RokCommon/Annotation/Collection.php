<?php
/**
 * @version                                             $Id: Collection.php 10831 2013-05-29 19:32:17Z btowles $
 * @author                                              RocketTheme http://www.rockettheme.com
 * @copyright                                           Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license                                             http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Based on Addendum
 * Original Copyright below
 *
 * RokCommon_Annotation_Addendum PHP Reflection Annotations
 * http://code.google.com/p/addendum/
 *
 * Copyright (C) 2006-2009 Jan "johno Suchal <johno@jsmf.net>
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 **/
defined('ROKCOMMON') or die('Restricted access');

/**
 *
 */
class RokCommon_Annotation_Collection
{
    /**
     * @var
     */
    private $annotations;

    /**
     * @param $annotations
     */
    public function __construct($annotations)
    {
        $this->annotations = $annotations;
    }

    /**
     * @param $class
     *
     * @return bool
     */
    public function hasAnnotation($class)
    {
        $class = RokCommon_Annotation_Addendum::resolveClassName($class);
        return isset($this->annotations[$class]);
    }

    /**
     * @param $class
     *
     * @return bool|mixed
     */
    public function getAnnotation($class)
    {
        $class = RokCommon_Annotation_Addendum::resolveClassName($class);
        return isset($this->annotations[$class]) ? end($this->annotations[$class]) : false;
    }

    /**
     * @return array
     */
    public function getAnnotations()
    {
        $result = array();
        foreach ($this->annotations as $instances) {
            $result[] = end($instances);
        }
        return $result;
    }

    /**
     * @param bool $restriction
     *
     * @return array
     */
    public function getAllAnnotations($restriction = false)
    {
        $restriction = RokCommon_Annotation_Addendum::resolveClassName($restriction);
        $result      = array();
        foreach ($this->annotations as $class => $instances) {
            if (!$restriction || $restriction == $class) {
                $result = array_merge($result, $instances);
            }
        }
        return $result;
    }
}