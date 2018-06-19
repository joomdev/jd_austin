<?php
/**
 * @version   $Id: Orderable.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;

/**
 *
 */
class Doctrine_Template_Orderable extends Doctrine_Template
{

	/**
	 * Array of Orderable options
	 *
	 * @var string
	 */
	protected $_options = array(
		'name'            => 'ordering',
		'alias'           => null,
		'type'            => 'integer',
		'length'          => 4,
		'indexName'       => null,
		'initialPosition' => 'first',
		'options'         => array('notnull'  => true,
		                           'unsigned' => true
		),
		'orderableBy'     => array()
	);

	/**
	 * Set table definition for Orderable behavior
	 *
	 * @return void
	 */
	public function setTableDefinition()
	{
		$name = $this->_options['name'];
		if ($this->_options['alias']) {
			$name .= ' as ' . $this->_options['alias'];
		}
		if ($this->_options['indexName'] === null) {
			$this->_options['indexName'] = $this->getTable()->getTableName() . '_ordering';
		}
		$this->hasColumn($name, $this->_options['type'], $this->_options['length'], $this->_options['options']);

		$this->addListener(new Doctrine_Template_Listener_Orderable($this->_options));
	}
}
