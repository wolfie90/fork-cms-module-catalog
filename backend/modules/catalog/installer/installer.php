<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Installer for the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class CatalogInstaller extends ModuleInstaller
{
	/**
	 * @var	int
	 */
	private $defaultCategoryId;
		
	/**
	 * Add a category for a language
	 *
	 * @param string $language
	 * @param string $title
	 * @param string $url
	 * @return int
	 */
	private function addCategory($language, $title, $url, $parentId)
	{
		// build array
		$item['meta_id'] = $this->insertMeta($title, $title, $title, $url);
		$item['language'] = (string) $language;
		$item['title'] = (string) $title;
		$item['created_on'] = gmdate('Y-m-d H:i:00');
		$item['parent_id'] = (int) $parentId;
		$item['sequence'] = 1;

		return (int) $this->getDB()->insert('catalog_categories', $item);
	}
	
	/**
	 * Fetch the id of the first category in this language we come across
	 *
	 * @param string $language
	 * @return int
	 */
	private function getCategory($language)
	{
		return (int) $this->getDB()->getVar(
			'SELECT id
			 FROM catalog_categories
			 WHERE language = ?',
			array((string) $language));
	}
	
	public function install()
	{
		// load install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'catalog' as a module
		$this->addModule('catalog');

		// import locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');
		
		// general settings
		$this->setSetting('catalog', 'allow_comments', true);
		$this->setSetting('catalog', 'requires_akismet', true);
		$this->setSetting('catalog', 'spamfilter', false);
		$this->setSetting('catalog', 'moderation', true);
		$this->setSetting('catalog', 'overview_num_items', 10);
		$this->setSetting('catalog', 'recent_products_full_num_items', 3);
		$this->setSetting('catalog', 'allow_multiple_categories', true);
		
		$this->setSetting('catalog', 'width1', (int)400);
		$this->setSetting('catalog', 'height1', (int)300);
		$this->setSetting('catalog', 'allow_enlargment1', true);
		$this->setSetting('catalog', 'force_aspect_ratio1', true);
		
		$this->setSetting('catalog', 'width2', (int)800);
		$this->setSetting('catalog', 'height2', (int)600);
		$this->setSetting('catalog', 'allow_enlargment2', true);
		$this->setSetting('catalog', 'force_aspect_ratio2', true);
		
		$this->setSetting('catalog', 'width3', (int)1600);
		$this->setSetting('catalog', 'height3', (int)1200);
		$this->setSetting('catalog', 'allow_enlargment3', true);
		$this->setSetting('catalog', 'force_aspect_ratio3', true);
		
		$this->makeSearchable('catalog');
		
		// module rights
		$this->setModuleRights(1, 'catalog');
		
		// products and index
		$this->setActionRights(1, 'catalog', 'index');
		$this->setActionRights(1, 'catalog', 'add');
		$this->setActionRights(1, 'catalog', 'edit');
		$this->setActionRights(1, 'catalog', 'delete');
		
		// categories
		$this->setActionRights(1, 'catalog', 'categories');
		$this->setActionRights(1, 'catalog', 'add_category');
		$this->setActionRights(1, 'catalog', 'edit_category');
		$this->setActionRights(1, 'catalog', 'delete_category');
		$this->setActionRights(1, 'catalog', 'sequence_categories');
		
		// specifications
		$this->setActionRights(1, 'catalog', 'specifications');
		$this->setActionRights(1, 'catalog', 'edit_specification');
		$this->setActionRights(1, 'catalog', 'delete_specification');
		$this->setActionRights(1, 'catalog', 'sequence_specifications');
		
		// media
		$this->setActionRights(1, 'catalog', 'mass_media_action');
		$this->setActionRights(1, 'catalog', 'media');
		
		// images
		$this->setActionRights(1, 'catalog', 'add_image');
		$this->setActionRights(1, 'catalog', 'edit_image');
		$this->setActionRights(1, 'catalog', 'delete_image');
		$this->setActionRights(1, 'catalog', 'sequence_media_images');
		
		// files
		$this->setActionRights(1, 'catalog', 'add_file');
		$this->setActionRights(1, 'catalog', 'edit_file');
		$this->setActionRights(1, 'catalog', 'delete_file');
		//$this->setActionRights(1, 'catalog', 'sequence_files');
		
		// videos
		$this->setActionRights(1, 'catalog', 'add_video');
		$this->setActionRights(1, 'catalog', 'edit_video');
		$this->setActionRights(1, 'catalog', 'delete_video');
		//$this->setActionRights(1, 'catalog', 'sequence_videos');
				
		// comments
		$this->setActionRights(1, 'catalog', 'comments');
		$this->setActionRights(1, 'catalog', 'edit_comment');
		$this->setActionRights(1, 'catalog', 'delete_spam');
		$this->setActionRights(1, 'catalog', 'mass_comment_action');
		
		// orders
		$this->setActionRights(1, 'catalog', 'orders');
		$this->setActionRights(1, 'catalog', 'edit_order');
		$this->setActionRights(1, 'catalog', 'delete_completed');
		$this->setActionRights(1, 'catalog', 'mass_order_action');
		
		// settings
		$this->setActionRights(1, 'catalog', 'settings');
		
		// add extra's
		$catalogId = $this->insertExtra('catalog', 'block', 'Catalog', null, null, 'N', 1000);
		$this->insertExtra('catalog', 'widget', 'Categories', 'categories', null, 'N', 1004);
		$this->insertExtra('catalog', 'widget', 'ShoppingCart', 'shopping_cart', null, 'N', 1005);
		$this->insertExtra('catalog', 'widget', 'RecentProducts', 'recent_products', null, 'N', 1006);
		
		foreach($this->getLanguages() as $language)
		{
			$this->defaultCategoryId = $this->getCategory($language);

			// no category exists
			if($this->defaultCategoryId == 0)
			{
				$this->defaultCategoryId = $this->addCategory($language, 'Default', 'default', 0);
			}
			
			// check if a page for catalog already exists in this language
			if(!(bool) $this->getDB()->getVar(
				'SELECT 1
				 FROM pages AS p
				 INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
				 WHERE b.extra_id = ? AND p.language = ?
				 LIMIT 1',
				 array($catalogId, $language)))
			{
				// insert page
				$this->insertPage(array('title' => 'Catalog',
										'language' => $language),
										null,
										array('extra_id' => $catalogId));
			}
		

			$this->installExampleData($language);
		}
		
		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$navigationCatalogId = $this->setNavigation($navigationModulesId, 'Catalog');
		$this->setNavigation($navigationCatalogId, 'Products', 'catalog/index', array('catalog/add', 'catalog/edit', 'catalog/media', 'catalog/add_image', 'catalog/edit_image', 'catalog/add_file', 'catalog/edit_file', 'catalog/add_video', 'catalog/edit_video'));
		$this->setNavigation($navigationCatalogId, 'Categories', 'catalog/categories', array('catalog/add_category', 'catalog/edit_category'));
		$this->setNavigation($navigationCatalogId, 'Specifications', 'catalog/specifications', array('catalog/add_specification', 'catalog/edit_specification'));
		$this->setNavigation($navigationCatalogId, 'Comments', 'catalog/comments', array('catalog/edit_comment'));
		$this->setNavigation($navigationCatalogId, 'Orders', 'catalog/orders', array('catalog/edit_order'));

		// settings navigation
		$navigationSettingsId = $this->setNavigation(null, 'Settings');
		$navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
		$this->setNavigation($navigationModulesId, 'Catalog', 'catalog/settings');
	}
	
	/**
	 * Install example data
	 *
	 * @param string $language The language to use.
	 */
	private function installExampleData($language)
	{
		// get db instance
		$db = $this->getDB();

		// check if products already exist in this language
		if(!(bool) $db->getVar(
			'SELECT 1
			 FROM catalog_products
			 WHERE language = ?
			 LIMIT 1',
			array($language)))
		{	
			// insert sample product
			$productId = $db->insert( 'catalog_products', array(
									'category_id' => $this->defaultCategoryId,
									'meta_id' => $this->insertMeta('Samsung', 'Samsung', 'Samsung', 'samsung'),
									'language' => $language,
									'title' => 'Samsung UN32F5500',
									'summary' => '	Discover More of the TV You Love with the New Samsung Smart TV
													Get Content Recommendations While Watching TV
													Connect to Your Network Wirelessly with Built-in Wi-Fi
													Lorem ipsum dolor sit amet, consectetur adipiscing elit.
													Donec arcu tellus, luctus at aliquam eu, viverra et turpis.
													Curabitur sed purus nisl. Phasellus non justo auctor,
													tincidunt mauris ac, placerat sem. Proin mattis metus bibendum vulputate aliquam.
													Curabitur lacus est, lobortis ultrices erat ac, scelerisque fermentum lacus.
													Nam eu ipsum ipsum. Mauris lorem sapien, mollis at interdum a, eleifend vitae metus.
													Duis luctus felis et feugiat adipiscing. Proin vestibulum risus eu dapibus tincidunt.
													Etiam porttitor faucibus viverra.',
									'text' => ' This is the main text of the product.',				
									'created_on' => gmdate('Y-m-d H:i:00'),
									'price' => '399',
									'allow_comments' => 'Y',
									'num_comments' => '0',
									'sequence' => 1				
			));
			
			// insert sample specification 1
			$specificationId = $db->insert('catalog_specifications', array(
						'title' => 'Weight',
						'type' => 'textbox',
						'meta_id' => $this->insertMeta('Weight', 'Weight', 'Weight', 'weight'),
						'language' => $language,
						'sequence' => 1				
			));
			
			// insert sample specification value 1
			$db->insert('catalog_specifications_values', array(
						'product_id' => $productId,
						'specification_id' => $specificationId,
						'value' => '13.4 pounds'
			));
			
			// insert sample specification 2
			$specificationId = $db->insert('catalog_specifications', array(
						'title' => 'Model Number',
						'type' => 'textbox',
						'meta_id' => $this->insertMeta('Model', 'Model', 'Model', 'model'),
						'language' => $language,
						'sequence' => 1				
			));
			
			// insert sample specification value 2
			$db->insert('catalog_specifications_values', array(
						'product_id' => $productId,
						'specification_id' => $specificationId,
						'value' => 'UN32F5500'
			));

			// insert sample image 1
			$db->insert('catalog_images', array(
						'product_id' => $productId,
						'title' => 'Front (Screen)',
						'filename' => '1.png',
						'sequence' => 1				
			));
						
			// insert sample image 2
			$db->insert('catalog_images', array(
						'product_id' => $productId,
						'title' => 'Front',
						'filename' => '2.png',
						'sequence' => 2
			));
			
			// insert sample image 3
			$db->insert('catalog_images', array(
						'product_id' => $productId,
						'title' => 'Side',
						'filename' => '3.png',
						'sequence' => 3
			));
						
			// insert sample image 4
			$db->insert('catalog_images', array(
						'product_id' => $productId,
						'title' => 'Side Two',
						'filename' => '4.png',
						'sequence' => 4
			));
						
			// insert sample image 5
			$db->insert('catalog_images', array(
						'product_id' => $productId,
						'title' => 'Side Three',
						'filename' => '5.png',
						'sequence' => 5
			));
						
			// insert sample image 6
			$db->insert('catalog_images', array(
						'product_id' => $productId,
						'title' => 'Side Four',
						'filename' => '6.png',
						'sequence' => 6
			));
			
			// copy images into files path
			SpoonDirectory::create(PATH_WWW . '/frontend/files/catalog/');
			SpoonDirectory::copy(PATH_WWW . '/backend/modules/catalog/installer/data/images', PATH_WWW . '/frontend/files/catalog/' . $productId);
		}
	}
}
