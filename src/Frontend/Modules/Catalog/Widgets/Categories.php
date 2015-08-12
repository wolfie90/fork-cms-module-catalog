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
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Categories extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        // get categories
        $categories = FrontendCatalogModel::getAllCategories();

        // get tree of all categories
        $tree = FrontendCatalogModel::getCategoriesTree();

        // any categories?
        if (!empty($categories)) {
            // build link
            $link = FrontendNavigation::getURLForBlock('Catalog', 'Category');

            // loop and reset url
            foreach ($categories as &$row) {
                $row['url'] = $link . '/' . $row['url'];
            }
        }

        // assign comments
        $this->tpl->assign('widgetCatalogCategoriesFlat', $categories);
        $this->tpl->assign('widgetCatalogCategoriesTree', $tree);
    }
}
