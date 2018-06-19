<?php
/**
 * @version   $Id: Zoo.php 18577 2014-02-07 00:58:21Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Zoo extends RokSprocket_Provider_AbstarctZooBasedProvider
{
	protected static $available;

	/**
	 * @param array $filters
	 * @param array $sort_filters
	 */
	public function __construct($filters = array(), $sort_filters = array())
	{
		parent::__construct('zoo');
		require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');
		$this->setFilterChoices($filters, $sort_filters);
	}

	/**
	 * @static
	 * @return bool
	 */
	public static function isAvailable()
	{
		if (isset(self::$available)) {
			return self::$available;
		}

		if (!class_exists('JFactory')) {
			self::$available = false;
		} else {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.extension_id');
			$query->from('#__extensions AS a');
			$query->where('a.type = "component"');
			$query->where('a.element = "com_zoo"');
			$query->where('a.enabled = 1');

			$db->setQuery($query);

			if ($db->loadResult()) {
				self::$available = true;
			} else {
				self::$available = false;
			}
		}
		return self::$available;

	}


	/**
	 * @param     $raw_item
	 * @param int $dborder
	 *
	 * @return \RokSprocket_Item
	 */
	protected function convertRawToItem($raw_item, $dborder = 0)
	{
		/** @var Item $raw_item*/
		$app_type = $this->params->get('zoo_application_type');
		//$textfield = $this->params->get('zoo_articletext_field', '');

		$item = new RokSprocket_Item();

		$item->setProvider($this->provider_name);
		$item->setId($raw_item->id);
		$item->setAlias(($raw_item->created_by_alias) ? $raw_item->created_by_alias : JFactory::getUser($raw_item->created_by)->name);
		$item->setAuthor(JFactory::getUser($raw_item->created_by)->name);
		$item->setTitle($raw_item->name);
		$item->setDate($raw_item->created);
		$item->setPublished(($raw_item->state == 1) ? true : false);
		$category = $raw_item->getPrimaryCategory();
		if ($category) $item->setCategory($category->name);
		$item->setHits($raw_item->hits);

		//$this->query->select('ROUND(AVG(v.value), 0) AS rating');
		//$this->query->join('LEFT', '#__zoo_rating AS v ON a.id = v.item_id');


		$item->setMetaKey('');
		$item->setMetaDesc('');
		$item->setMetaData('');

		$texts  = array();
		$images = array();
		$links  = array();

		/** @var Element[] $elements */
		$elements = $raw_item->getElements();
		$rating = 0;

		foreach ($elements as $element) {
			/** @var RokSprocket_Provider_Zoo_FieldProcessorInterface $processor */
			$processor     = RokSprocket_Provider_Zoo_FieldProcessorFactory::getFieldProcessor($element->getElementType());
			$sprocket_type = RokSprocket_Provider_Zoo_FieldProcessorFactory::getSprocketType($element->getElementType());
			switch ($sprocket_type) {
				case 'image':
					if ($processor instanceof RokSprocket_Provider_Zoo_ImageFieldProcessorInterface) {
						/** @var RokSprocket_Provider_Zoo_ImageFieldProcessorInterface $processor */
						$image                           = $processor->getAsSprocketImage($element);
						$images[$image->getIdentifier()] = $image;
						if (isset($images['image_field_' . $element->identifier]) && !$item->getPrimaryImage()) {
							$item->setPrimaryImage($image);
						}
					}
					break;
				case 'link':
					if ($processor instanceof RokSprocket_Provider_Zoo_LinkFieldProcessorInterface) {
						/** @var RokSprocket_Provider_Zoo_LinkFieldProcessorInterface $processor */
						$link                          = $processor->getAsSprocketLink($element);
						$links[$link->getIdentifier()] = $link;
						if (isset($links['link_field_' . $element->identifier]) && !$item->getPrimaryLink()) {
							$item->setPrimaryLink($link);
						}
					}
					break;
				case 'text':
					/** @var RokSprocket_Provider_Zoo_FieldProcessorInterface $processor */
					$texts['text_field_' . $element->identifier] = $processor->getValue($element);
					break;
				default:
					break;
			}
			if ($element->getElementType() == 'rating')
			{
				$rating = $element->getRating();
			}
		}
		$item->setRating($rating);
		$item->setImages($images);
		$item->setLinks($links);

		$params                       = RokCommon_JSON::decode($raw_item->params);
		$desc                         = "metadata.description";
		$texts['text_field_metadesc'] = $params->$desc;
		$texts['text_field_name']     = $raw_item->name;
		$texts                        = $this->processPlugins($texts);
		$item->setTextFields($texts);
		$text = array_values($texts);
		$text = array_shift($text);
		$item->setText($text);


		$primary_link = new RokSprocket_Item_Link();
		$primary_link->setUrl(JRoute::_('index.php?option=com_zoo&task=item&item_id=' . $raw_item->id, true));
		$primary_link->getIdentifier('article_link');

		$item->setPrimaryLink($primary_link);

		$item->setCommentCount(count($raw_item->getComments()));
		//$tags = (explode(',', $raw_item->tags)) ? explode(',', $raw_item->tags) : array();
		$tags = $raw_item->getTags();
		$item->setTags($tags);

		$item->setDbOrder($dborder);

		return $item;
	}

}

