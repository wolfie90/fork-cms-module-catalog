<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit-action, it will display a form to edit an existing item
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogEditOrder extends BackendBaseActionEdit
{
	/**
	 * DataGrids
	 *
	 * @var	BackendDataGridDB
	 */
    private $dgProducts;
  
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendCatalogModel::existsOrder($this->id))
		{
			parent::execute();
			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}

		// no item found, throw an exception, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data
	 * If a revision-id was specified in the URL we load the revision and not the actual data.
	 */
	private function getData()
	{
		// get the record
		$this->record = (array) BackendCatalogModel::getOrder($this->id);
        
		// datagrid for the products within an order
		$this->dgProducts = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_PRODUCTS_ORDER, array($this->id));

		// hide columns
		$this->dgProducts->setColumnsHidden('order_id', 'product_id', 'url', 'date');
			
		// set column URLs
		$this->dgProducts->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[product_id]');
        
		// no item found, throw an exceptions, because somebody is fucking with our URL
		if(empty($this->record)) $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('editOrder');

		// create elements
		$this->frm->addText('email', $this->record['email']);
		$this->frm->addText('fname', $this->record['fname']);
		$this->frm->addText('lname', $this->record['lname']);
		$this->frm->addText('address', $this->record['address']);
        $this->frm->addText('hnumber', $this->record['hnumber']);
        $this->frm->addText('postal', $this->record['postal']);
        $this->frm->addText('hometown', $this->record['hometown']);

		// assign URL
		//$this->tpl->assign('itemURL', BackendModel::getURLForBlock($this->getModule(), 'detail') . '/' . $this->record['product_url'] . '#order-' . $this->record['product_id']);
		
        $this->tpl->assign('products', $this->record);
		$this->tpl->assign('dgProducts', ($this->dgProducts->getNumResults() != 0) ? $this->dgProducts->getContent() : false);
        $this->tpl->assign('orderPerson', $this->record['fname']);
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
			$this->frm->getField('email')->isEmail(FL::err('EmailIsRequired'));
			$this->frm->getField('fname')->isFilled(BL::err('FirstNameIsRequired'));
			$this->frm->getField('lname')->isFilled(BL::err('LastNameIsRequired'));
			$this->frm->getField('address')->isFilled(BL::err('AddressIsRequired'));
			$this->frm->getField('hnumber')->isFilled(BL::err('HouseNumberIsRequired'));
			$this->frm->getField('postal')->isFilled(BL::err('PostalIsRequired'));
			$this->frm->getField('hometown')->isFilled(BL::err('HometownIsRequired'));
            
			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$order['id'] = $this->id;
				$order['email'] = $this->frm->getField('email')->getValue();
				$order['fname'] = $this->frm->getField('fname')->getValue();
				$order['lname'] = $this->frm->getField('lname')->getValue();
				$order['address'] = $this->frm->getField('address')->getValue();
				$order['hnumber'] = $this->frm->getField('hnumber')->getValue();
				$order['postal'] = $this->frm->getField('postal')->getValue();
				$order['hometown'] = $this->frm->getField('hometown')->getValue();
	
				// insert the item
				BackendCatalogModel::updateOrder($order);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_order', array('item' => $order));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('orders') . '&report=edited-order&id=' . $order['id'] . '&highlight=row-' . $order['id'] . '#tab' . SpoonFilter::toCamelCase($this->record['status']));
			}
		}
	}
}
