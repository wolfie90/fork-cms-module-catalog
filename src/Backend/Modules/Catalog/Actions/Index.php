<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
 
/**
 * This is the index-action (default), it will display the overview of products
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Index extends BackendBaseActionIndex
{
	/**
	 * The category where is filtered on
	 *
	 * @var	array
	 */
	private $category;

	/**
	 * The id of the category where is filtered on
	 *
	 * @var	int
	 */
	private $categoryId;

	/**
	 * DataGrids
	 *
	 * @var	SpoonDataGrid
	 */
	private $dgProducts;
	
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		
		$this->categoryId = \SpoonFilter::getGetValue('category', null, null, 'int');
		if($this->categoryId == 0) $this->categoryId = null;
		else {
			// get category
			$this->category = BackendCatalogModel::getCategory($this->categoryId);
						
			// reset
			if(empty($this->category)) {
				// reset GET to trick Spoon
				$_GET['category'] = null;

				// reset
				$this->categoryId = null;
			}
		}
		
		$this->loadDataGrid();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the dataGrid
	 */
	private function loadDataGrid()
	{
		// filter category
		if($this->categoryId != null ) {			
			// create datagrid
			$this->dgProducts = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_FOR_CATEGORY, array($this->categoryId, BL::getWorkingLanguage()));
			
			// set the URL
			$this->dgProducts->setURL('&amp;category=' . $this->categoryId, true);
		} else {
			// dont filter category
			// create datagrid
			$this->dgProducts = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE, array(BL::getWorkingLanguage()));
		}

		// our JS needs to know an id, so we can highlight it
		$this->dgProducts->setRowAttributes(array('id' => 'row-[id]'));
		$this->dgProducts->setColumnsHidden(array('category_id', 'sequence'));
		
		// check if this action is allowed
		if(BackendAuthentication::isAllowedAction('edit')) {			
			// set column URLs
			$this->dgProducts->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]&amp;category=' . $this->categoryId);

			// add edit and media column
			$this->dgProducts->addColumn('media', null, BL::lbl('Media'), BackendModel::createURLForAction('media') . '&amp;id=[id]', BL::lbl('Media'));
			$this->dgProducts->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]&amp;category=' . $this->categoryId, BL::lbl('Edit'));
		
			// set media column function
			$this->dgProducts->setColumnFunction(array(__CLASS__, 'setMediaLink'), array('[id]'), 'media');
		}
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{		
		// parse the datagrid for all products
		$this->tpl->assign('dgProducts', ($this->dgProducts->getNumResults() != 0) ? $this->dgProducts->getContent() : false);
	
		// get categories
		$categories = BackendCatalogModel::getCategories(true);
				
		// multiple categories?
		if(count($categories) > 1) {
			// create form
			$frm = new BackendForm('filter', null, 'get', true);

			// create element
			$frm->addDropdown('category', $categories, $this->categoryId);
			$frm->getField('category')->setDefaultElement('');
			
			// parse the form
			$frm->parse($this->tpl);
		}

		// parse category
		if(!empty($this->category)) $this->tpl->assign('filterCategory', $this->category);	
	}
	
	/**
	 * Sets a link to the media overview
	 *
	 * @param int $productId The specific id of the product
	 * @return string
	 */
	public static function setMediaLink($productId)
	{
		return '<a class="button icon iconEdit linkButton" href="' . BackendModel::createURLForAction('media') . '&product_id=' . $productId . '">
					<span>' . BL::lbl('ManageMedia') . '</span>
				</a>';
	}
}