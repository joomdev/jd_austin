<?php
/**
 * @version   $Id: AbstractAjaxRenderingLayoutModel.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_AbstractAjaxRenderingLayoutModel extends RokCommon_Ajax_AbstractModel
{
	/**
	 * @param  $action
	 * @param  $params
	 *
	 * @throws Exception
	 * @throws RokCommon_Ajax_Exception
	 * @return RokCommon_Ajax_Result
	 */
	public function run($action, $params)
	{
		$result  = parent::run($action, $params);
		$payload = $result->getPayload();
		if (isset($payload['html'])) {
			$container = RokCommon_Service::getContainer();
			/** @var $helper RokSprocket_PlatformHelper */
			$helper = $container->roksprocket_platformhelper;
			$payload['html'] = $helper->cleanup($payload['html']);
		}
		$result->setPayload($payload);
		return $result;
	}
}
