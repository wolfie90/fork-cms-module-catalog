<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit file action, it will display a form to edit an existing product file.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogEditFile extends BackendBaseActionEdit
{
    /**
     * The allowed file extensions
     *
     * @var	array
     *
     */
    private $allowedExtensions = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pps', 'ppsx', 'zip');

    /**
     * The product
     *
     * @var	array
     */
    private $product;

    /**
     * The file of a product
     *
     * @var	array
     */
    private $file;

    /**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');
		
		if($this->id !== null && BackendCatalogModel::existsFile($this->id))
		{
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
		$this->file = BackendCatalogModel::getFile($this->getParameter('id', 'int'));
		$this->file['data'] = unserialize($this->record['data']);
		$this->file['link'] = $this->record['data']['link'];

	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		$this->frm = new BackendForm('editFile');
		$this->frm->addText('title', $this->file['title']);
		$this->frm->addFile('file');
        $this->frm->getField('file')->setAttribute('extension', implode(', ', $this->allowedExtensions));
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('id', $this->id);
		$this->tpl->assign('item', $this->file);
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
            $file = $this->frm->getField('file');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			if($this->file['filename'] === null) $file->isFilled(BL::err('FieldIsRequired'));

            // validate the file
            if($this->frm->getField('file')->isFilled())
            {
                // file extension
                $this->frm->getField('file')->isAllowedExtension($this->allowedExtensions, BL::err('FileExtensionNotAllowed'));
            }

            // no errors?
			if($this->frm->isCorrect())
			{
				// build image record to insert
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['filename'] = $this->file['filename'];
								
				// the file path
				$filePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/' . $this->project['id'] . '/source';
				
				if($file->isFilled())
				{
					// overwrite the filename
					if($item['filename'] === null)
					{
						$item['filename'] = time() . '.' . $file->getExtension();
					}

					$file->moveFile($filePath . '/' . $item['filename']);
				}
				
				// save the item
				$id = BackendCatalogModel::saveFile($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_file', array('item' => $item));
								
				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&product_id=' . $this->product['id'] . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $id . '#tabFiles');
			}
		}
	}
}
