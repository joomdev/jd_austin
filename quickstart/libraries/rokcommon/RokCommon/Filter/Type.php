<?php
/**
 * @version   $Id: Type.php 30067 2016-03-08 13:44:25Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKCOMMON') or die;


/**
 *
 */
abstract class RokCommon_Filter_Type implements RokCommon_Filter_IType
{
	/**
	 *
	 */
	const JAVASCRIPT_NAME_VARIABLE = '|name|';
	/**
	 *
	 */
	const DELIMITER = '|';
	/**
	 *
	 */
	const VARSEPERATOR = ':';

	/**
	 * @var RokCommon_Service_Container
	 */
	protected $container;
	/**
	 * @var string
	 */
	protected $type = '';

	/**
	 * @var string
	 */
	protected $javascript;

	/**
	 * @var array
	 */
	protected $selection_types = array();

	/**
	 * @var array
	 */
	protected $selections = array();

	/**
	 * @var array
	 */
	protected $selection_labels = array();

	/**
	 * @var bool
	 */
	protected $isselector = false;

	/**
	 * @var bool
	 */
	protected $isroot = false;

	/**
	 * @var SimpleXMLElement
	 */
	protected $xmlnode;

	/**
	 * @var string;
	 */
	protected $selectRenderer = 'html.renderer.select';

	/**
	 * @param null|SimpleXMLElement $xmlnode
	 * @param null                  $renderer
	 */
	public function __construct(SimpleXMLElement &$xmlnode = null, $renderer = null)
	{
		$this->container = RokCommon_Service::getContainer();
		$this->xmlnode   = $xmlnode;
		$this->initializeSelections();
		if (null != $renderer) {
			$this->selectRenderer = $renderer;
		}
	}

	/**
	 *
	 */
	protected function initializeSelections()
	{
		foreach ($this->selection_types as $selection_name => $selection_class) {
			//TODO check for valid class
			$this->selections[$selection_name] = new $selection_class($this->xmlnode);
		}
	}

	/**
	 * @return RokCommon_Filter_Chunk[]
	 */
	public function getChunks()
	{
		$chunks = array();
		$chunk  = new RokCommon_Filter_Chunk();


		$chunk->setSelector($this->isChunkSelector());
		$chunk->setId($this->getChunkType());
		$chunk->setRender($this->getChunkRender());
		$chunk->setJavascript($this->getChunkJavascript());
		$chunk->setRoot($this->isChunkRoot());
		if ($this->isChunkSelector()) {
			$chunk->setSelections($this->getChunkSelections());
		}
		$chunks[$chunk->getId()] = $chunk;

		if ($this->isChunkSelector()) {
			foreach ($this->selections as $selection_instance) {
				$chunks = array_merge($chunks, $selection_instance->getChunks());
			}
		}
		return $chunks;
	}

	/**
	 * @return RokCommon_Filter_Chunk_Selection[]
	 */
	public function getChunkSelections()
	{
		$selections = array();
		foreach ($this->selections as $selection_name => $selection_instance) {
			$selections[$selection_name] = new RokCommon_Filter_Chunk_Selection($selection_name, $selection_instance->getChunkSelectionRender($selection_name));
		}
		return $selections;
	}

	/**
	 * @return array
	 */
	protected function getSelections()
	{
		return $this->selection_types;
	}

	/**
	 * @return array
	 */
	protected function getSelectionLabels()
	{
		return $this->selection_labels;
	}

	/**
	 * @return string
	 */
	protected function getChunkType()
	{
		return $this->type;
	}

	/**
	 * Get the Javascript template for the chunk.
	 * @return string
	 */
	protected function getChunkJavascript()
	{
		return $this->getJavascript();
	}

	/**
	 * Render the Javascript output from the PHP side
	 *
	 * @param string $name
	 * @param null   $value
	 *
	 * @return string
	 */
	public function renderJavascript($name = self::JAVASCRIPT_NAME_VARIABLE, $value = null)
	{
		return $this->getJavascript($name, $value);
	}

	/**
	 * @param string $name
	 * @param null   $value
	 *
	 * @return string
	 */
	protected function getJavascript($name = self::JAVASCRIPT_NAME_VARIABLE, $value = null)
	{
		return $this->javascript;
	}

	/**
	 * @return bool
	 */
	protected function isChunkSelector()
	{
		return $this->isselector;
	}


	/**
	 * @return bool
	 */
	protected function isChunkRoot()
	{
		return $this->isroot;
	}

	/**
	 * @param string $name
	 * @param null   $value
	 *
	 * @return string
	 */
	protected function getSelectionRender($name = self::JAVASCRIPT_NAME_VARIABLE, $value = null)
	{
		$options = array();
		$attribs = array('class'   => $this->type . ' chzn-done',
		                 'data-key'=> $this->type
		);

		if ($this->isselector) {
			$attribs['data-selector'] = 'true';
		}
		foreach ($this->selection_labels as $selection_value => $selection_label) {
			$option    = new RokCommon_HTML_Select_Option($selection_value, $selection_label, $value == $selection_value);
			$options[] = $option;
		}
		$service = $this->selectRenderer;
		/** @var $renderer RokCommon_HTML_ISelect */
		$renderer = $this->container->{$service};
		return $renderer->getList($name, $options, $attribs);
	}

//     public function getChunkSelectionRender();
//    {
//        return 'is |' . $this->type . '|';
//    }

	/**
	 * @return string
	 */
	public function getChunkRender()
	{
		$ret = '';
		if ($this->isselector) {
			$ret = $this->getSelectionRender();
		}
		return $ret;
	}

	/**
	 * @param $name
	 * @param $type
	 * @param $values
	 *
	 * @return string
	 */
	public function render($name, $type, $values)
	{
		$html = '';
		if ($this->isselector) {
			$value = key($values[$type]);
			$html  = $this->getSelectionRender($name, $value);
		}
		return $html;
	}

	/**
	 * @param array $values
	 * @param       $parentname
	 *
	 * @return string
	 * @throws RokCommon_Exception
	 */
	public function getFieldRender(array $values, $parentname)
	{
		//if (isset($values['root'])) unset($values['root']);
		reset($values);
		$current_type = key($values);
		$current_name = $parentname . '[' . $current_type . ']';

		$html[] = '<span class="chunk">';
		$html[] = $this->render($current_name, $current_type, $values);
		$html[] = '</span>';

		if ($this->isselector) {
			$subvalues  = $values[$current_type];
			$child_type = key($subvalues);
			if (!array_key_exists($child_type, $this->selections)) {
				throw new RokCommon_Exception(rc__('Unknown Selection Type %s', $child_type));
			}

			$html[] = $this->selections[$child_type]->getFieldRender($subvalues, $current_name);
		}
		return implode('', $html);
	}

	/**
	 * @param null $type
	 * @param null $varname
	 *
	 * @return string
	 */
	public function getTypeDescription($type = null, $varname = null)
	{
		if (null == $type) {
			$type = $this->type;
		}
		if (null != $varname) {
			$varname = ':' . $varname;
		}
		return self::DELIMITER . $type . $varname . self::DELIMITER;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

}
