<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action will delete a comment
 *
 * @author Tijs Verkoyen <tijs@verkoyen.eu>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogDeleteCompleted extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		BackendCatalogModel::deleteCompletedOrders();

		// item was deleted, so redirect
		$this->redirect(BackendModel::createURLForAction('orders') . '&report=deleted-completed#tabCompleted');
	}
}
