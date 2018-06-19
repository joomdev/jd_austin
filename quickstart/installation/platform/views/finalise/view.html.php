<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewFinalise extends AView
{
	public function onBeforeMain()
	{
        $this->container->application->getDocument()->addScriptDeclaration(<<<ENDSRIPT
var akeebaAjax = null;
$(document).ready(function(){
    akeebaAjax = new akeebaAjaxConnector('index.php');

    akeebaAjax.callJSON({
        'view'   : 'runscripts',
        'format' : 'raw'
    });
});
ENDSRIPT
);
		$model = $this->getModel();

		$this->showconfig = $model->getState('showconfig', 0);

		if ($this->showconfig)
		{
			$this->configuration = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container)->getFileContents();
		}

        if($this->container->session->get('tfa_warning', false))
        {
            $this->extra_warning  = '<div class="alert alert-block alert-error">';
            $this->extra_warning .=     '<h4 class="alert-heading">'.AText::_('FINALISE_TFA_DISABLED_TITLE').'</h4>';
            $this->extra_warning .=     '<p>'.AText::_('FINALISE_TFA_DISABLED_BODY').'</p>';
            $this->extra_warning .= '</div>';
        }

		return true;
	}
}
