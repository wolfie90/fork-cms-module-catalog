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
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
 
/**
 * This is the specifications action, it will display the overview of specifications
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Specifications extends BackendBaseActionIndex
{
    /**
     * DataGrid
     *
     * @var	BackendDataGridDB
     */
    protected $dataGrid;

    /**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();		
		$this->loadDataGrid();

		$this->parse();
		$this->display();
	}
	
	/**
	 * Load the dataGrid
	 */
	private function loadDataGrid()
	{
		$this->dataGrid = new BackendDataGridDB(
			BackendCatalogModel::QRY_DATAGRID_BROWSE_SPECIFICATIONS,
			BL::getWorkingLanguage()
		);
	
		// check if this action is allowed
		if(BackendAuthentication::isAllowedAction('EditSpecification')) {
			$this->dataGrid->setColumnURL('specification', BackendModel::createURLForAction('edit_specification') . '&amp;id=[id]');
			
			$this->dataGrid->addColumn(
				'edit', null, BL::lbl('Edit'),
				BackendModel::createURLForAction('edit_specification') . '&amp;id=[id]',
				BL::lbl('Edit')
			);
		}

		// sequence
		$this->dataGrid->enableSequenceByDragAndDrop();
		$this->dataGrid->setAttributes(array('data-action' => 'sequence_specifications'));
	}
	
	/**
	 * Parse & display the page
	 */
	protected function parse()
	{
		$this->tpl->assign('dataGrid', (string) $this->dataGrid->getContent());
	}
}