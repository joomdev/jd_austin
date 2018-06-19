<?php
/**
 * @version   $Id: Filter_Type_CustomField.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo_Filter_Type_CustomField extends RokCommon_Filter_Type
{
    /**
     * @var string
     */
    protected $type = 'customfield';
    /**
     * @var bool
     */
    public $isselector = true;

    /**
     * @var array
     */
    public $class_mappings = array(
        'textfield' => 'RokCommon_Filter_Type_Text',
        'textarea' => 'RokCommon_Filter_Type_Text',
        'link' => 'RokCommon_Filter_Type_Text',
        'labels' => 'RokCommon_Filter_Type_Text',
        'radio' => 'RokSprocket_Provider_Zoo_SelectField',
        'select' => 'RokSprocket_Provider_ZOO_SelectField',
        'multipleSelect' => 'RokSprocket_Provider_Zoo_SelectField',
        'date' => 'RokCommon_Filter_Type_Date'
    );

    /**
     * @var array
     */
    public $selection_labels = array();

    /**
     * @var array
     */
    public $selection_types = array();

    /**
     * @var array
     */
    public $selection_options = array();


    /**
     * @param null|SimpleXMLElement $xmlnode
     * @param null $renderer
     */
    function __construct(SimpleXMLElement &$xmlnode = null, $renderer = null)
    {
        parent::__construct();
        $this->container = RokCommon_Service::getContainer();
        $this->xmlnode = $xmlnode;

        if (null != $renderer) {
            $this->selectRenderer = $renderer;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id, name, value, type');
        $query->from('#__k2_extra_fields');
        $query->order('name ASC');

        // Get the options.
        $db->setQuery($query);
        $items = $db->loadObjectList();

        $this->selection_types = array();
        $this->selection_labels = array();
        $this->selection_options = array();
        foreach ($items as $item) {
            if (array_key_exists($item->type, $this->class_mappings)) {
                $this->selection_types[$item->id] = $this->class_mappings[$item->type];
                $this->selection_labels[$item->id] = $item->name;
                $this->selections[$item->id] = new $this->class_mappings[$item->type]($this->xmlnode);
                switch ($item->type) {
                    case 'radio':
                    case 'select':
                    case 'multipleSelect':
                    case 'labels':
                        $this->selections[$item->id]->setSelectionOptions($this->getSelectFieldOptions($item));
                        $this->selections[$item->id]->setName($item->type);
                        break;
                    default:
                }

            }
        }
    }

    protected function getSelectFieldOptions($item)
    {
        $options = array();
        if (!empty($item->value)) {
            $option_pairs = json_decode($item->value);
            foreach ($option_pairs as $option) {
                $options[$option->value] = $option->name;
            }
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getChunkSelectionRender()
    {
        return rc__('ROKSPROCKET_FILTER_TYPE_ZOO_CUSTOMFIELD_RENDER', $this->getTypeDescription());
    }

    /**
     * @param $name
     * @param $type
     * @param $values
     * @return string
     */
    public function render($name, $type, $values)
    {
        return rc__('ROKSPROCKET_FILTER_TYPE_ZOO_CUSTOMFIELD_RENDER', parent::render($name, $type, $values));
    }

}
