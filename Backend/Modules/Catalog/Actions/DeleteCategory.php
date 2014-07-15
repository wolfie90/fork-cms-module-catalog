<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action will delete a category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogDeleteCategory extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id == null || !BackendCatalogModel::existsCategory($this->id))
		{
			$this->redirect(
				BackendModel::createURLForAction('categories') . '&error=non-existing'
			);
		}

		// fetch the category
		$this->record = (array) BackendCatalogModel::getCategory($this->id);

		// delete item
		BackendCatalogModel::deleteCategory($this->id);
		
		// trigger event
		BackendModel::triggerEvent(
			$this->getModule(),
			'after_delete_category',
			array('item' => $this->record)
		);

		// category was deleted, so redirect
		$this->redirect(
			BackendModel::createURLForAction('categories') . '&report=deleted-category&var=' . urlencode($this->record['title'])
		);
	}
}
