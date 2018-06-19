<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerBaseSetup extends AController
{
    public function apply()
    {
        /** @var AngieModelBaseSetup $model */
        $model = $this->getThisModel();
        $msg   = null;
        $type  = null;

        try
        {
            $writtenConfiguration = $model->applySettings();
            $url = 'index.php?view=finalise';

            if (!$writtenConfiguration)
            {
                $url .= '&showconfig=1';
            }
        }
        catch (Exception $exc)
        {
            $type = 'error';
            $msg  = $exc->getMessage();
            $url  = 'index.php?view=setup';
        }

        $this->setRedirect($url, $msg, $type);

        // Encode the result if we're in JSON format
        if($this->input->getCmd('format', '') == 'json')
        {
            $result['error'] = $msg;

            echo json_encode($result);
        }
    }
}
