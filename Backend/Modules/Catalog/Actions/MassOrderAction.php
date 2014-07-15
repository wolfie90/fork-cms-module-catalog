<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action is used to perform mass actions on orders (delete, ...)
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogMassOrderAction extends BackendBaseAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// current status
		$from = SpoonFilter::getGetValue('from', array('moderation', 'completed'), 'moderation');

		// action to execute
		$action = SpoonFilter::getGetValue('action', array('moderation', 'completed', 'delete'), 'completed');

		// no id's provided
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('orders') . '&error=no-orders-selected');

		// redefine id's
		$ids = (array) $_GET['id'];

		// delete comment(s)
		if($action == 'delete') BackendCatalogModel::deleteOrders($ids);
		if($action == 'completed')BackendCatalogModel::updateOrderStatuses($ids, $action);
		if($action == 'moderation')BackendCatalogModel::updateOrderStatuses($ids, $action);

		// define report
		$report = (count($ids) > 1) ? 'orders-' : 'order-';

		// init var
		if($action == 'moderation') $report .= 'moved-moderation';
		if($action == 'completed') $report .= 'moved-completed';
		if($action == 'delete') $report .= 'deleted';

		// redirect
		$this->redirect(BackendModel::createURLForAction('orders') . '&report=' . $report . '#tab' . SpoonFilter::ucfirst($from));
	}
}
