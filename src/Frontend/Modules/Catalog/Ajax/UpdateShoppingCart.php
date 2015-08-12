<?php

namespace Frontend\Modules\Catalog\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Common\Cookie as Cookie;
use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Template as FrontendTemplate;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

/**
 * This is a ajax call to update the view of the shopping cart
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class UpdateShoppingCart extends FrontendBaseAJAXAction
{
    /**
     * The order id
     *
     * @var    int
     */
    private $orderId;

    /**
     * The amount of products in the shopping cart
     *
     * @var    int
     */
    private $amountOfProducts;
    /**
     * The total amount (price) of the order
     *
     * @var    int
     */
    private $totalPrice;

    /**
     * The products within an order
     *
     * @var    array
     */
    private $products;

    /**
     * The url for overview-page shopping-cart
     *
     * @var    string
     */
    private $overviewUrl;

    /**
     * The total price
     *
     * @var    array
     */
    private $totalPriceArr;

    /**
     * Are cookies enabled?
     *
     * @var    string
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
    private function getData()
    {
        // get cookie
        $this->orderId = Cookie::get('order_id');

        // check if cookies are available
        $this->cookiesEnabled = Cookie::hasAllowedCookies();

        // check if cookies exists
        if ($this->orderId || $this->cookiesEnabled == true) {
            // get the products
            $this->products = FrontendCatalogModel::getProductsByOrder($this->orderId);

            // count amount of products in shopping cart
            $this->amountOfProducts = count($this->products);

            // total price
            $this->totalPrice = '0';

            // calculate total amount
            foreach ($this->products as &$product) {
                // calculate total
                $subtotal = (int)$product['subtotal_price'];
                $this->totalPrice = (int)$this->totalPrice;
                $this->totalPrice = $this->totalPrice + $subtotal;
            }

            $this->totalPriceArr['total'] = $this->totalPrice;

            // insert total price in db
            FrontendCatalogModel::updateOrder($this->totalPriceArr, $this->orderId);
        }
    }

    /**
     * Parse the data into the template
     */
    private function parse()
    {
        // url for checkout
        $this->overviewUrl = FrontendNavigation::getURLForBlock('Catalog', 'Checkout');

        if (!empty($this->products)) {
            $this->tpl->assign('productsInShoppingCart', $this->products);
        }
        if (!empty($this->totalPrice)) {
            $this->tpl->assign('totalPrice', $this->totalPrice);
        }
        if (!empty($this->amountOfProducts)) {
            $this->tpl->assign('amountOfProducts', $this->amountOfProducts);
        }
        if ($this->cookiesEnabled == true) {
            $this->tpl->assign('cookiesEnabled', $this->cookiesEnabled);
        }

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
        $this->output(self::OK, $this->tpl->getContent(FRONTEND_PATH . '/Modules/Catalog/Layout/Widgets/ShoppingCartAjax.tpl', false, true));
    }
}
