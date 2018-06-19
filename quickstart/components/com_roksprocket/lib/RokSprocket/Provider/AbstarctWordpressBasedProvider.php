<?php
/**
 * @version   $Id: AbstarctWordpressBasedProvider.php 22593 2014-08-08 14:46:31Z jakub $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokSprocket_Provider_AbstarctWordpressBasedProvider extends RokSprocket_Provider
{

    protected function populateTags($items)
   	{

        if (!empty($items)) {

            global $wpdb;

            $query = 'SELECT tr.object_id as object_id, t.name as name FROM '.$wpdb->term_relationships. ' as tr LEFT JOIN ';
            $query .= $wpdb->term_taxonomy.' AS tx on tr.term_taxonomy_id = tx.term_taxonomy_id LEFT JOIN ';
            $query .= $wpdb->terms.' AS t ON tx.term_id = t.term_id WHERE tx.taxonomy = "post_tag"';
            $query .= sprintf(" and tr.object_id in (%s)",implode(',',array_keys($items)));

            $tags = $wpdb->get_results($query, OBJECT);

            foreach($tags as $tag)
            {
                if(!isset($items[$tag->object_id]->tags) || !is_array( $items[$tag->object_id]->tags))
                {
                    $items[$tag->object_id]->tags = array();
                }
                $items[$tag->object_id]->tags[]=$tag->name;
            }
        }

   		return $items;
   	}

	/**
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems()
	{
		if (empty($this->filters)) return new RokSprocket_ItemCollection();

		global $wpdb;

		/** @var $filer_processor RokSprocket_Provider_AbstractWordpressPlatformFilter */
		$filer_processor = $this->getFilterProcessor();
		$filer_processor->setWidgetId($this->module_id);
        $filer_processor->setDisplayedIds($this->displayed_ids);
		$manualsort = ($this->params->get($this->params->get('provider', 'joomla') . '_sort', 'automatic') == 'manual') ? true : false;
		$filer_processor->setManualSort($manualsort);
		$filer_processor->process($this->filters, $this->sort_filters, $this->showUnpublished);

		$query = $filer_processor->getQuery();

		$raw_results = $wpdb->get_results($query, OBJECT_K);

		if ($raw_results === false) {
			throw new RokSprocket_Exception($wpdb->last_error);
		}

        $this->populateTags($raw_results);
		$converted = $this->convertRawToItems($raw_results);
		$this->mapPerItemData($converted);
		return $converted;
	}

	function filter_where($where = '')
	{
		return $where;
	}

	/**
	 * @param array $data
	 *
	 * @return RokSprocket_ItemCollection
	 */
	protected function convertRawToItems(array $data)
	{
		$collection = new RokSprocket_ItemCollection();
		$dborder    = 0;
		foreach ($data as $raw_item) {
			$item                              = $this->convertRawToItem($raw_item, $dborder);
			$collection[$item->getArticleId()] = $item;
			$dborder++;
		}
		return $collection;
	}

	/**
	 * @abstract
	 *
	 * @param     $raw_item
	 * @param int $dborder
	 */
	abstract protected function convertRawToItem($raw_item, $dborder = 0);

	/**
	 * @param RokSprocket_ItemCollection $items
	 *
	 * @throws RokSprocket_Exception
	 */
	protected function getModuleItemSettings(RokSprocket_ItemCollection &$items)
	{
		global $wpdb;
		//TODO move this to be a platform independent fucntion
		$item_ids = array_keys($items);
		$query    = '';
		$query .= 'SELECT rsi.provider_id as id, rsi.order as order, rsi.params as params';
		$query .= ' FROM #__roksprocket_items as rsi';
		$query .= ' WHERE ' . sprintf('rsi.widget_id = %d', $this->module_id);
		$query .= ' AND ' . sprintf('rsi.provider = %s', '"' . $this->provider_name . '"');
		$query .= ' AND ' . sprintf('rsi.provider_id in (%s)', implode(',', $item_ids));
		$query .= ' ORDER BY rsi.order';
		$query        = str_replace('#__', $wpdb->prefix, $query); //add wp db prefix
		$item_results = $wpdb->get_results($query, OBJECT_K);
		if ($item_results === false) {
			throw new RokSprocket_Exception($wpdb->last_error);
		}
		foreach ($item_results as $item_id => $item) {
			if (isset($items[$item_id])) {

			}
		}
	}

	/**
	 * @param RokSprocket_ItemCollection $items
	 *
	 * @throws RokSprocket_Exception
	 */
	protected function mapPerItemData(RokSprocket_ItemCollection &$items)
	{
		global $wpdb;
		$query = 'SELECT i.provider_id as id, i.order, i.params';
		$query .= ' FROM #__roksprocket_items as i';
		$query .= ' WHERE i.widget_id = "' . $this->module_id . '"';
		$query .= ' AND i.provider = "' . $this->provider_name . '"';
		$query          = str_replace('#__', $wpdb->prefix, $query); //add wp db prefix
		$sprocket_items = $wpdb->get_results($query, OBJECT_K);
		if ($sprocket_items === false) {
			throw new RokSprocket_Exception($wpdb->last_error);
		}

		/** @var $items RokSprocket_Item[] */
		foreach ($items as $item_id => &$item) {
			list($provider, $id) = explode('-', $item_id);
			if (array_key_exists($id, $sprocket_items)) {
				$items[$item_id]->setOrder((int)$sprocket_items[$id]->order);
				if (!is_null($sprocket_items[$id]->params)) {
					$items[$item_id]->setParams(RokCommon_Utils_ArrayHelper::fromObject(RokCommon_JSON::decode($sprocket_items[$id]->params)));
				} else {
					$items[$item_id]->setParams(array());

				}
			}
		}
	}

	/**
	 * @param $id
	 *
	 * @return \RokSprocket_Item
	 */
	public function getArticlePreview($id)
	{
		$ret = $this->getArticleInfo($id);
		$ret->setText($this->_cleanPreview($ret->getText()));
		return $ret;
	}

	/**
	 * @param      $id
	 *
	 * @param bool $raw return the raw object not the RokSprocket_Item
	 *
	 * @return stdClass|RokSprocket_Item
	 * @throws RokSprocket_Exception
	 */
	public function getArticleInfo($id, $raw = false)
	{
		global $wpdb;
		/** @var $filer_processor RokCommon_Filter_IProcessor */
		$filer_processor = $this->getFilterProcessor();
		$filer_processor->process(array('id' => array($id)), array(), true);
		$query = $filer_processor->getQuery();
		$ret   = $wpdb->get_results($query, OBJECT);
		$ret   = $ret[0];
		if ($ret === false) {
			throw new RokSprocket_Exception($wpdb->last_error);
		}

		if ($raw) {
			$ret->preview = $this->_cleanPreview($ret->introtext);
			$ret->editUrl = $this->getArticleEditUrl($id);
			return $ret;
		} else {
			$item          = $this->convertRawToItem($ret);
			$item->editUrl = $this->getArticleEditUrl($id);
			$item->preview = $this->_cleanPreview($item->getText());
			return $item;
		}
	}

	/**
	 * @abstract
	 *
	 * @param $id
	 */
	abstract protected function getArticleEditUrl($id);

	/**
	 * @param $content
	 *
	 * @return mixed
	 */
	protected function _cleanPreview($content)
	{
		//Replace src links
		$base = site_url();

		$regex   = '#href="index.php\?([^"]*)#m';
		$content = preg_replace_callback($regex, array('self', '_route'), $content);

		$protocols = '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$regex     = '#(src|href)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$content   = preg_replace($regex, "$1=\"$base\$2\" target=\"_blank\"", $content);

		$regex   = '#(onclick="window.open\(\')(?!/|' . $protocols . '|\#)([^/]+[^\']*?\')#m';
		$content = preg_replace($regex, '$1' . $base . '$2', $content);

		// ONMOUSEOVER / ONMOUSEOUT
		$regex   = '#(onmouseover|onmouseout)="this.src=([\']+)(?!/|' . $protocols . '|\#|\')([^"]+)"#m';
		$content = preg_replace($regex, '$1="this.src=$2' . $base . '$3$4"', $content);

		// Background image
		$regex   = '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|' . $protocols . '|\#)([^\)\'\"]+)[\'\"]?\)#m';
		$content = preg_replace($regex, 'style="$1: url(\'' . $base . '$2$3\')', $content);

		$content = strip_shortcodes($content);

		return $content;
	}

	/**
	 * @param $matches
	 *
	 * @return string
	 */
	protected function _route(&$matches)
	{
		$original = $matches[0];
		$url      = $matches[1];
		$url      = str_replace('&amp;', '&', $url);
		$route    = site_url() . $url;

		return 'target="_blank" href="' . $route;
	}

    /**
     * @param array $texts
     * @return array
     */
    protected function processPlugins($texts = array())
	{
        if(!is_admin()){

            global $shortcode_tags;
            $unset = false;

            //prevent continuous nesting of roksprocket shortcode
            //and we don't want to load a roksprocket widget inside of a roksprocket widget
            if(isset($shortcode_tags['roksprocket'])){
                $unset = $shortcode_tags['roksprocket'];
                unset($shortcode_tags['roksprocket']);
            }

            foreach ($texts as $k => $v) {

               	if (empty($shortcode_tags) || !is_array($shortcode_tags))
                    $texts[$k] = stripcslashes($v);

               	$pattern = get_shortcode_regex();
                $texts[$k] = preg_replace_callback( "/$pattern/s", 'do_shortcode_tag', stripcslashes($v) );
            }

            //put roksprocket back into shortcode
            if($unset){
                $shortcode_tags['roksprocket'] = $unset;
            }
        }
		return $texts;
	}
}
