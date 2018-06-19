<?php
/**
 * @version                                             $Id: Matcher_Annotations.php 10831 2013-05-29 19:32:17Z btowles $
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
class RokCommon_Annotation_Matcher_Composite
{
    /**
     * @var array
     */
    protected $matchers = array();
    /**
     * @var bool
     */
    private $wasConstructed = false;

    /**
     * @param $matcher
     */
    public function add($matcher)
    {
        $this->matchers[] = $matcher;
    }

    /**
     * @param $string
     * @param $value
     *
     * @return mixed
     */
    public function matches($string, &$value)
    {
        if (!$this->wasConstructed) {
            $this->build();
            $this->wasConstructed = true;
        }
        return $this->match($string, $value);
    }

    /**
     *
     */
    protected function build()
    {
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_Parallel extends RokCommon_Annotation_Matcher_Composite
{
    /**
     * @param $string
     * @param $value
     *
     * @return bool
     */
    protected function match($string, &$value)
    {
        $maxLength = false;
        $result    = null;
        foreach ($this->matchers as $matcher) {
            $length = $matcher->matches($string, $subvalue);
            if ($maxLength === false || $length > $maxLength) {
                $maxLength = $length;
                $result    = $subvalue;
            }
        }
        $value = $this->process($result);
        return $maxLength;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function process($value)
    {
        return $value;
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_Serial extends RokCommon_Annotation_Matcher_Composite
{
    /**
     * @param $string
     * @param $value
     *
     * @return bool|int
     */
    protected function match($string, &$value)
    {
        $results      = array();
        $total_length = 0;
        foreach ($this->matchers as $matcher) {
            if (($length = $matcher->matches($string, $result)) === false) return false;
            $total_length += $length;
            $results[] = $result;
            $string    = substr($string, $length);
        }
        $value = $this->process($results);
        return $total_length;
    }

    /**
     * @param $results
     *
     * @return string
     */
    protected function process($results)
    {
        return implode('', $results);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_SimpleSerial extends RokCommon_Annotation_Matcher_Serial
{
    /**
     * @var int
     */
    private $return_part_index;

    /**
     * @param int $return_part_index
     */
    public function __construct($return_part_index = 0)
    {
        $this->return_part_index = $return_part_index;
    }

    /**
     * @param $parts
     *
     * @return mixed
     */
    public function process($parts)
    {
        return $parts[$this->return_part_index];
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_Regex
{
    /**
     * @var
     */
    private $regex;

    /**
     * @param $regex
     */
    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    /**
     * @param $string
     * @param $value
     *
     * @return bool|int
     */
    public function matches($string, &$value)
    {
        if (preg_match("/^{$this->regex}/", $string, $matches)) {
            $value = $this->process($matches);
            return strlen($matches[0]);
        }
        $value = false;
        return false;
    }

    /**
     * @param $matches
     *
     * @return mixed
     */
    protected function process($matches)
    {
        return $matches[0];
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_Annotations
{
    /**
     * @param $string
     * @param $annotations
     *
     * @return mixed
     */
    public function matches($string, &$annotations)
    {
        $annotations        = array();
        $annotation_matcher = new RokCommon_Annotation_Matcher_Annotation;
        while (true) {
            if (preg_match('/\s(?=@)/', $string, $matches, PREG_OFFSET_CAPTURE)) {
                $offset = $matches[0][1] + 1;
                $string = substr($string, $offset);
            } else {
                return; // no more annotations
            }
            if (($length = $annotation_matcher->matches($string, $data)) !== false) {
                $string = substr($string, $length);
                list($name, $params) = $data;
                $annotations[$name][] = $params;
            }
        }
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_Annotation extends RokCommon_Annotation_Matcher_Serial
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_Regex('@'));
        $this->add(new RokCommon_Annotation_Matcher_Regex('[A-Z][a-zA-Z0-9_]*'));
        $this->add(new RokCommon_Annotation_Matcher_AnnotationParameters);
    }

    /**
     * @param $results
     *
     * @return array
     */
    protected function process($results)
    {
        return array($results[1], $results[2]);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_Constant extends RokCommon_Annotation_Matcher_Regex
{
    /**
     * @var
     */
    private $constant;

    /**
     * @param $regex
     * @param $constant
     */
    public function __construct($regex, $constant)
    {
        parent::__construct($regex);
        $this->constant = $constant;
    }

    /**
     * @param $matches
     *
     * @return mixed
     */
    protected function process($matches)
    {
        return $this->constant;
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationParameters extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_Constant('', array()));
        $this->add(new RokCommon_Annotation_Matcher_Constant('\(\)', array()));
        $params_matcher = new RokCommon_Annotation_Matcher_SimpleSerial(1);
        $params_matcher->add(new RokCommon_Annotation_Matcher_Regex('\(\s*'));
        $params_matcher->add(new RokCommon_Annotation_Matcher_AnnotationValues);
        $params_matcher->add(new RokCommon_Annotation_Matcher_Regex('\s*\)'));
        $this->add($params_matcher);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationValues extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationTopValue);
        $this->add(new RokCommon_Annotation_Matcher_AnnotationHash);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationTopValue extends RokCommon_Annotation_Matcher_AnnotationValue
{
    /**
     * @param $value
     *
     * @return array
     */
    protected function process($value)
    {
        return array('value' => $value);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationValue extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_Constant('true', true));
        $this->add(new RokCommon_Annotation_Matcher_Constant('false', false));
        $this->add(new RokCommon_Annotation_Matcher_Constant('TRUE', true));
        $this->add(new RokCommon_Annotation_Matcher_Constant('FALSE', false));
        $this->add(new RokCommon_Annotation_Matcher_Constant('NULL', null));
        $this->add(new RokCommon_Annotation_Matcher_Constant('null', null));
        $this->add(new RokCommon_Annotation_Matcher_AnnotationString);
        $this->add(new RokCommon_Annotation_Matcher_AnnotationNumber);
        $this->add(new RokCommon_Annotation_Matcher_AnnotationArray);
        $this->add(new RokCommon_Annotation_Matcher_AnnotationStaticConstant);
        $this->add(new RokCommon_Annotation_Matcher_NestedAnnotation);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationKey extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_Regex('[a-zA-Z][a-zA-Z0-9_]*'));
        $this->add(new RokCommon_Annotation_Matcher_AnnotationString);
        $this->add(new RokCommon_Annotation_Matcher_AnnotationInteger);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationPair extends RokCommon_Annotation_Matcher_Serial
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationKey);
        $this->add(new RokCommon_Annotation_Matcher_Regex('\s*=\s*'));
        $this->add(new RokCommon_Annotation_Matcher_AnnotationValue);
    }

    /**
     * @param $parts
     *
     * @return array
     */
    protected function process($parts)
    {
        return array($parts[0] => $parts[2]);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationHash extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationPair);
        $this->add(new AnnotationMorePairsMatcher);
    }
}

/**
 *
 */
class AnnotationMorePairsMatcher extends RokCommon_Annotation_Matcher_Serial
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationPair);
        $this->add(new RokCommon_Annotation_Matcher_Regex('\s*,\s*'));
        $this->add(new RokCommon_Annotation_Matcher_AnnotationHash);
    }

    /**
     * @param $string
     * @param $value
     *
     * @return bool|int
     */
    protected function match($string, &$value)
    {
        $result = parent::match($string, $value);
        return $result;
    }

    /**
     * @param $parts
     *
     * @return array
     */
    public function process($parts)
    {
        return array_merge($parts[0], $parts[2]);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationArray extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_Constant('{}', array()));
        $values_matcher = new RokCommon_Annotation_Matcher_SimpleSerial(1);
        $values_matcher->add(new RokCommon_Annotation_Matcher_Regex('\s*{\s*'));
        $values_matcher->add(new RokCommon_Annotation_Matcher_AnnotationArrayValues);
        $values_matcher->add(new RokCommon_Annotation_Matcher_Regex('\s*}\s*'));
        $this->add($values_matcher);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationArrayValues extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationArrayValue);
        $this->add(new AnnotationMoreValuesMatcher);
    }
}

/**
 *
 */
class AnnotationMoreValuesMatcher extends RokCommon_Annotation_Matcher_SimpleSerial
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationArrayValue);
        $this->add(new RokCommon_Annotation_Matcher_Regex('\s*,\s*'));
        $this->add(new RokCommon_Annotation_Matcher_AnnotationArrayValues);
    }

    /**
     * @param $string
     * @param $value
     *
     * @return mixed
     */
    protected function match($string, &$value)
    {
        $result = parent::match($string, $value);
        return $result;
    }

    /**
     * @param $parts
     *
     * @return array
     */
    public function process($parts)
    {
        return array_merge($parts[0], $parts[2]);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationArrayValue extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationValueInArray);
        $this->add(new RokCommon_Annotation_Matcher_AnnotationPair);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationValueInArray extends RokCommon_Annotation_Matcher_AnnotationValue
{
    /**
     * @param $value
     *
     * @return array
     */
    public function process($value)
    {
        return array($value);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationString extends RokCommon_Annotation_Matcher_Parallel
{
    /**
     *
     */
    protected function build()
    {
        $this->add(new RokCommon_Annotation_Matcher_AnnotationSingleQuotedString);
        $this->add(new RokCommon_Annotation_Matcher_AnnotationDoubleQuotedString);
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationNumber extends RokCommon_Annotation_Matcher_Regex
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("-?[0-9]*\.?[0-9]*");
    }

    /**
     * @param $matches
     *
     * @return float|int
     */
    protected function process($matches)
    {
        $isFloat = strpos($matches[0], '.') !== false;
        return $isFloat ? (float)$matches[0] : (int)$matches[0];
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationInteger extends RokCommon_Annotation_Matcher_Regex
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("-?[0-9]*");
    }

    /**
     * @param $matches
     *
     * @return int
     */
    protected function process($matches)
    {
        return (int)$matches[0];
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationSingleQuotedString extends RokCommon_Annotation_Matcher_Regex
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("'([^']*)'");
    }

    /**
     * @param $matches
     *
     * @return mixed
     */
    protected function process($matches)
    {
        return $matches[1];
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationDoubleQuotedString extends RokCommon_Annotation_Matcher_Regex
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('"([^"]*)"');
    }

    /**
     * @param $matches
     *
     * @return mixed
     */
    protected function process($matches)
    {
        return $matches[1];
    }
}

/**
 *
 */
class RokCommon_Annotation_Matcher_AnnotationStaticConstant extends RokCommon_Annotation_Matcher_Regex
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('(\w+::\w+)');
    }

    /**
     * @param $matches
     *
     * @return bool|mixed
     */
    protected function process($matches)
    {
        $name = $matches[1];
        if (!defined($name)) {
            trigger_error("Constant '$name' used in annotation was not defined.");
            return false;
        }
        return constant($name);
    }

}

/**
 *
 */
class RokCommon_Annotation_Matcher_NestedAnnotation extends RokCommon_Annotation_Matcher_Annotation
{
    /**
     * @param $result
     *
     * @return bool|object
     */
    protected function process($result)
    {
        $builder = new RokCommon_Annotation_Builder;
        return $builder->instantiateAnnotation($result[1], $result[2]);
    }
}