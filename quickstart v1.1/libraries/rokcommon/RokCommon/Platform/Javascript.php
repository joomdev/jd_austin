<?php
/**
 * @version   $Id: Javascript.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Platform_Javascript
{

    /** @var string */
    protected $_name;

    /** @var string */
    protected $_verison;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $verison
     */
    public function setVerison($verison)
    {
        $this->_verison = $verison;
    }

    /**
     * @return string
     */
    public function getVerison()
    {
        return $this->_verison;
    }
}
