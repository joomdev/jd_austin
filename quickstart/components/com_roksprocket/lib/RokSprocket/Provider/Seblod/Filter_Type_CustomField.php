<?php
/**
 * @version   $Id: Filter_Type_CustomField.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Seblod_Filter_Type_CustomField extends RokCommon_Filter_Type
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
        'text' => 'RokCommon_Filter_Type_Text',
        'textarea' => 'RokCommon_Filter_Type_Text',
        'wysiwyg_editor' => 'RokCommon_Filter_Type_Text',
        'wysiwyg_editor' => 'RokCommon_Filter_Type_Text',
        'checkbox' => 'RokSprocket_Provider_Seblod_SelectField',
        'radio' => 'RokSprocket_Provider_Seblod_SelectField',
        'select_simple' => 'RokSprocket_Provider_Seblod_SelectField',
        'select_numeric' => 'RokSprocket_Provider_Seblod_SelectField',
        'select_dynamic' => 'RokSprocket_Provider_Seblod_SelectField'
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

        $query->select('CONCAT(cf.name, "||", cf.storage_field) AS value, cf.type, cf.options, cf.options2, CONCAT(ct.title, " - ", IF(ctf.label!="",ctf.label,cf.label)) AS title');
        $query->from('#__cck_core_fields AS cf');
        $query->join('LEFT', '#__cck_core_type_field AS ctf ON ctf.fieldid = cf.id');
        $query->join('LEFT', '#__cck_core_types AS ct ON ct.id = ctf.typeid');
        $query->join('LEFT', '#__cck_core AS c ON c.cck = ct.name');
        $query->where('c.storage_location = "joomla_article"');
        $query->where('cf.storage_table = "#__cck_core"');
        $query->group('cf.id');
        $query->order('cf.title ASC');

        // Get the options.
        $db->setQuery($query);
        $items = $db->loadObjectList();

        $this->selection_types = array();
        $this->selection_labels = array();
        $this->selection_options = array();
        foreach ($items as $item) {
            if (array_key_exists($item->type, $this->class_mappings)) {
                $this->selection_types[$item->value] = $this->class_mappings[$item->type];
                $this->selection_labels[$item->value] = $item->title;
                $this->selections[$item->value] = new $this->class_mappings[$item->type]($this->xmlnode);
                switch ($item->type) {
                    case 'select_simple':
                    case 'select_numeric':
                    case 'select_dynamic':
                        $this->selections[$item->value]->setSelectionOptions($this->getSelectFieldOptions($item));
                        $this->selections[$item->value]->setName($item->type);
                        break;
                    default:
                }

            }
        }
    }

    protected function getSelectFieldOptions($item)
    {
        $options = array();
        switch ($item->type) {
            case 'select_simple':
                if (!empty($item->options)) {
                    $option_pairs = explode('||', $item->options);
                    foreach ($option_pairs as $option_pair) {
                        $option_parts = explode('=', $option_pair);
                        $options[(isset($option_parts[1])) ? $option_parts[1] : $option_parts[0]] = $option_parts[0];
                    }
                }
                break;

            case 'select_numeric':
                if (!empty($item->options2)) {
                    $option_parts = json_decode($item->options2);
                    if ($option_parts->first != '') { $options[$option_parts->first] = (float)$option_parts->first;}
                    for ($i = $option_parts->start; $i <= $option_parts->end;) {
                        $options[$i] = (float)$i;
                        switch ($option_parts->math) {
                            case 1:
                                $i = (float)$i*(float)$option_parts->step;
                                break;
                            case 2:
                                $i = (float)$i-(float)$option_parts->step;
                                break;
                            case 3:
                                $i = (float)$i/(float)$option_parts->step;
                                break;
                            default:
                                $i = (float)$i+(float)$option_parts->step;
                                break;
                        }

                    }
                    if ($option_parts->last != '') { $options[$option_parts->last] = (float)$option_parts->last;}
                }
                break;

            case 'select_dynamic':
                if (!empty($item->options2)) {
                    $option_parts = json_decode($item->options2);

                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);

                    if ($option_parts->query && ($option_parts->query != 'SELECT')) {
                        $query = $option_parts->query;
                        $db->setQuery($query);
                    }
                    else if ( $option_parts->name && $option_parts->value && $option_parts->table ) {
                        $dir = (isset($option_parts->orderby_direction)) ? ' '.$option_parts->orderby_direction : '';
                        $limit = (isset($option_parts->limit)) ? $option_parts->limit : '';
                        $query->select($option_parts->name . ' AS name');
                        $query->select($option_parts->value . ' AS value');
                        $query->from($option_parts->table);
                        if (isset($option_parts->where) && $option_parts->where!='')
                            $query->where($option_parts->where);
                        if (isset($option_parts->orderby)&& $option_parts->orderby!='')
                            $query->order($option_parts->orderby . $dir);
                        $db->setQuery($query, '', $limit);
                    }
                    if($rows = $db->loadObjectList()){
                        foreach($rows as $row){
                            if(isset($row->value) || isset($row->name)) {
                                $options[(isset($row->value))?$row->value:$row->name] = (isset($row->name))?$row->name:$row->value;
                            }
                        }
                    }
                }
                break;
            default:
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getChunkSelectionRender()
    {
        return rc__('ROKSPROCKET_FILTER_TYPE_SEBLOD_CUSTOMFIELD_RENDER', $this->getTypeDescription());
    }

    /**
     * @param $name
     * @param $type
     * @param $values
     * @return string
     */
    public function render($name, $type, $values)
    {
        return rc__('ROKSPROCKET_FILTER_TYPE_SEBLOD_CUSTOMFIELD_RENDER', parent::render($name, $type, $values));
    }

}
