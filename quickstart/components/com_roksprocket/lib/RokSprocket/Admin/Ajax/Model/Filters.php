<?php
/**
 * @version   $Id: Filters.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocketAdminAjaxModelFilters extends RokCommon_Ajax_AbstractModel
{
	/**
	 * Delete the file and all associated rows (done by foreign keys) and files
	 * $params object should be a json like
	 * <code>
	 * {
	 *  "pathsref": "roksprocket.providers.registered.joomla.path",
	 *  "file": "filters.xml"
	 * }
	 * </code>
	 *
	 * @param $params
	 *
	 * @throws #C\Exception|?
	 * @throws RokSprocket_Exception
	 * @return \RokCommon_Ajax_Result
	 *
	 *
	 */
	public function getFilters($params)
	{

	/*
            After everything fine, i'll handle via js and domready the call to filters ajax model
            Something along these lines:

                model: 'Filters',
                action: 'getData',
                params: JSON.encoded(
                    [{
                        id1: {pathrefs: .., file: ..}
                    }],
                    [{
                        id2: {pathrefs: .., file: ..}
                    }],
                    [{
                        id3: {pathrefs: .., file: ..}
                    }],
                    ...
                )
        */


		$result = new RokCommon_Ajax_Result();
		try {
			$container = RokCommon_Service::getContainer();
			$filters = $params->filters;
			$output = array();

			foreach($filters as $filterid => $filter){
				$filter_file = $container[$filter->pathsref] . '/' . $filter->file;

				if (!file_exists($filter_file)) {
					throw new RokSprocket_Exception(rc__('Unable to find filter file %s', $filter_file));
				}
				$xmlfile = simplexml_load_file($filter_file);
				$outfilter = new RokCommon_Filter($xmlfile);
				$output[$filterid] = $outfilter->getJson();
			}
			$result->setPayload(array('json' =>$output));
		} catch (Exception $e) {
			throw $e;
		}
		return $result;
	}
}
