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
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

/**
 * This is a widget with recent products
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class RecentProducts extends FrontendBaseWidget
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
		// get list of recent products
		$numItems = FrontendModel::getModuleSetting('Catalog', 'recent_products_full_num_items', 3);
		$recentProducts = FrontendCatalogModel::getAll($numItems);
        
		$this->tpl->assign('widgetCatalogRecentProducts', $recentProducts);
	}
}
