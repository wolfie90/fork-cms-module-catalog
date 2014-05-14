<?php
/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add action, it will display a form to add an file to a product.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogAddFile extends BackendBaseActionAdd
{
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
		$this->frm = new BackendForm('addFile');
		$this->frm->addText('title');
		$this->frm->addFile('file');
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
			$file = $this->frm->getField('file');
            
			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			$file->isFilled(BL::err('FieldIsRequired'));
			            
			// no errors?
			if($this->frm->isCorrect())
			{
				// build file record to insert
				$item['product_id'] = $this->product['id'];
				$item['title'] = $this->frm->getField('title')->getValue();

				// the file path
				$filePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/' . $item['product_id'] . '/source';
                
				// create folders if needed
				if(!SpoonDirectory::exists($filePath)) SpoonDirectory::create($filePath);

				// file provided?
				if($file->isFilled())
				{
					// build the file name
					$item['filename'] = time() . '.' . $file->getExtension();

					// upload the file
					$file->moveFile($filePath . '/' . $item['filename']);
				}
                
				$item['sequence'] = BackendCatalogModel::getMaximumFilesSequence($item['product_id'])+1;

				// insert it
				$item['id'] = BackendCatalogModel::saveFile($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_add_file', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&product_id=' . $item['product_id'] . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id'] . '#tabFiles');
			}
		}
	}
}
