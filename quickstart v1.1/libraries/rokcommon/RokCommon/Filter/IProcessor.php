<?php
/**
 * @version   $Id: IProcessor.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

interface RokCommon_Filter_IProcessor
{
    /**
     * @abstract
     * @param RokCommon_Filter_Selection[] $filters
     */
    public function process(array $filters, array $sort_filters = array(), $showUnpublished = false);
}
