<?php
/**
 * @version   $Id: Provider.php 19543 2014-03-07 21:49:38Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider implements RokSprocket_IProvider
{
	/**
	 *
	 */
	const SORT_METHOD_AUTOMATIC = 'automatic';
	/**
	 *
	 */
	const SORT_METHOD_MANUAL = 'manual';

	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;

	/**
	 * @var string
	 */
	protected $fitler_file;

	/**
	 * @var RokCommon_Filter
	 */
	protected $filter;

	/**
	 * @var array
	 */
	protected $article_ids = array();

	/**
	 * @var array
	 */
	protected $filters = array();

	/**
	 * @var array
	 */
	protected $sort_filters = array();

	/**
	 * @var int
	 */
	protected $module_id;

    /**
     * @var array
     */
    protected $displayed_ids = array();

	/**
	 * @var string
	 */
	protected $provider_name;

	/**
	 * @var string
	 */
	protected $sort_method = self::SORT_METHOD_AUTOMATIC;

	/**
	 * @var array
	 */
	protected $sort_options = array();

	/** @var RokCommon_Registry */
	protected $params;

	/**
	 * @var bool
	 */
	protected $showUnpublished = false;


	/**
	 * @param string $provider_name
	 */
	public function __construct($provider_name = 'unsupported')
	{
		$this->container     = RokCommon_Service::getContainer();
		$this->provider_name = $provider_name;
		$this->filter_file   = $this->container['roksprocket.providers.registered.' . $provider_name . '.path'] . '/' . $this->container['roksprocket.providers.registered.' . $provider_name . '.filter.file'];

		if (!file_exists($this->filter_file)) {
			throw new Exception(rc__('Unable to find filter file for %1s at path %2s.', $provider_name, $this->filter_file));
		}
		$xmlfile = simplexml_load_file($this->filter_file);
		$this->filter = new RokCommon_Filter($xmlfile);
	}


	/**
	 * @param array $filters
	 * @param       $sort_filters
	 */
	public function setFilterChoices($filters, $sort_filters)
	{
		$this->filters      = $this->format_filters($filters);
		$this->sort_filters = $this->format_filters($sort_filters);
	}

	/**
	 * @param $filters
	 *
	 * @return array
	 */
	protected function format_filters($filters)
	{
		$filter_lines = array();
		if (!empty($filters)) {
			$root_type = $this->filter->getRootType();
			if (!empty($filters)) {
				foreach ($filters as $row_number => $full_row) {
					foreach ($full_row[$root_type] as $filter_type => $filter_data) {
						if (!array_key_exists($filter_type, $filter_lines)) {
							$filter_lines[$filter_type] = array();
						}
						$filter_lines[$filter_type][] = $filter_data;
					}
				}
			}
		}
		return $filter_lines;
	}

	/**
	 * @return \RokCommon_Filter_IProcessor
	 */
	public function getFilterProcessor()
	{
		$processor_service = $this->container['roksprocket.providers.registered.' . $this->provider_name . '.filter.processor'];
		/** @var $processor RokCommon_Filter_IProcessor */
		$processor = $this->container->$processor_service;
		return $processor;
	}

	/**
	 * @param $id
	 */
	public function setModuleId($id)
	{
		$this->module_id = $id;
	}

    /**
     * @param $ids
     */
    public function setDisplayedIds($ids)
    {
        $this->displayed_ids = $ids;
    }


	/**
	 * @param       $method
	 * @param array $options
	 */
	public function setSortInfo($method, array $options = array())
	{
		$this->sort_method  = $method;
		$this->sort_options = $options;
	}

	/**
	 * @param \RokCommon_Registry $params
	 */
	public function setParams(RokCommon_Registry $params)
	{
		$this->params = $params;
	}

	/**
	 * @param bool $show
	 */
	public function setShowUnpublished($show = false)
	{
		$this->showUnpublished = $show;
	}

	/**
	 * @param string $default
	 * @param array  $currentTypes
	 */
	public function filterPerItemTypes($type, $name, array &$currentTypes)
	{
		return;
	}

	public static function shouldShowField($type, $name)
	{
		switch(strtolower($type))
		{
			case 'label':
			case 'provideroptionedselector':
				if (preg_match('/_default(s_title|_custom)*$/',strtolower($name)))
				{
					return self::ATTACH_TO_PROVIDER;
				}
			default:
				return self::DO_NOTHING;
		}
	}

	/**
	 * @return int
	 * @throws RokSprocket_Exception
	 */
	public static function addNewItem($module_id)
	{
		throw new RokSprocket_Exception('This provider does not support adding a new item.');
	}

	/**
	 * @param $item_id
	 * @param $module_id
	 *
	 * @return bool
	 * @throws RokSprocket_Exception
	 */
	public static function removeItem($item_id, $module_id)
	{
		throw new RokSprocket_Exception('This provider does not support removing an item.');
	}

	/**
	 * @param int|string $id
	 */
	public function postSave($id)
	{
		return;
	}


}


