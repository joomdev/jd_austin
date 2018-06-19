<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewOffsitedirs extends AView
{
	public function onBeforeMain()
	{
        /** @var AngieModelSteps $stepsModel */
		$stepsModel   = AModel::getAnInstance('Steps', 'AngieModel', array(), $this->container);
        /** @var AngieModelOffsitedirs $offsiteModel */
        $offsiteModel = AModel::getAnInstance('Offsitedirs', 'AngieModel', array(), $this->container);

        $substeps   = $offsiteModel->getDirs(true, true);
		$cursubstep = $stepsModel->getActiveSubstep();

		$this->substep = $substeps[$cursubstep];
		$this->number_of_substeps = $stepsModel->getNumberOfSubsteps();

		return true;
	}
}
