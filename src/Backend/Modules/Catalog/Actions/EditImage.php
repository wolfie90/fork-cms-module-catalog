<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
use Backend\Modules\Catalog\Engine\Helper as BackendCatalogHelper;

/**
 * This is the edit image action, it will display a form to edit an existing product image.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogEditImage extends BackendBaseActionEdit
{
	/**
	 * The product
	 *
	 * @var	array
	 */
	private $product;
    
	/**
	 * The image of a product
	 *
	 * @var	array
	 */
	private $image;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');
		if($this->id !== null && BackendCatalogModel::existsImage($this->id)) {
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
		// the item does not exist
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data
	 */
	protected function getData()
	{
		$this->product = BackendCatalogModel::get($this->getParameter('product_id', 'int'));
		$this->image = BackendCatalogModel::getImage($this->getParameter('id', 'int'));
		$this->image['data'] = unserialize($this->record['data']);
		$this->image['link'] = $this->record['data']['link'];
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		$this->frm = new BackendForm('editImage');
		$this->frm->addText('title', $this->image['title']);
		$this->frm->addImage('image');
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();
				
		$this->tpl->assign('product', $this->product);
		$this->tpl->assign('id', $this->id);
		$this->tpl->assign('item', $this->image);
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted()) {
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$image = $this->frm->getField('image');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			if($this->image['filename'] === null) $image->isFilled(BL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect()) {
				// build image record to insert
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['filename'] = $this->image['filename'];

				// set files path for this record
				$path = FRONTEND_FILES_PATH . '/' . $this->module . '/' . $this->product['id'];
				$formats = array();
				$formats[] = array('size' => '64x64', 'force_aspect_ratio' => false);
				$formats[] = array('size' => '128x128', 'force_aspect_ratio' => false);

				if($image->isFilled()) {
					// overwrite the filename
					if($item['filename'] === null) {
						$item['filename'] = time() . '.' . $image->getExtension();
					}

					// add images
					BackendCatalogHelper::addImages($image, $path, $item['filename'], $formats);
				}
				
				// save the item
				$id = BackendCatalogModel::saveImage($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_image', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&product_id=' . $this->product['id'] . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $id);
			}
		}
	}
}
