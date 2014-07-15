<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a ajax call to update the view of the shopping cart
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendCatalogAjaxUpdateShoppingCart extends FrontendBaseAJAXAction
{
	/**
	 * The order id
	 *
	 * @var	int
	 */
	private $orderId;
	
	/**
	 * The amount of products in the shopping cart
	 *
	 * @var	int
	 */
	private $amountOfProducts;	
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
	private $overviewUrl;
	
	/**
	 * The total price
	 *
	 * @var	array
	 */
	private $totalPriceArr;
	
	/**
	 * Are cookies enabled?
	 *
	 * @var	string
	 */
	private $cookiesEnabled;
		
	/**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

	$this->getData();
        $this->loadTemplate();
        $this->display();
    }

	/**
	 * Get the data
	 */
	private function getData(){
		// get cookie
		$this->orderId = CommonCookie::get('order_id');
		
		// check if cookies are available
		$cookie = CommonCookie::set('cookie', 'true');
		$cookieExists = CommonCookie::exists('cookie');		
		
		// check if cookies exists
		if($this->orderId || $cookieExists)
		{
			// get the products
			$this->products = FrontendCatalogModel::getProductsByOrder($this->orderId);
			
			// count amount of products in shopping cart
			$this->amountOfProducts = count($this->products);
			
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
			
			$this->totalPriceArr['total'] = $this->totalPrice;
			
			// insert total price in db
			FrontendCatalogModel::updateOrder($this->totalPriceArr, $this->orderId);
			
			$this->cookiesEnabled = true;
		}
		
		// set cookies enabled to false
		else
		{
		    $this->cookiesEnabled = false;
		}	
	}
	
    /**
     * Parse the data into the template
     */
    private function parse()
    {
		// url for checkout
		$this->overviewUrl = FrontendNavigation::getURLForBlock('catalog', 'checkout');
		
		if(!empty($this->products)) $this->tpl->assign('productsInShoppingCart', $this->products);
		if(!empty($this->totalPrice)) $this->tpl->assign('totalPrice', $this->totalPrice);
		if(!empty($this->amountOfProducts)) $this->tpl->assign('amountOfProducts', $this->amountOfProducts);
		if($this->cookiesEnabled == true) $this->tpl->assign('cookiesEnabled', $this->cookiesEnabled);
		
		$this->tpl->assign('overviewUrl', $this->overviewUrl);
    }

    /**
     * Load the template
     */
    protected function loadTemplate()
    {
        // create template
        $this->tpl = new FrontendTemplate(false);
    }

    private function display()
    {
        // parse
        $this->parse();

        // output
        $this->output(self::OK, $this->tpl->getContent(FRONTEND_PATH . '/modules/catalog/layout/widgets/shopping_cart_ajax.tpl', false, true));
    }

}
