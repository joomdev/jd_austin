<?php
/**
 * @version   $Id: Edit.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocketAdminAjaxModelEdit extends RokCommon_Ajax_AbstractModel
{
	public function save($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$container = RokCommon_Service::getContainer();
			/** @var $platforminfo RokCommon_IPlatformInfo */
			$platforminfo = $container->getService('platforminfo');
			/** @var $model RokSprocket_Model_Edit */
			$model = $container->getService('roksprocket.edit.model');
			if (($id = $model->save($params)) === false) {
				throw new Exception($model->getLastError());
			}
			if ($params['id'] == 0) {
				$result->setPayload(array('redirect' => sprintf($platforminfo->getRootUrl() . '/wp-admin/admin.php?page=roksprocket-edit&id=%d', $id)));
			} else {
				$result->setPayload(array('redirect'     => null));
			}
			return $result;
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function saveascopy($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$container = RokCommon_Service::getContainer();
			/** @var $platforminfo RokCommon_IPlatformInfo */
			$platforminfo = $container->getService('platforminfo');
			/** @var $model RokSprocket_Model_Edit */
			$model           = $container->getService('roksprocket.edit.model');
			$copyied_id = $params['id'];
			$params['id']    = 0;
			$params['title'] = rc__('ROKSPROCKET_WIDGET_COPY', $params['title']);
			if (($id = $model->save($params,$copyied_id)) === false) {
				throw new Exception($model->getLastError());
			}
			$result->setPayload(array('redirect' => sprintf($platforminfo->getRootUrl() . '/wp-admin/admin.php?page=roksprocket-edit&id=%d', $id)));
			return $result;
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function saveandnew($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$container = RokCommon_Service::getContainer();
			/** @var $platforminfo RokCommon_IPlatformInfo */
			$platforminfo = $container->getService('platforminfo');
			/** @var $model RokSprocket_Model_Edit */
			$model = $container->getService('roksprocket.edit.model');
			if (($id = $model->save($params)) === false) {
				throw new Exception($model->getLastError());
			}
			$result->setPayload(array('redirect' => sprintf($platforminfo->getRootUrl() . '/wp-admin/admin.php?page=roksprocket-edit&id=%d', 0)));
			return $result;
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function saveandclose($params)
	{
		$result = new RokCommon_Ajax_Result();
		try {
			$container = RokCommon_Service::getContainer();
			/** @var $platforminfo RokCommon_IPlatformInfo */
			$platforminfo = $container->getService('platforminfo');
			/** @var $model RokSprocket_Model_Edit */
			$model = $container->getService('roksprocket.edit.model');
			if (($id = $model->save($params)) === false) {
				throw new Exception($model->getLastError());
			}
			$result->setPayload(array('redirect' => $platforminfo->getRootUrl() . '/wp-admin/admin.php?page=roksprocket-list'));
			return $result;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
