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
 * This is a ajax call to update the view of the checkout cart
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class UpdateCheckoutCart extends FrontendBaseAJAXAction
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
     * The url for personal data page
     *
     * @var    string
     */
    private $personalDataUrl, $catalogUrl;

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

        if ($this->orderId) {
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

            // url for next step
            $this->personalDataUrl = FrontendNavigation::getURLForBlock('Catalog', 'PersonalData');

            // url for next step
            $this->catalogUrl = FrontendNavigation::getURLForBlock('Catalog');
        }
    }

    /**
     * Parse the data into the template
     */
    private function parse()
    {
        $this->tpl->assign('productsInShoppingCart', $this->products);
        $this->tpl->assign('totalPrice', $this->totalPrice);
        $this->tpl->assign('personalDataUrl', $this->personalDataUrl);
        $this->tpl->assign('catalogUrl', $this->catalogUrl);
        $this->tpl->assign('amountOfProducts', $this->amountOfProducts);
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
        $this->output(self::OK, $this->tpl->getContent(FRONTEND_PATH . '/Modules/Catalog/Layout/Templates/CheckoutAjax.tpl', false, true));
    }
}
