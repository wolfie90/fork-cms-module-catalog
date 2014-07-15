<?php

namespace Frontend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the category-action, it will display the overview of products/subcategories within a category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendCatalogCategory extends FrontendBaseBlock
{
	/**
	 * The items
	 *
	 * @var	array
	 */
	private $record;

    /**
     * All products within the category
     *
     * @var	array
     */
    private $products;

    /**
     * All subcategories in flat view
     *
     * @var	array
     */
    private $subcategories;

    /**
     * All subcategories in tree view
     *
     * @var	array
     */
    private $subcategoriesTree;
	
	/**
     * URL parameters
     *
	 * @var	array
	 */
	private $parameters;

	/**
	 * The pagination array
	 * It will hold all needed parameters, some of them need initialization.
	 *
	 * @var	array
	 */
	protected $pagination = array('limit' => 10, 'offset' => 0, 'requested_page' => 1, 'num_items' => null, 'num_pages' => null);

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
		$this->parameters = $this->URL->getParameters();
		$url = end($this->parameters);
		
		if($url === null) $this->redirect(FrontendNavigation::getURL(404));
		
		// get by URL
		$this->record = FrontendCatalogModel::getCategory($url);
		
		if(empty($this->record)) $this->redirect(FrontendNavigation::getURL(404));
				
		// get subcategories
		$this->subcategories = FrontendCatalogModel::getAllCategories($this->record['id'], $this->record['url']);
		
		// get subcategories tree of parent
		$this->subcategoriesTree = FrontendCatalogModel::getCategoriesTree($this->record['id']);	
			
		// get products
		$this->products = FrontendCatalogModel::getAllByCategory($this->record['id']);
		
		// requested page
		$requestedPage = $this->URL->getParameter('page', 'int', 1);

		// set URL and limit
		$this->pagination['url'] = FrontendNavigation::getURLForBlock('catalog', 'category') . '/' . $this->record['url'];

		$this->pagination['limit'] = FrontendModel::getModuleSetting('catalog', 'overview_num_items', 10);

		// populate count fields in pagination
		$this->pagination['num_items'] = FrontendCatalogModel::getCategoryCount($this->record['id']);
		$this->pagination['num_pages'] = (int) ceil($this->pagination['num_items'] / $this->pagination['limit']);

		// num pages is always equal to at least 1
		if($this->pagination['num_pages'] == 0) $this->pagination['num_pages'] = 1;

		// redirect if the request page doesn't exist
		if($requestedPage > $this->pagination['num_pages'] || $requestedPage < 1) $this->redirect(FrontendNavigation::getURL(404));

		// populate calculated fields in pagination
		$this->pagination['requested_page'] = $requestedPage;
		$this->pagination['offset'] = ($this->pagination['requested_page'] * $this->pagination['limit']) - $this->pagination['limit'];
	}
	
	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// add css 
		$this->header->addCSS('/frontend/modules/' . $this->getModule() . '/layout/css/catalog.css');
		
		// add noty js
		$this->header->addJS('/frontend/modules/' . $this->getModule() . '/js/noty/packaged/jquery.noty.packaged.min.js');
		
		// add breadcrumbs
		$categories = FrontendCatalogModel::getAllCategories();
		$paths = FrontendCatalogModel::traverseUp($categories, $this->record);
		$baseUrl = FrontendNavigation::getURLForBlock('catalog', 'category');
		
		// get full urls
		foreach($paths as $key => $value)
		{
			$category = FrontendCatalogModel::getCategoryById($key);
			$breadcrumbPaths = FrontendCatalogModel::traverseUp($categories, $category);
			$url = implode('/', $breadcrumbPaths);
			
			$category['full_url'] = $baseUrl . '/' . $url;
			
			// add breadcrumb
			$this->breadcrumb->addElement($category['title'], $category['full_url']);
		}

		// hide action title
		$this->tpl->assign('hideContentTitle', true);

		// show the title
		$this->tpl->assign('title', $this->record['title']);

		// set meta
		$this->header->setPageTitle($this->record['meta_title'], ($this->record['meta_title_overwrite'] == 'Y'));
		$this->header->addMetaDescription($this->record['meta_description'], ($this->record['meta_description_overwrite'] == 'Y'));
		$this->header->addMetaKeywords($this->record['meta_keywords'], ($this->record['meta_keywords_overwrite'] == 'Y'));

		// advanced SEO-attributes
		if(isset($this->record['meta_data']['seo_index'])) $this->header->addMetaData(array('name' => 'robots', 'content' => $this->record['meta_data']['seo_index']));
		if(isset($this->record['meta_data']['seo_follow'])) $this->header->addMetaData(array('name' => 'robots', 'content' => $this->record['meta_data']['seo_follow']));

		// assign items
		$this->tpl->assign('products', $this->products);
		$this->tpl->assign('record', $this->record);
		
		// flat array of categories
		$this->tpl->assign('subcategoriesFlat', $this->subcategories);
		
		// multidimensional array of categories
		$this->tpl->assign('subcategoriesTree', $this->subcategoriesTree);
		
		// multidimensional html list of subcategories
		$this->tpl->assign('subcategoriesHTML', FrontendCatalogModel::getTreeHTML($this->subcategoriesTree));

		// parse the pagination
		$this->parsePagination();
	}
}
