<?php
/**
 * @version   $Id: ElementPopulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_ElementPopulator implements RokCommon_Filter_IPicklistPopulator
{
    /**
     *
     * @return array;
     */
    public function getPicklistOptions()
    {
        require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

        $app = App::getInstance('zoo');
        $applications = $app->application->getApplications();
        $options = array();
        foreach ($applications as $application) {
            $types = $application->getTypes();
            foreach ($types as $type) {
                $elements = $type->getElements();
                foreach ($elements as $element){
                    if($element->config->type == 'textarea'){
                        $options['text_field_' . $element->identifier] = $application->name . ' - ' . $element->config->name;
                    }
                }
            }
        }
        return $options;
    }
}
