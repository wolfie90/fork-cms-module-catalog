<?php

namespace Frontend\Modules\Catalog\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

/**
 * This is a widget with the Catalog-categories
 *
 * @author Waldo Cosman<waldo@comsa.be>
 */
class Category extends FrontendBaseWidget
{

    /**
     * The item.
     *
     * @var    array
     */
    private $category;
    private $products;

    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadData();

        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Load the data
     */
    private function loadData()
    {
        // Get category
        $this->category = FrontendCatalogModel::getCategoryById((int)$this->data['id']);

        // Get Products
        $this->products = FrontendCatalogModel::getAllByCategory($this->data['id']);
    }

    /**
     * Parse
     */
    private function parse()
    {
        // assign comments
        $this->tpl->assign('category', $this->category);
        $this->tpl->assign('products', $this->products);
    }
}
