<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewDatabase extends AView
{
	/** @var int Do we have a flag for large tables? */
	public $large_tables = 0;

	public function onBeforeMain()
	{
		/** @var AngieModelSteps $stepsModel */
		$stepsModel = AModel::getAnInstance('Steps', 'AngieModel', array(), $this->container);
		/** @var AngieModelDatabase $dbModel */
		$dbModel = AModel::getAnInstance('Database', 'AngieModel', array(), $this->container);

		$this->substep = $stepsModel->getActiveSubstep();
		$this->number_of_substeps = $stepsModel->getNumberOfSubsteps();
		$this->db = $dbModel->getDatabaseInfo($this->substep);
		$this->large_tables = $dbModel->largeTablesDetected();

		if ($this->large_tables)
		{
			$this->large_tables = round($this->large_tables / (1024 * 1024), 2);
		}

		return true;
	}
}
