<?php
/**
 * @version   $Id: Unsupported.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Unsupported implements RokSprocket_IProvider
{
    /**
     * @static
     * @return bool
     */
    public static function isAvailable()
    {
        return false;
    }

	/**
	 *
	 * @return RokSprocket_ItemCollection
	 */
	public function getItems()
	{
		// TODO: Implement getItems() method.
	}

	/**
	 *
	 * @param array $filters
	 * @param array $sort_filters
	 */
	public function setFilterChoices($filters, $sort_filters)
	{
		// TODO: Implement setFilterChoices() method.
	}

	/**
	 *
	 * @param $id
	 */
	public function setModuleId($id)
	{
		// TODO: Implement setModuleId() method.
	}

    /**
     *
     * @param $ids
     */
    public function setDisplayedIds($ids)
    {
        // TODO: Implement setModuleId() method.
    }

	/**
	 *
	 */
	public function getFilterProcessor()
	{
		// TODO: Implement getFilterProcessor() method.
	}

	/**
	 *
	 * @param $id
	 *
	 * @return RokSprocket_Item
	 */
	public function getArticleInfo($id)
	{
		// TODO: Implement getArticleInfo() method.
	}

	/**
	 *
	 * @param $id
	 *
	 * @return RokSprocket_Item
	 */
	public function getArticlePreview($id)
	{
		// TODO: Implement getArticlePreview() method.
	}

	/**
	 *
	 * @param       $method
	 * @param array $options
	 */
	public function setSortInfo($method, array $options = array())
	{
		// TODO: Implement setSortInfo() method.
	}


	/**
	 * @return array the array of image type and label
	 */
	public function getImageTypes()
	{
		// TODO: Implement getImageTypes() method.
	}
}
