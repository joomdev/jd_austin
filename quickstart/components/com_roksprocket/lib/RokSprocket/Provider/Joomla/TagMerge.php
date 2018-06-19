<?php

/**
 * @version   $Id: TagMerge.php 19581 2014-03-10 22:02:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokSprocket_Provider_Joomla_TagMerge implements RokSprocket_Provider_Joomla_ITagMerge
{
	/**
	 * @param array $items
	 *
	 * @throws RokSprocket_Exception
	 */
	public function populateTags(array $items)
	{
		if (!empty($items)) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('ct.content_item_id as article_id, t.title as tag');
			$query->from('#__contentitem_tag_map as ct');
			$query->join('LEFT', '#__tags AS t ON t.id = ct.tag_id');
			$query->where(sprintf("ct.content_item_id in (%s)",implode(',',array_keys($items))));
			$db->setQuery($query);
			$results = $db->loadAssocList();
			if ($error = $db->getErrorMsg()) {
				throw new RokSprocket_Exception($error);
			}
			foreach($results as $result)
			{
				$items[$result['article_id']]->tags[]=$result['tag'];
			}
		}
	}

}
 