<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a ajax call to save products in an order
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendCatalogAjaxSaveShoppingCart extends FrontendBaseAJAXAction
{
	/**
	 * The order values
	 *
	 * @var	array
	 */
	private $orderValues;
	    
	/**
	 * Execute the order save
	 */
	public function execute()
	{
		parent::execute();
		
		// get order values
		$this->orderValues['product_id'] = SpoonFilter::getPostValue('productId', null, '');
		$this->orderValues['amount'] = SpoonFilter::getPostValue('productAmount', null, '');
				
		$action = SpoonFilter::getPostValue('action', null, '');
		
		// get cookie 
		$cookieOrderId = CommonCookie::get('order_id');
		
		// check if cookies are enabled
		$cookiesEnabled = CommonCookie::set('enabled', 'true');
		$cookieExists = CommonCookie::exists('enabled');
		
		// enabled
		//if ($cookieExists == true)
		//{
		//	die(print_r('cookies aan!'));
		//}
		//
		//// disabled
		//else
		//{
		//	die(print_r('cookies uit!'));
		//}
		
		
		// check if cookies are set, when true update the order
		if(isset($cookieOrderId) && FrontendCatalogModel::existsOrder($cookieOrderId) == true)
		{
			$this->orderValues['order_id'] = $cookieOrderId;
						
			// action add or update
			if($action == 'add-update')
			{
				if(FrontendCatalogModel::existsOrderValue($this->orderValues['product_id'], $this->orderValues['order_id']) == true)
				{
					// update the order values
					FrontendCatalogModel::updateOrderValue($this->orderValues, $this->orderValues['order_id'], $this->orderValues['product_id']);
					$this->output(self::OK, null, 'Order values updated.');
				} else
				{
					// insert order values
					FrontendCatalogModel::insertOrderValue($this->orderValues);
					$this->output(self::OK, null, 'Order values inserted.');
				}
			}
			
			// action delete
			else if ($action == 'delete')
			{
				if(FrontendCatalogModel::existsOrderValue($this->orderValues['product_id'], $this->orderValues['order_id']) == true)
				{
					// delete the order values
					FrontendCatalogModel::deleteOrderValue($this->orderValues['order_id'], $this->orderValues['product_id']);
					$this->output(self::OK, null, 'Order values deleted.');
				}
			}
		
		// when no cookies are set, create new cookie and insert order
		} else {
			$orderId = FrontendCatalogModel::insertOrder();
			
			if($orderId != '')
			{
			  // set order id
			  $this->orderValues['order_id'] = $orderId;
			  
			  // set cookie
			  CommonCookie::set('order_id', $orderId);
			  
			  // insert order values
			  FrontendCatalogModel::insertOrderValue($this->orderValues);
			  
			  $this->output(self::OK, null, 'Order imported.');	  
			}
		}    
	}
}
