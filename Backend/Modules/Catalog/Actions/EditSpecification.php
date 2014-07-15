<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit specification-action, it will display a form to edit a specification
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogEditSpecification extends BackendBaseActionAdd
{
    /**
     * The specification id
     *
     * @var	array
     */
    private $id;

    /**
     * The specification record
     *
     * @var	array
     */
    private $record;

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
		
        if($this->id == null || !BackendCatalogModel::existsSpecification($this->id))
		{
			$this->redirect(
				BackendModel::createURLForAction('specifications') . '&error=non-existing'
			);
		}

		$this->record = BackendCatalogModel::getSpecification($this->id);
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		// assign the data
		$this->tpl->assign('item', $this->record);
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('editSpecification');

		$this->frm->addText('title', $this->record['title']);
		
		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
        $this->meta->setURLCallback('BackendCatalogModel', 'getURLForSpecification');
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
						
			$this->meta->validate();

			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['language'] = BL::getWorkingLanguage();
				$item['meta_id'] = $this->meta->save(true);
                
				// save the data
				BackendCatalogModel::updateSpecification($item['id'], $item);
				
				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_specification', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(
					BackendModel::createURLForAction('specifications') . '&report=edited-specification&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']
				);
			}
		}
	}    
}
