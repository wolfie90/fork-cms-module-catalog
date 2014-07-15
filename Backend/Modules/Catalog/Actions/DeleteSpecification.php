<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action will delete a specification
 *
 * @author Tijs Verkoyen <tijs@verkoyen.eu>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogDeleteSpecification extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id == null || !BackendCatalogModel::existsSpecification($this->id))
		{
			$this->redirect(
				BackendModel::createURLForAction('specifications') . '&error=non-existing'
			);
		}

		// fetch the specification
		$this->record = (array) BackendCatalogModel::getSpecification($this->id);

		// delete item
		BackendCatalogModel::deleteSpecification($this->id);
		
		// trigger event
		BackendModel::triggerEvent($this->getModule(), 'after_delete_specification', array('item' => $this->record));

		// specification was deleted, so redirect
		$this->redirect(
			BackendModel::createURLForAction('specifications') . '&report=deleted-specification&var=' . urlencode($this->record['title'])
		);
	}
}
