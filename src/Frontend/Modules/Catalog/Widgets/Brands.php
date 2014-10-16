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
 * @author Waldo Cosman <waldo_cosman@hotmail.com>
 */
class Brands extends FrontendBaseWidget
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
        $brands = FrontendCatalogModel::getAllBrands();

        // assign comments
        $this->tpl->assign('widgetCatalogBrands', $brands);
    }
}
