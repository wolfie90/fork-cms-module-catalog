<?php

namespace Frontend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

/**
 * This is the checkout-action (default), it will display the overview of the shopping cart
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Checkout extends FrontendBaseBlock
{
	/**
	 * The order id
	 *
	 * @var	int
	 */
	private $orderId;
	
	/**
	 * The total amount (price) of the order
	 *
	 * @var	int
	 */
	private $totalPrice;
	
	/**
	 * The products within an order
	 *
	 * @var	array
	 */
	private $products;
	
	/**
	 * The url for overview-page shopping-cart
	 *
	 * @var	string
	 */
	private $personalDataUrl;
	
	/**
	 * The url for catalog page
	 *
	 * @var	string
	 */
	private $catalogUrl;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadTemplate();
		$this->getData();
		$this->parse();
	}

	/**
	 * Load the data, don't forget to validate the incoming data
	 */
	private function getData()
	{
		// get cookie
		$this->orderId = CommonCookie::get('order_id');
		
		if($this->orderId)
		{
			// get the products
			$this->products = FrontendCatalogModel::getProductsByOrder($this->orderId);
			
			// total price
			$this->totalPrice = '0';
			
			// calculate total amount
			foreach($this->products as &$product)
			{
				// calculate total
				$subtotal = (int)$product['subtotal_price'];
				$this->totalPrice = (int)$this->totalPrice;
				$this->totalPrice = $this->totalPrice + $subtotal;
			}
		}
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// add css
		$this->header->addCSS('/Frontend/Modules/' . $this->getModule() . '/Layout/Css/catalog.css');
		
		// add noty js
		$this->header->addJS('/Frontend/Modules/' . $this->getModule() . '/Js/noty/packaged/jquery.noty.packaged.min.js');
		
		// url for next step
		$this->personalDataUrl = FrontendNavigation::getURLForBlock('catalog', 'personal_data');
		$this->catalogUrl = FrontendNavigation::getURLForBlock('catalog');
			  
		if(!empty($this->products)) $this->tpl->assign('productsInShoppingCart', $this->products);
		if(!empty($this->totalPrice)) $this->tpl->assign('totalPrice', $this->totalPrice);

		$this->tpl->assign('personalDataUrl', $this->personalDataUrl);
		$this->tpl->assign('catalogUrl', $this->catalogUrl);
	}
}
