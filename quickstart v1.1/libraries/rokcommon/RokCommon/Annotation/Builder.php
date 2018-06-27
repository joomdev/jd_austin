<?php
/**
 * @version                                             $Id: Builder.php 10831 2013-05-29 19:32:17Z btowles $
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
class RokCommon_Annotation_Builder
{
    /**
     * @var array
     */
    private static $cache = array();

    /**
     * @param $targetReflection
     *
     * @return RokCommon_Annotation_Collection
     */
    public function build($targetReflection)
    {
        $data        = $this->parse($targetReflection);
        $annotations = array();
        foreach ($data as $class => $parameters) {
            foreach ($parameters as $params) {
                $annotation = $this->instantiateAnnotation($class, $params, $targetReflection);
                if ($annotation !== false) {
                    $annotations[get_class($annotation)][] = $annotation;
                }
            }
        }
        return new RokCommon_Annotation_Collection($annotations);
    }

    /**
     * @param      $class
     * @param      $parameters
     * @param bool $targetReflection
     *
     * @return bool|object
     */
    public function instantiateAnnotation($class, $parameters, $targetReflection = false)
    {
        $class = RokCommon_Annotation_Addendum::resolveClassName($class);
        if (is_subclass_of($class, 'RokCommon_Annotation') && !RokCommon_Annotation_Addendum::ignores($class) || $class == 'RokCommon_Annotation') {
            $annotationReflection = new ReflectionClass($class);
            return $annotationReflection->newInstance($parameters, $targetReflection);
        }
        return false;
    }

    /**
     * @param $reflection
     *
     * @return mixed
     */
    private function parse($reflection)
    {
        $key = $this->createName($reflection);
        if (!isset(self::$cache[$key])) {
            $parser = new RokCommon_Annotation_Matcher_Annotations;
            $parser->matches($this->getDocComment($reflection), $data);
            self::$cache[$key] = $data;
        }
        return self::$cache[$key];
    }

    /**
     * @param $target
     *
     * @return string
     */
    private function createName($target)
    {
        if ($target instanceof ReflectionMethod) {
            return $target->getDeclaringClass()->getName() . '::' . $target->getName();
        } elseif ($target instanceof ReflectionProperty) {
            return $target->getDeclaringClass()->getName() . '::$' . $target->getName();
        } else {
            return $target->getName();
        }
    }

    /**
     * @param $reflection
     *
     * @return bool
     */
    protected function getDocComment($reflection)
    {
        return RokCommon_Annotation_Addendum::getDocComment($reflection);
    }

    /**
     * @static
     *
     */
    public static function clearCache()
    {
        self::$cache = array();
    }
}
