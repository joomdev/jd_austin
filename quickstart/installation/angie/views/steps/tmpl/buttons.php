<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var $this AView */

$document = $this->container->application->getDocument();

$data = $this->input->getData();

/** @var AngieModelSteps $stepsModel */
$stepsModel = AModel::getAnInstance('Steps', 'AngieModel', array(), $this->container);
$this->input->setData($data);

// Previous step
$prevStep = $stepsModel->getPreviousStep();

if(!empty($prevStep['step']))
{
	$url = 'index.php?view=' . $prevStep['step']
		. (!empty($prevStep['substep']) ? '&substep=' . $prevStep['substep'] : '');
	$document->appendButton(
		'GENERIC_BTN_PREV', $url, '', 'arrow-left', 'btnPrev'
	);
}

// Skip (on database step)
if($stepsModel->getActiveStep() == 'database')
{
	// Next step
	$nextStep = $stepsModel->getNextStep();
	if(!empty($nextStep['step']))
	{
		$url = 'index.php?view=' . $nextStep['step']
			. (!empty($nextStep['substep']) ? '&substep=' . $nextStep['substep'] : '');
		$document->appendButton(
			'GENERIC_BTN_SKIP', $url, 'warning', 'white forward', 'btnSkip'
		);
	}
	$key = $stepsModel->getActiveSubstep();
	$document->appendButton(
		'GENERIC_BTN_NEXT', 'javascript:databaseRunRestoration(\''.$key.'\'); return false;', 'primary', 'white arrow-right', 'btnNext'
	);
}
elseif($stepsModel->getActiveStep() == 'offsitedirs')
{
    // Next step
    $nextStep = $stepsModel->getNextStep();

    if(!empty($nextStep['step']))
    {
        $url = 'index.php?view=' . $nextStep['step']
            . (!empty($nextStep['substep']) ? '&substep=' . $nextStep['substep'] : '');
        $document->appendButton(
            'GENERIC_BTN_SKIP', $url, 'warning', 'white forward', 'btnSkip'
        );
    }

    $key = $stepsModel->getActiveSubstep();
    $document->appendButton(
        'GENERIC_BTN_NEXT', 'javascript:offsitedirsRunRestoration(\''.$key.'\'); return false;', 'primary', 'white arrow-right', 'btnNext'
    );
}
elseif($stepsModel->getActiveStep() == 'setup')
{
    $nextStep = $stepsModel->getNextStep();
    $key      = $stepsModel->getActiveSubstep();

    // Sometimes I have to display the setup page multiple times (ie Drupal multisite)
    if($key)
    {
        if(!empty($nextStep['step']))
        {
            $url = 'index.php?view=' . $nextStep['step']
                . (!empty($nextStep['substep']) ? '&substep=' . $nextStep['substep'] : '');
            $document->appendButton(
                'GENERIC_BTN_SKIP', $url, 'warning', 'white forward', 'btnSkip'
            );
        }

        $document->appendButton(
            'GENERIC_BTN_NEXT', 'javascript:setupRunRestoration(\''.$key.'\'); return false;', 'primary multisite', 'white arrow-right', 'btnNext'
        );
    }
    else
    {
        if(!empty($nextStep['step']))
        {
            $url = 'index.php?view=' . $nextStep['step']
                . (!empty($nextStep['substep']) ? '&substep=' . $nextStep['substep'] : '');
            $document->appendButton(
                'GENERIC_BTN_NEXT', $url, 'primary', 'white arrow-right', 'btnNext'
            );
        }
    }
}
else
{
	// Next step
	$nextStep = $stepsModel->getNextStep();
	if(!empty($nextStep['step']))
	{
		$url = 'index.php?view=' . $nextStep['step']
			. (!empty($nextStep['substep']) ? '&substep=' . $nextStep['substep'] : '');
		$document->appendButton(
			'GENERIC_BTN_NEXT', $url, 'primary', 'white arrow-right', 'btnNext'
		);
	}
}
