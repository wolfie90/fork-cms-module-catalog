<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add action, it will display a form to add an image to a product.
 *
 * @author Bart De Clercq <info@lexxweb.be> 
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogAddImage extends BackendBaseActionAdd
{
    /**
     * The product id
     *
     * @var	int
     */
    private $id;

    /**
	 * The product record
	 *
	 * @var	array
	 */
	private $product;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('product_id', 'int');
		
		if($this->id !== null && BackendCatalogModel::exists($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
		
		// the product does not exist
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the necessary data
	 */
	private function getData()
	{
		$this->product = BackendCatalogModel::get($this->getParameter('product_id', 'int'));
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('addImage');
		$this->frm->addText('title');
		$this->frm->addImage('image');
	}

	/**
	 * Parses stuff into the template
	 */
	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('product', $this->product);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$image = $this->frm->getField('image');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			$image->isFilled(BL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build image record to insert
				$item['product_id'] = $this->product['id'];
				$item['title'] = $this->frm->getField('title')->getValue();

				// set files path for this record
				$path = FRONTEND_FILES_PATH . '/' . $this->module . '/' . $item['product_id'];

				// set formats
				$formats = array();
				$formats[] = array('size' => '64x64', 'allow_enlargement' => true, 'force_aspect_ratio' => false);
				$formats[] = array('size' => '128x128', 'allow_enlargement' => true, 'force_aspect_ratio' => false);
				$formats[] = array('size' => BackendModel::getModuleSetting($this->URL->getModule(), 'width1') . 'x' . BackendModel::getModuleSetting($this->URL->getModule(), 'height1'), 'allow_enlargement' => BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment1'), 'force_aspect_ratio' => BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio1'));
				$formats[] = array('size' => BackendModel::getModuleSetting($this->URL->getModule(), 'width2') . 'x' . BackendModel::getModuleSetting($this->URL->getModule(), 'height2'), 'allow_enlargement' => BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment2'), 'force_aspect_ratio' => BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio2'));
				$formats[] = array('size' => BackendModel::getModuleSetting($this->URL->getModule(), 'width3') . 'x' . BackendModel::getModuleSetting($this->URL->getModule(), 'height3'), 'allow_enlargement' => BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment3'), 'force_aspect_ratio' => BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio3'));

				// set the filename
				$item['filename'] = time() . '.' . $image->getExtension();
				$item['sequence'] = BackendCatalogModel::getMaximumImagesSequence($item['product_id'])+1;

				// add images
				BackendCatalogHelper::addImages($image, $path, $item['filename'], $formats);

				// save the item
				$item['id'] = BackendCatalogModel::saveImage($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_add_image', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(
					BackendModel::createURLForAction('media') . '&product_id=' . $item['product_id'] . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']
				);
			}
		}
	}
}
