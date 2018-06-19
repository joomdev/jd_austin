<?php
/**
 * @version                                             $Id: DocComment.php 10831 2013-05-29 19:32:17Z btowles $
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
class RokCommon_Reflection_DocComment
{
    /**
     * @var array
     */
    private static $classes = array();
    /**
     * @var array
     */
    private static $methods = array();
    /**
     * @var array
     */
    private static $fields = array();
    /**
     * @var array
     */
    private static $parsedFiles = array();

    /**
     * @static
     *
     */
    public static function clearCache()
    {
        self::$classes     = array();
        self::$methods     = array();
        self::$fields      = array();
        self::$parsedFiles = array();
    }

    /**
     * @param $reflection
     *
     * @return bool
     */
    public function get($reflection)
    {
        if ($reflection instanceof ReflectionClass) {
            return $this->forClass($reflection);
        } elseif ($reflection instanceof ReflectionMethod) {
            return $this->forMethod($reflection);
        } elseif ($reflection instanceof ReflectionProperty) {
            return $this->forProperty($reflection);
        }
    }

    /**
     * @param $reflection
     *
     * @return bool
     */
    public function forClass($reflection)
    {
        $this->process($reflection->getFileName());
        $name = $reflection->getName();
        return isset(self::$classes[$name]) ? self::$classes[$name] : false;
    }

    /**
     * @param $reflection
     *
     * @return bool
     */
    public function forMethod($reflection)
    {
        $this->process($reflection->getDeclaringClass()->getFileName());
        $class  = $reflection->getDeclaringClass()->getName();
        $method = $reflection->getName();
        return isset(self::$methods[$class][$method]) ? self::$methods[$class][$method] : false;
    }

    /**
     * @param $reflection
     *
     * @return bool
     */
    public function forProperty($reflection)
    {
        $this->process($reflection->getDeclaringClass()->getFileName());
        $class = $reflection->getDeclaringClass()->getName();
        $field = $reflection->getName();
        return isset(self::$fields[$class][$field]) ? self::$fields[$class][$field] : false;
    }

    /**
     * @param $file
     */
    private function process($file)
    {
        if (!isset(self::$parsedFiles[$file])) {
            $this->parse($file);
            self::$parsedFiles[$file] = true;
        }
    }

    /**
     * @param $file
     */
    protected function parse($file)
    {
        $tokens       = $this->getTokens($file);
        $currentClass = false;
        $currentBlock = false;
        $max          = count($tokens);
        $i            = 0;
        while ($i < $max) {
            $token = $tokens[$i];
            if (is_array($token)) {
                list($code, $value) = $token;
                switch ($code) {
                    case T_DOC_COMMENT:
                        $comment = $value;
                        break;

                    case T_CLASS:
                        $class = $this->getString($tokens, $i, $max);
                        if ($comment !== false) {
                            self::$classes[$class] = $comment;
                            $comment               = false;
                        }
                        break;

                    case T_VARIABLE:
                        if ($comment !== false) {
                            $field                        = substr($token[1], 1);
                            self::$fields[$class][$field] = $comment;
                            $comment                      = false;
                        }
                        break;

                    case T_FUNCTION:
                        if ($comment !== false) {
                            $function                         = $this->getString($tokens, $i, $max);
                            self::$methods[$class][$function] = $comment;
                            $comment                          = false;
                        }

                        break;

                    // ignore
                    case T_WHITESPACE:
                    case T_PUBLIC:
                    case T_PROTECTED:
                    case T_PRIVATE:
                    case T_ABSTRACT:
                    case T_FINAL:
                    case T_VAR:
                        break;

                    default:
                        $comment = false;
                        break;
                }
            } else {
                $comment = false;
            }
            $i++;
        }
    }

    /**
     * @param $tokens
     * @param $i
     * @param $max
     *
     * @return bool
     */
    private function getString($tokens, &$i, $max)
    {
        do {
            $token = $tokens[$i];
            $i++;
            if (is_array($token)) {
                if ($token[0] == T_STRING) {
                    return $token[1];
                }
            }
        } while ($i <= $max);
        return false;
    }

    /**
     * @param $file
     *
     * @return array
     */
    private function getTokens($file)
    {
        return token_get_all(file_get_contents($file));
    }
}
