<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;

/**
 * This is the edit brand action, it will display a form to edit an existing brand.
 *
 * @author Waldo Cosman <waldo@comsa.be>
 */
class EditBrand extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{

		parent::execute();

		$this->getData();
		$this->loadForm();
		$this->validateForm();


		$this->parse();
		$this->display();
	}

	/**
	 * Get the data
	 */
	private function getData()
	{
		$this->id = $this->getParameter('id', 'int');
		if($this->id == null || !BackendCatalogModel::existsBrand($this->id))
		{
			$this->redirect(BackendModel::createURLForAction('brands') . '&error=non-existing');
		}

		$this->record = BackendCatalogModel::getBrand($this->id);
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('editBrand');
		$this->frm->addText('title', $this->record['title']);
		$this->frm->addImage('image');

		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
		$this->meta->setUrlCallback('Backend\Modules\Catalog\Engine\Model', 'getURLForBrand', array($this->record['id']));
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		// assign the data
		$this->tpl->assign('item', $this->record);

		// is brand allowed to be deleted?
		if(BackendCatalogModel::isBrandAllowedToBeDeleted($this->id)) $this->tpl->assign('showDelete', true);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));

			if($this->frm->getField('image')->isFilled())
			{
				$this->frm->getField('image')->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly'));
				$this->frm->getField('image')->isAllowedMimeType(array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'), BL::err('JPGGIFAndPNGOnly'));
			}

			$this->meta->validate();

			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['meta_id'] = $this->meta->save(true);

				// the image path
				$imagePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/catalog_brands/' . $this->id;

				// create folders if needed
				$fs = new Filesystem();

				if(!$fs->exists($imagePath . '/150x150/'))
				{
					$fs->mkdir($imagePath . '/150x150/');
				}

				if(!$fs->exists($imagePath . '/source/'))
				{
					$fs->mkdir($imagePath . '/source/');
				}

				// image provided?
				if($this->frm->getField('image')->isFilled())
				{
					// build the image name
					$item['image'] = $this->meta->getUrl() . '.' . $this->frm->getField('image')->getExtension();

					// upload the image & generate thumbnails
					$this->frm->getField('image')->generateThumbnails($imagePath, $item['image']);
				}

				// update the item
				BackendCatalogModel::updateBrand($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_brand', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('brands') . '&report=edited-brand&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
