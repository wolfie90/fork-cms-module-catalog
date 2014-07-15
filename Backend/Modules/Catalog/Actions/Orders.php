<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the orders-action , it will display the overview of orders
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogOrders extends BackendBaseActionIndex
{
	/**
	 * DataGrids
	 *
	 * @var	BackendDataGridDB
	 */
	private $dgModeration, $dgCompleted;

	/**
	 * Add productdata into the order
	 *
	 * @param  string $text The order.
	 * @param string $title The title for the product.
	 * @param string $URL The URL for the product.
	 * @param int $id The id of the order.
	 * @return string
	 */
	public static function addProductData($text, $title, $URL, $id)
	{
		// reset URL
		$URL = BackendModel::getURLForBlock('catalog', 'detail') . '/' . $URL . '#order-' . $id;

		// build HTML
		return '<p><em>' . sprintf(BL::msg('OrderOnWithURL'), $URL, $title) . '</em></p>' . "\n" . (string) $text;
	}

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadDataGrids();
		$this->parse();
		$this->display();
	}

	/**
	 * Loads the datagrids
	 */
	private function loadDataGrids()
	{
		/*
		 * DataGrid for the orders that are awaiting moderation.
		 */
		$this->dgModeration = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_ORDERS, array('moderation'));

		// active tab
		$this->dgModeration->setActiveTab('tabModeration');

		// num items per page
		$this->dgModeration->setPagingLimit(30);

		// header labels
		$this->dgModeration->setHeaderLabels(array('ordered_on' => SpoonFilter::ucfirst(BL::lbl('Date'))));

		// add the multicheckbox column
		$this->dgModeration->setMassActionCheckboxes('checkbox', '[id]');

		// assign column functions
		$this->dgModeration->setColumnFunction(array('BackendDataGridFunctions', 'getTimeAgo'), '[ordered_on]', 'ordered_on', true);

		// sorting
		$this->dgModeration->setSortingColumns(array('ordered_on', 'order_nr'), 'ordered_on');
		$this->dgModeration->setSortParameter('desc');

		// hide columns
		$this->dgModeration->setColumnsHidden('status');

		// add mass action dropdown
		$ddmMassAction = new SpoonFormDropdown('action', array('completed' => BL::lbl('MoveToCompleted'), 'delete' => BL::lbl('Delete')), 'completed');
		$ddmMassAction->setAttribute('id', 'actionModeration');
		$ddmMassAction->setOptionAttributes('delete', array('data-message-id' => 'confirmDeleteModeration'));
		$ddmMassAction->setOptionAttributes('completed', array('data-message-id' => 'confirmCompletedModeration'));
		$this->dgModeration->setMassAction($ddmMassAction);
		
		// check if this action is allowed
		if(BackendAuthentication::isAllowedAction('edit_order'))
		{
			$this->dgModeration->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_order') . '&amp;id=[id]', BL::lbl('Edit'));
		}

		// check if this action is allowed
		if(BackendAuthentication::isAllowedAction('mass_order_action'))
		{
			$this->dgModeration->addColumn('approve', null, BL::lbl('Approve'), BackendModel::createURLForAction('mass_order_action') . '&amp;id=[id]&amp;from=moderation&amp;action=completed', BL::lbl('Approve'));
		}

		/*
		 * DataGrid for the orders that are marked as completed
		 */
		$this->dgCompleted = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_ORDERS, array('completed'));

		// active tab
		$this->dgCompleted->setActiveTab('tabCompleted');

		// num items per page
		$this->dgCompleted->setPagingLimit(30);

		// header labels
		$this->dgCompleted->setHeaderLabels(array('ordered_on' => SpoonFilter::ucfirst(BL::lbl('Date'))));

		// add the multicheckbox column
		$this->dgCompleted->setMassActionCheckboxes('checkbox', '[id]');

		// assign column functions
		$this->dgCompleted->setColumnFunction(array('BackendDataGridFunctions', 'getTimeAgo'), '[ordered_on]', 'ordered_on', true);

		// sorting
		$this->dgCompleted->setSortingColumns(array('ordered_on'), 'ordered_on');
		$this->dgCompleted->setSortParameter('desc');

		// hide columns
		$this->dgCompleted->setColumnsHidden('status');

		// add mass action dropdown
		$ddmMassAction = new SpoonFormDropdown('action', array('moderation' => BL::lbl('MoveToModeration'), 'delete' => BL::lbl('Delete')), 'moderation');
		$ddmMassAction->setAttribute('id', 'actionCompleted');
		$ddmMassAction->setOptionAttributes('delete', array('data-message-id' => 'confirmDeleteCompleted'));
		
		$this->dgCompleted->setMassAction($ddmMassAction);
	}

	/**
	 * Parse & display the page
	 */
	protected function parse()
	{
		parent::parse();

		// moderation datagrid and num results
		$this->tpl->assign('dgModeration', ($this->dgModeration->getNumResults() != 0) ? $this->dgModeration->getContent() : false);
		$this->tpl->assign('numModeration', $this->dgModeration->getNumResults());

		// spam datagrid and num results
		$this->tpl->assign('dgCompleted', ($this->dgCompleted->getNumResults() != 0) ? $this->dgCompleted->getContent() : false);
		$this->tpl->assign('numCompleted', $this->dgCompleted->getNumResults());
	}
}
