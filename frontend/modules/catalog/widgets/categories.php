<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a widget with the Catalog-categories
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendCatalogWidgetCategories extends FrontendBaseWidget
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
		$tree = FrontendCatalogModel::getCategoryTree();
			
		// any categories?
		if(!empty($categories))
		{
			// build link
			$link = FrontendNavigation::getURLForBlock('catalog', 'category');

			// loop and reset url
			foreach($categories as &$row) $row['url'] = $link . '/' . $row['url'];
		}

		// assign comments
		$this->tpl->assign('widgetCatalogCategoriesFlat', $categories);
		$this->tpl->assign('widgetCatalogCategoriesTree', $tree);
	}
}
