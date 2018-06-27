<?php
/**
 * @version                                             $Id: ReflectionClass.php 10831 2013-05-29 19:32:17Z btowles $
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

class RokCommon_Annotation_ReflectionClass extends ReflectionClass
{
    /**
     * @var RokCommon_Annotation_Collection
     */
    private $annotations;

    /**
     * @param $class
     */
    public function __construct($class)
    {
        parent::__construct($class);
        $this->annotations = $this->createAnnotationBuilder()->build($this);
    }

    /**
     * @param $class
     *
     * @return bool
     */
    public function hasAnnotation($class)
    {
        return $this->annotations->hasAnnotation($class);
    }

    /**
     * @param $annotation
     *
     * @return bool|mixed
     */
    public function getAnnotation($annotation)
    {
        return $this->annotations->getAnnotation($annotation);
    }

    /**
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations->getAnnotations();
    }

    /**
     * @param bool $restriction
     *
     * @return array
     */
    public function getAllAnnotations($restriction = false)
    {
        return $this->annotations->getAllAnnotations($restriction);
    }

    /**
     * @return null|RokCommon_Annotation_ReflectionMethod
     */
    public function getConstructor()
    {
        return $this->createReflectionAnnotatedMethod(parent::getConstructor());
    }

    /**
     * @param $name
     *
     * @return null|RokCommon_Annotation_ReflectionMethod
     */
    public function getMethod($name)
    {
        return $this->createReflectionAnnotatedMethod(parent::getMethod($name));
    }

    /**
     * @param $filter
     *
     * @return array
     */
    public function getMethods($filter = -1)
    {
        $result = array();
        foreach (parent::getMethods($filter) as $method) {
            $result[] = $this->createReflectionAnnotatedMethod($method);
        }
        return $result;
    }

    /**
     * @param $name
     *
     * @return null|RokCommon_Annotation_ReflectionProperty
     */
    public function getProperty($name)
    {
        return $this->createReflectionAnnotatedProperty(parent::getProperty($name));
    }

    /**
     * @param $filter
     *
     * @return array
     */
    public function getProperties($filter = -1)
    {
        $result = array();
        foreach (parent::getProperties($filter) as $property) {
            $result[] = $this->createReflectionAnnotatedProperty($property);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getInterfaces()
    {
        $result = array();
        foreach (parent::getInterfaces() as $interface) {
            $result[] = $this->createReflectionAnnotatedClass($interface);
        }
        return $result;
    }

    /**
     * @return bool|RokCommon_Annotation_ReflectionClass
     */
    public function getParentClass()
    {
        $class = parent::getParentClass();
        return $this->createReflectionAnnotatedClass($class);
    }

    /**
     * @return RokCommon_Annotation_Builder
     */
    protected function createAnnotationBuilder()
    {
        return new RokCommon_Annotation_Builder();
    }

    /**
     * @param $class
     *
     * @return bool|RokCommon_Annotation_ReflectionClass
     */
    private function createReflectionAnnotatedClass($class)
    {
        return ($class !== false) ? new RokCommon_Annotation_ReflectionClass($class->getName()) : false;
    }

    /**
     * @param $method
     *
     * @return null|RokCommon_Annotation_ReflectionMethod
     */
    private function createReflectionAnnotatedMethod($method)
    {
        return ($method !== null) ? new RokCommon_Annotation_ReflectionMethod($this->getName(), $method->getName()) : null;
    }

    /**
     * @param $property
     *
     * @return null|RokCommon_Annotation_ReflectionProperty
     */
    private function createReflectionAnnotatedProperty($property)
    {
        return ($property !== null) ? new RokCommon_Annotation_ReflectionProperty($this->getName(), $property->getName()) : null;
    }
}