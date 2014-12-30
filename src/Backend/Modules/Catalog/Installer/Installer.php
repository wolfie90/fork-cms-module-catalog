<?php

namespace Backend\Modules\Catalog\Installer;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

use Backend\Core\Installer\ModuleInstaller;

/**
 * Installer for the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Installer extends ModuleInstaller
{
    /**
     * @var    int
     */
    private $defaultCategoryId;

    /**
     * @var    int
     */
    private $defaultBrandId;

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
        $item['language'] = (string)$language;
        $item['title'] = (string)$title;
        $item['created_on'] = gmdate('Y-m-d H:i:00');
        $item['edited_on'] = gmdate('Y-m-d H:i:00');
        $item['parent_id'] = (int)$parentId;
        $item['image'] = '';
        $item['sequence'] = 1;

        return (int)$this->getDB()->insert('catalog_categories', $item);
    }

    /**
     * Add a default brand
     *
     * @param string $language
     * @param string $title
     * @param string $url
     * @return int
     */
    private function addBrand($language, $title, $url)
    {
        // build array
        $item['meta_id'] = $this->insertMeta($title, $title, $title, $url);
        $item['language'] = (string)$language;
        $item['title'] = (string)$title;
        $item['created_on'] = gmdate('Y-m-d H:i:00');
        $item['edited_on'] = gmdate('Y-m-d H:i:00');
        $item['sequence'] = 1;

        return (int)$this->getDB()->insert('catalog_brands', $item);
    }

    /**
     * Fetch the id of the first category in this language we come across
     *
     * @param string $language
     * @return int
     */
    private function getCategory($language)
    {
        return (int)$this->getDB()->getVar(
            'SELECT id
			 FROM catalog_categories
			 WHERE language = ?',
            array((string)$language));
    }

    public function install()
    {
        // load install.sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // add 'catalog' as a module
        $this->addModule('Catalog');

        // import locale
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        // general settings
        $this->setSetting('Catalog', 'allow_comments', true);
        $this->setSetting('Catalog', 'requires_akismet', true);
        $this->setSetting('Catalog', 'spamfilter', false);
        $this->setSetting('Catalog', 'moderation', true);
        $this->setSetting('Catalog', 'overview_num_items', 10);
        $this->setSetting('Catalog', 'recent_products_full_num_items', 3);
        $this->setSetting('Catalog', 'allow_multiple_categories', true);

        $this->setSetting('Catalog', 'width1', (int)400);
        $this->setSetting('Catalog', 'height1', (int)300);
        $this->setSetting('Catalog', 'allow_enlargment1', true);
        $this->setSetting('Catalog', 'force_aspect_ratio1', true);

        $this->setSetting('Catalog', 'width2', (int)800);
        $this->setSetting('Catalog', 'height2', (int)600);
        $this->setSetting('Catalog', 'allow_enlargment2', true);
        $this->setSetting('Catalog', 'force_aspect_ratio2', true);

        $this->setSetting('Catalog', 'width3', (int)1600);
        $this->setSetting('Catalog', 'height3', (int)1200);
        $this->setSetting('Catalog', 'allow_enlargment3', true);
        $this->setSetting('Catalog', 'force_aspect_ratio3', true);

        $this->makeSearchable('Catalog');

        // module rights
        $this->setModuleRights(1, 'Catalog');

        // products and index
        $this->setActionRights(1, 'Catalog', 'Index');
        $this->setActionRights(1, 'Catalog', 'Add');
        $this->setActionRights(1, 'Catalog', 'Edit');
        $this->setActionRights(1, 'Catalog', 'Delete');

        // categories
        $this->setActionRights(1, 'Catalog', 'Categories');
        $this->setActionRights(1, 'Catalog', 'AddCategory');
        $this->setActionRights(1, 'Catalog', 'EditCategory');
        $this->setActionRights(1, 'Catalog', 'DeleteCategory');
        $this->setActionRights(1, 'Catalog', 'SequenceCategories');

        // specifications
        $this->setActionRights(1, 'Catalog', 'Specifications');
        $this->setActionRights(1, 'Catalog', 'EditSpecification');
        $this->setActionRights(1, 'Catalog', 'DeleteSpecification');
        $this->setActionRights(1, 'Catalog', 'SequenceSpecifications');

        // media
        $this->setActionRights(1, 'Catalog', 'MassMediaAction');
        $this->setActionRights(1, 'Catalog', 'Media');

        // images
        $this->setActionRights(1, 'Catalog', 'AddImage');
        $this->setActionRights(1, 'Catalog', 'EditImage');
        $this->setActionRights(1, 'Catalog', 'DeleteImage');
        $this->setActionRights(1, 'Catalog', 'SequenceMediaImages');

        // files
        $this->setActionRights(1, 'Catalog', 'AddFile');
        $this->setActionRights(1, 'Catalog', 'EditFile');
        $this->setActionRights(1, 'Catalog', 'DeleteFile');
        //$this->setActionRights(1, 'Catalog', 'SequenceFiles');

        // videos
        $this->setActionRights(1, 'Catalog', 'AddVideo');
        $this->setActionRights(1, 'Catalog', 'EditVideo');
        $this->setActionRights(1, 'Catalog', 'DeleteVideo');
        //$this->setActionRights(1, 'Catalog', 'SequenceVideos');

        // comments
        $this->setActionRights(1, 'Catalog', 'Comments');
        $this->setActionRights(1, 'Catalog', 'EditComment');
        $this->setActionRights(1, 'Catalog', 'DeleteSpam');
        $this->setActionRights(1, 'Catalog', 'MassCommentAction');

        // orders
        $this->setActionRights(1, 'Catalog', 'Orders');
        $this->setActionRights(1, 'Catalog', 'EditOrder');
        $this->setActionRights(1, 'Catalog', 'DeleteCompleted');
        $this->setActionRights(1, 'Catalog', 'MassOrderAction');

        // settings
        $this->setActionRights(1, 'Catalog', 'Settings');

        // categories
        $this->setActionRights(1, 'Catalog', 'Brands');
        $this->setActionRights(1, 'Catalog', 'AddBrand');
        $this->setActionRights(1, 'Catalog', 'EditBrand');
        $this->setActionRights(1, 'Catalog', 'DeleteBrand');
        $this->setActionRights(1, 'Catalog', 'SequenceBrands');

        // add extra's
        $catalogId = $this->insertExtra('Catalog', 'block', 'Catalog', null, null, 'N', 1000);
        $this->insertExtra('Catalog', 'widget', 'Categories', 'Categories', null, 'N', 1004);
        $this->insertExtra('Catalog', 'widget', 'ShoppingCart', 'ShoppingCart', null, 'N', 1005);
        $this->insertExtra('Catalog', 'widget', 'RecentProducts', 'RecentProducts', null, 'N', 1006);
        $this->insertExtra('Catalog', 'widget', 'Brands', 'Brands', null, 'N', 1007);

        foreach ($this->getLanguages() as $language)
        {
            $this->defaultCategoryId = $this->getCategory($language);

            // no category exists
            if ($this->defaultCategoryId == 0)
            {
                $this->defaultCategoryId = $this->addCategory($language, 'Default', 'default', 0);
            }

            // add default brand
            $this->defaultBrandId = $this->addBrand($language, 'Samsung', 'samsung');

            // check if a page for catalog already exists in this language
            if (!(bool)$this->getDB()->getVar(
                'SELECT 1
				 FROM pages AS p
				 INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
				 WHERE b.extra_id = ? AND p.language = ?
				 LIMIT 1',
                array($catalogId, $language))
            )
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
        $this->setNavigation($navigationCatalogId, 'Brands', 'catalog/brands', array('catalog/add_brand', 'catalog/edit_brand'));

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
        if (!(bool)$db->getVar(
            'SELECT 1
			 FROM catalog_products
			 WHERE language = ?
			 LIMIT 1',
            array($language))
        )
        {
            // insert sample product
            $productId = $db->insert('catalog_products', array(
                'category_id' => $this->defaultCategoryId,
                'brand_id' => $this->defaultBrandId,
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
                'edited_on' => gmdate('Y-m-d H:i:00'),
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
            $fs = new Filesystem();
            if (!$fs->exists(PATH_WWW . '/src/Frontend/Files/Catalog/')) $fs->mkdir(PATH_WWW . '/src/Frontend/Files/Catalog/');
            $fs->mirror(PATH_WWW . '/src/Backend/Modules/Catalog/Installer/Data/Images/', PATH_WWW . '/src/Frontend/Files/Catalog/' . $productId);
        }
    }
}
