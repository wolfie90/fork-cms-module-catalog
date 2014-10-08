<?php

namespace Backend\Modules\Catalog\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Exception;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;

/**
 * In this file we store all generic functions that we will be using in the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Model
{
	const QRY_DATAGRID_BROWSE = 'SELECT i.id, i.category_id, i.title AS title, i.sequence
		 FROM catalog_products AS i
		 WHERE i.language = ?
		 ORDER BY i.sequence ASC';

	const QRY_DATAGRID_BROWSE_FOR_CATEGORY = 'SELECT
			i.id, i.category_id, i.title AS title, i.sequence,
			c.title AS category
		 FROM catalog_products AS i
		 INNER JOIN catalog_categories AS c ON i.category_id = c.id
		 WHERE i.category_id = ? AND i.language = ? 
		 ORDER BY i.sequence ASC';

	const QRY_DATAGRID_BROWSE_COMMENTS = 'SELECT
		 	i.id, UNIX_TIMESTAMP(i.created_on) AS created_on, i.author, i.text,
		 	p.id AS product_id, p.title AS product_title, m.url AS product_url
		 FROM catalog_comments AS i
		 INNER JOIN catalog_products AS p
			ON i.product_id = p.id
			AND i.language = p.language
		 INNER JOIN meta AS m ON p.meta_id = m.id
		 WHERE i.status = ? AND i.language = ?
		 GROUP BY i.id';

	const QRY_DATAGRID_BROWSE_ORDERS = 'SELECT
		 	i.id, i.id AS order_nr, i.status, UNIX_TIMESTAMP(i.date) AS ordered_on,
			i.email, i.fname AS FirstName, i.lname AS LastName, i.total AS TotalPrice
		 FROM catalog_orders AS i
		 WHERE i.status = ?
		 GROUP BY i.id';

	const QRY_DATAGRID_BROWSE_PRODUCTS_ORDER = 'SELECT c.title, c.price, o.*, m.url
		 FROM `catalog_orders_values` AS o
		 INNER JOIN `catalog_products` AS c ON o.product_id = c.id
		 INNER JOIN meta AS m ON c.meta_id = m.id
		 WHERE o.order_id = ?';

	const QRY_DATAGRID_BROWSE_CATEGORIES = 'SELECT c.id, c.title, COUNT(i.id) AS num_products, c.sequence
		 FROM catalog_categories AS c
		 LEFT OUTER JOIN catalog_products AS i
			ON c.id = i.category_id
			AND i.language = c.language
		 WHERE c.language = ?
		 GROUP BY c.id
		 ORDER BY c.sequence ASC';

	const QRY_DATAGRID_BROWSE_CATEGORIES_WITH_CATEGORYID = 'SELECT c.id, c.title, COUNT(i.id) AS num_products, c.sequence
		 FROM catalog_categories AS c
		 LEFT OUTER JOIN catalog_products AS i
			ON c.id = i.category_id
			AND i.language = c.language
		 WHERE c.parent_id = ? AND c.language = ?
		 GROUP BY c.id
		 ORDER BY c.sequence ASC';

	const QRY_DATAGRID_BROWSE_SPECIFICATIONS = 'SELECT c.id, c.title AS specification, c.sequence
		 FROM catalog_specifications AS c
		 WHERE c.language = ?
		 GROUP BY c.id
		 ORDER BY c.sequence ASC';

	const QRY_DATAGRID_BROWSE_IMAGES = 'SELECT i.id, i.product_id, i.filename, i.title, i.sequence
		 FROM catalog_images AS i
		 WHERE i.product_id = ?
		 GROUP BY i.id
         ORDER BY i.sequence ASC';

	const QRY_DATAGRID_BROWSE_FILES = 'SELECT i.id, i.product_id, i.filename, i.title, i.sequence
		 FROM catalog_files AS i
		 WHERE i.product_id = ?
		 GROUP BY i.id
         ORDER BY i.sequence ASC';

	const QRY_DATAGRID_BROWSE_VIDEOS = 'SELECT i.id, i.product_id, i.embedded_url, i.title, i.sequence
		 FROM catalog_videos AS i
		 WHERE i.product_id = ?
		 GROUP BY i.id
         ORDER BY i.sequence ASC';

	const QRY_DATAGRID_BROWSE_BRANDS = 'SELECT b.id, b.title, COUNT(p.id) AS num_products, b.sequence
		 FROM catalog_brands AS b
		 LEFT JOIN catalog_products AS p ON p.brand_id = b.id
		 GROUP BY b.id
		 ORDER BY b.sequence ASC';

	/**
	 * Delete a certain item
	 *
	 * @param int $id
	 */
	public static function delete($id)
	{
		BackendModel::getContainer()->get('database')->delete('catalog_products', 'id = ?', (int)$id);
		BackendModel::getContainer()->get('database')->delete('catalog_specifications_values', 'product_id = ?', (int)$id);
	}

	/**
	 * Delete a specific category
	 *
	 * @param int $id
	 */
	public static function deleteCategory($id)
	{
		$db = BackendModel::getContainer()->get('database');
		$item = self::getCategory($id);

		if(!empty($item))
		{
			$db->delete('meta', 'id = ?', array($item['meta_id']));
			$db->delete('catalog_categories', 'id = ?', array((int)$id));
			$db->update('catalog_products', array('category_id' => null), 'category_id = ?', array((int)$id));
		}
	}

	/**
	 * Delete a specific category
	 *
	 * @param int $id
	 */
	public static function deleteBrand($id)
	{
		$db = BackendModel::getContainer()->get('database');
		$item = self::getBrand($id);

		if(!empty($item))
		{
			$db->delete('meta', 'id = ?', array($item['meta_id']));
			$db->delete('catalog_brands', 'id = ?', array((int)$id));
			$db->update('catalog_products', array('brand_id' => null), 'brand_id = ?', array((int)$id));
		}
	}

	/**
	 * Deletes one or more comments
	 *
	 * @param array $ids The id(s) of the items(s) to delete.
	 */
	public static function deleteComments($ids)
	{
		// make sure $ids is an array
		$ids = (array)$ids;

		// loop and cast to integers
		foreach($ids as &$id)
		{
			$id = (int)$id;
		}

		// create an array with an equal amount of questionmarks as ids provided
		$idPlaceHolders = array_fill(0, count($ids), '?');

		// get db
		$db = BackendModel::getContainer()->get('database');

		// get ids
		$itemIds = (array)$db->getColumn('SELECT i.product_id
			 FROM catalog_comments AS i
			 WHERE i.id IN (' . implode(', ', $idPlaceHolders) . ')', $ids);

		// update record
		$db->delete('catalog_comments', 'id IN (' . implode(', ', $idPlaceHolders) . ')', $ids);

		// recalculate the comment count
		if(!empty($itemIds)) self::reCalculateCommentCount($itemIds);

		// invalidate the cache for catalog
		BackendModel::invalidateFrontendCache('catalog', BL::getWorkingLanguage());
	}

	/**
	 * Deletes one or more orders
	 *
	 * @param array $ids The id(s) of the items(s) to delete.
	 */
	public static function deleteOrders($ids)
	{
		// make sure $ids is an array
		$ids = (array)$ids;

		// loop and cast to integers
		foreach($ids as &$id) $id = (int)$id;

		// create an array with an equal amount of questionmarks as ids provided
		$idPlaceHolders = array_fill(0, count($ids), '?');

		// get db
		$db = BackendModel::getContainer()->get('database');

		// get ids
		$itemIds = (array)$db->getColumn('SELECT i.id
			 FROM catalog_orders AS i
			 WHERE i.id IN (' . implode(', ', $idPlaceHolders) . ')', $ids);

		// update record
		$db->delete('catalog_orders', 'id IN (' . implode(', ', $idPlaceHolders) . ')', $ids);

		// invalidate the cache for catalog
		BackendModel::invalidateFrontendCache('catalog', BL::getWorkingLanguage());
	}

	/**
	 * Delete a specific specification
	 *
	 * @param int $id
	 */
	public static function deleteSpecification($id)
	{
		$db = BackendModel::getContainer()->get('database');
		$item = self::getSpecification($id);

		if(!empty($item))
		{
			$db->delete('meta', 'id = ?', array($item['meta_id']));
			$db->delete('catalog_specifications', 'id = ?', array((int)$id));
			$db->delete('catalog_specifications_values', 'specification_id = ?', array((int)$id));
		}
	}

	/**
	 * Delete all spam
	 */
	public static function deleteSpamComments()
	{
		$db = BackendModel::getContainer()->get('database');

		// get ids
		$itemIds = (array)$db->getColumn('SELECT i.product_id
			 FROM catalog_comments AS i
			 WHERE status = ? AND i.language = ?', array('spam', BL::getWorkingLanguage()));

		// update record
		$db->delete('catalog_comments', 'status = ? AND language = ?', array('spam', BL::getWorkingLanguage()));

		// recalculate the comment count
		if(!empty($itemIds)) self::reCalculateCommentCount($itemIds);

		// invalidate the cache for blog
		BackendModel::invalidateFrontendCache('catalog', BL::getWorkingLanguage());
	}

	/**
	 * Delete all spam
	 */
	public static function deleteCompletedOrders()
	{
		$db = BackendModel::getContainer()->get('database');

		// get ids
		$itemIds = (array)$db->getColumn('SELECT i.id
			 FROM catalog_orders AS i
			 WHERE status = ?', array('completed'));

		// update record
		$db->delete('catalog_orders', 'status = ?', array('completed'));

		// invalidate the cache for catalog
		BackendModel::invalidateFrontendCache('catalog', BL::getWorkingLanguage());
	}

	/**
	 * Delete related product
	 *
	 * @param int The product id
	 * @param int [optional] The related product id
	 */
	public static function deleteRelatedProduct($productId, $relatedProductId = null)
	{
		// delete specific related product
		if(isset($relatedProductId))
		{
			BackendModel::getContainer()->get('database')->delete('catalog_related_products', 'product_id = ? AND related_product_id = ?', array((int)$productId, (int)$relatedProductId));
		}
		else
		{
			// delete all related products from product
			BackendModel::getContainer()->get('database')->delete('catalog_related_products', 'product_id = ?', array((int)$productId));
		}
	}

	/**
	 * @param array $ids
	 */
	public static function deleteImage(array $ids)
	{
		if(empty($ids)) return;

		foreach($ids as $id)
		{
			$item = self::getImage($id);
			$product = self::get($item['product_id']);

			// delete image reference from db
			BackendModel::getContainer()->get('database')->delete('catalog_images', 'id = ?', array($id));

			// delete image from disk
			$basePath = FRONTEND_FILES_PATH . '/catalog/' . $item['product_id'];
			\SpoonFile::delete($basePath . '/source/' . $item['filename']);
			\SpoonFile::delete($basePath . '/64x64/' . $item['filename']);
			\SpoonFile::delete($basePath . '/128x128/' . $item['filename']);
			\SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('catalog', 'width1') . 'x' . BackendModel::getModuleSetting('catalog', 'height1') . '/' . $item['filename']);
			\SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('catalog', 'width2') . 'x' . BackendModel::getModuleSetting('catalog', 'height2') . '/' . $item['filename']);
			\SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('catalog', 'width3') . 'x' . BackendModel::getModuleSetting('catalog', 'height3') . '/' . $item['filename']);
		}

		BackendModel::invalidateFrontendCache('slideshowCache');
	}

	/**
	 * @param array $ids
	 */
	public static function deleteFile(array $ids)
	{
		if(empty($ids)) return;

		foreach($ids as $id)
		{
			$item = self::getFile($id);
			$product = self::get($item['product_id']);

			// delete file reference from db
			BackendModel::getContainer()->get('database')->delete('catalog_files', 'id = ?', array($id));

			// delete file from disk
			$basePath = FRONTEND_FILES_PATH . '/catalog/' . $item['product_id'];
			\SpoonFile::delete($basePath . '/source/' . $item['filename']);
		}
	}

	/**
	 * @param array $ids
	 */
	public static function deleteVideo(array $ids)
	{
		if(empty($ids)) return;

		foreach($ids as $id)
		{
			$item = self::getVideo($id);
			$product = self::get($item['product_id']);

			// delete video reference from db
			BackendModel::getContainer()->get('database')->delete('catalog_videos', 'id = ?', array($id));
		}
	}

	/**
	 * Checks if a certain item exists
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_products AS i
			 WHERE i.id = ?
			 LIMIT 1', array((int)$id));
	}

	/**
	 * Does the category exist?
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function existsCategory($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_categories AS i
			 WHERE i.id = ? AND i.language = ?
			 LIMIT 1', array((int)$id, BL::getWorkingLanguage()));
	}

	/**
	 * Checks if a comment exists
	 *
	 * @param int $id The id of the item to check for existence.
	 * @return int
	 */
	public static function existsComment($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_comments AS i
			 WHERE i.id = ? AND i.language = ?
			 LIMIT 1', array((int)$id, BL::getWorkingLanguage()));
	}

	/**
	 * Checks if a order exists
	 *
	 * @param int $id The id of the item to check for existence.
	 * @return int
	 */
	public static function existsOrder($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_orders AS i
			 WHERE i.id = ?
			 LIMIT 1', array((int)$id));
	}

	/**
	 * Checks if specification exists
	 *
	 * @param int $id Id of a specification.
	 * @param int [optional] $productId Id of a product.
	 * @return bool
	 */
	public static function existsSpecification($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_specifications AS i
			 WHERE i.id = ? AND i.language = ?
			 LIMIT 1', array((int)$id, BL::getWorkingLanguage()));
	}

	/**
	 * Checks if value of specification exists
	 *
	 * @param int $id Id of a specification.
	 * @param int [optional] $productId Id of a product.
	 * @return bool
	 */
	public static function existsSpecificationValue($productId, $specificationId)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_specifications_values AS i
			 WHERE i.product_id = ? AND i.specification_id = ? 
			 LIMIT 1', array((int)$productId, (int)$specificationId));
	}

	/**
	 * Checks if image exists
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function existsImage($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_images AS a
			 WHERE a.id = ?', array((int)$id));
	}

	/**
	 * Checks if file exists
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function existsFile($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_files AS a
			 WHERE a.id = ?', array((int)$id));
	}

	/**
	 * Checks if video exists
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function existsVideo($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_videos AS a
			 WHERE a.id = ?', array((int)$id));
	}

	/**
	 * Does the category exist?
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function existsBrand($id)
	{
		return (bool)BackendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_brands AS i
			 WHERE i.id = ?
			 LIMIT 1', array((int)$id));
	}

	/**
	 * Fetches a certain item
	 *
	 * @param int $id
	 * @return array
	 */
	public static function get($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM catalog_products AS i
			 WHERE i.id = ?', array((int)$id));
	}

	/**
	 * Fetches a all items
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getAll()
	{
		$db = BackendModel::getContainer()->get('database');

		return (array)$db->getPairs('SELECT i.id, i.title
			 FROM catalog_products AS i
			 WHERE i.language = ?
			 GROUP BY i.id', array(BL::getWorkingLanguage()));
	}

	/**
	 * Get all the categories
	 *
	 * @param bool [optional] $includeCount
	 * @return array
	 */
	public static function getCategories($includeCount = false)
	{
		$db = BackendModel::getContainer()->get('database');

		if($includeCount)
		{
			$allCategories = (array)$db->getRecords('SELECT i.id, i.parent_id, CONCAT(i.title, " (", COUNT(p.category_id) ,")") AS title
				 FROM catalog_categories AS i
				 LEFT OUTER JOIN catalog_products AS p ON i.id = p.category_id AND i.language = p.language
				 WHERE i.language = ?
				 GROUP BY i.id
				 ORDER BY i.sequence', array(BL::getWorkingLanguage()));


			$tree = array();

			$categoryTree = self::buildTree($tree, $allCategories);
			$categoryTree = array('no_category' => ucfirst(BL::getLabel('None'))) + $categoryTree;

			return $categoryTree;
		}
	}

	/**
	 * Build the category tree
	 *
	 * @param $tree
	 * @param array $categories
	 * @param int $parentId
	 * @param int $level
	 * @return array
	 */
	public static function buildTree(array &$tree, array $categories, $parentId = 0, $level = 0)
	{
		foreach($categories as $category)
		{
			if($category['parent_id'] == $parentId)
			{
				$tree[$category['id']] = str_repeat('-', $level) . $category['title'];

				$level++;
				$children = self::buildTree($tree, $categories, $category['id'], $level);
				$level--;
			}
		}
		return $tree;
	}

	/**
	 * Get all data for a given id
	 *
	 * @param int $id The Id of the comment to fetch?
	 * @return array
	 */
	public static function getComment($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on,
			 p.id AS product_id, p.title AS product_title, m.url AS product_url
			 FROM catalog_comments AS i
			 INNER JOIN catalog_products AS p ON i.product_id = p.id AND i.language = p.language
			 INNER JOIN meta AS m ON p.meta_id = m.id
			 WHERE i.id = ?
			 LIMIT 1', array((int)$id));
	}

	/**
	 * Get all data for a given id
	 *
	 * @param int $id The Id of the order to fetch?
	 * @return array
	 */
	public static function getOrder($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*, UNIX_TIMESTAMP(i.date) AS ordered_on,
			 p.amount AS amount_of_product, c.title AS product_title
			 FROM catalog_orders AS i
			 INNER JOIN catalog_orders_values AS p ON i.id = p.order_id
			 INNER JOIN catalog_products AS c ON p.product_id = c.id
			 WHERE i.id = ?
			 LIMIT 1', array((int)$id));
	}

	/**
	 * Get multiple comments at once
	 *
	 * @param array $ids The id(s) of the comment(s).
	 * @return array
	 */
	public static function getComments(array $ids)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecords('SELECT *
			 FROM catalog_comments AS i
			 WHERE i.id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')', $ids);
	}

	/**
	 * Get a count per comment
	 *
	 * @return array
	 */
	public static function getCommentStatusCount()
	{
		return (array)BackendModel::getContainer()->get('database')->getPairs('SELECT i.status, COUNT(i.id)
			 FROM catalog_comments AS i
			 WHERE i.language = ?
			 GROUP BY i.status', array(BL::getWorkingLanguage()));
	}

	/**
	 * Get all products grouped by categories
	 *
	 * @return array
	 */
	public static function getAllProductsGroupedByCategories()
	{
		$db = BackendModel::getContainer()->get('database');

		$allProducts = (array)$db->getRecords('SELECT p.id, p.title, pc.id AS category_id, pc.title AS category_title
			 FROM catalog_products p
			 INNER JOIN catalog_categories pc ON p.category_id = pc.id
			 WHERE p.language = ?', array(BL::getWorkingLanguage()));

		$productsGroupedByCategory = array();

		foreach($allProducts as $pid => $product)
		{
			$productsGroupedByCategory[$product['category_title']][$product['id']] = $product['title'];
		}

		return $productsGroupedByCategory;
	}

	/**
	 * Get related products of an item
	 *
	 * @param int $id The product id
	 * @return array
	 */
	public static function getRelatedProducts($id)
	{
		$db = BackendModel::getContainer()->get('database');

		$relatedProducts = (array)$db->getPairs('SELECT r.related_product_id AS keyId, r.related_product_id AS valueId
			 FROM catalog_related_products r
			 WHERE r.product_id = ?', array((int)$id));

		$i = 0;

		// build new keys (starting from zero)	
		foreach($relatedProducts as $key => $value)
		{
			if(isset($relatedProducts[$key]))
			{
				$relatedProducts[$i] = $relatedProducts[$key];
				unset($relatedProducts[$key]);
			}
			$i++;
		}

		return $relatedProducts;
	}

	/**
	 * Get specification value of an item
	 *
	 * @param int $specificationId The specification id
	 * @param int $productId The product id
	 * @return string
	 */
	public static function getSpecificationValue($specificationId, $productId)
	{
		$db = BackendModel::getContainer()->get('database');

		$value = (array)$db->getRecord('SELECT i.value
			 FROM catalog_specifications_values i
			 WHERE i.specification_id = ? AND i.product_id = ?', array((int)$specificationId, (int)$productId));

		return $value;
	}

	/**
	 * Fetch an image
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getImage($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM catalog_images AS i
			 WHERE i.id = ?', array((int)$id));
	}

	/**
	 * Fetch an file
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getFile($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM catalog_files AS i
			 WHERE i.id = ?', array((int)$id));
	}

	/**
	 * Fetch an video
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getVideo($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM catalog_videos AS i
			 WHERE i.id = ?', array((int)$id));
	}

	/**
	 * Check if category has children
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function categoryHasChildren($id)
	{
		// gives children from category
		$item = (array)BackendModel::getContainer()->get('database')->getRecord('SELECT c1.id as childID, c1.title ChildName, c2.title as ParentName
			 FROM catalog_categories AS c1
			 LEFT OUTER JOIN catalog_categories AS c2 ON c1.id = c2.parent_id
			 WHERE c1.language = ? AND c1.parent_id = ?', array(BL::getWorkingLanguage(), $id));

		if($item == null)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Fetch a category
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getCategory($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM catalog_categories AS i
			 WHERE i.id = ? AND i.language = ?', array((int)$id, BL::getWorkingLanguage()));
	}

	/**
	 * Fetch a specification
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getSpecification($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM catalog_specifications AS i
			 WHERE i.id = ? AND i.language = ?', array((int)$id, BL::getWorkingLanguage()));
	}

	/**
	 * Get the maximum sequence for a category
	 *
	 * @return int
	 */
	public static function getMaximumCategorySequence()
	{
		return (int)BackendModel::getContainer()->get('database')->getVar('SELECT MAX(i.sequence)
			 FROM catalog_categories AS i
			 WHERE i.language = ?', array(BL::getWorkingLanguage()));
	}

	/**
	 * Get the maximum sequence for a specification
	 *
	 * @return int
	 */
	public static function getMaximumSpecificationSequence()
	{
		return (int)BackendModel::getContainer()->get('database')->getVar('SELECT MAX(i.sequence)
			 FROM catalog_specifications AS i
			 WHERE i.language = ?', array(BL::getWorkingLanguage()));
	}

	/**
	 * Get the max sequence id for an image
	 *
	 * @param int $id The product id.
	 * @return int
	 */
	public static function getMaximumImagesSequence($id)
	{
		return (int)BackendModel::getContainer()->get('database')->getVar('SELECT MAX(i.sequence)
			 FROM catalog_images AS i
			 WHERE i.product_id = ?', array((int)$id));
	}

	/**
	 * Get the max sequence id for an file
	 *
	 * @param int $id The product id.
	 * @return int
	 */
	public static function getMaximumFilesSequence($id)
	{
		return (int)BackendModel::getContainer()->get('database')->getVar('SELECT MAX(i.sequence)
			 FROM catalog_files AS i
			 WHERE i.product_id = ?', array((int)$id));
	}

	/**
	 * Get the max sequence id for an videos
	 *
	 * @param int $id The product id.
	 * @return int
	 */
	public static function getMaximumVideosSequence($id)
	{
		return (int)BackendModel::getContainer()->get('database')->getVar('SELECT MAX(i.sequence)
			 FROM catalog_videos AS i
			 WHERE i.product_id = ?', array((int)$id));
	}

	/**
	 * Get the maximum sequence
	 *
	 * @return int
	 */
	public static function getMaximumSequence()
	{
		return (int)BackendModel::getContainer()->get('database')->getVar('SELECT MAX(i.sequence)
			 FROM catalog_products AS i');
	}

	/**
	 * Get all the specifications
	 *
	 * @return array
	 */
	public static function getSpecifications()
	{
		$db = BackendModel::getContainer()->get('database');

		$items = (array)$db->getRecords('SELECT i.id, i.title, i.type
			 FROM catalog_specifications AS i
			 WHERE i.language = ?
			 GROUP BY i.id
			 ORDER BY i.sequence', array(BL::getWorkingLanguage()));

		return $items;
	}

	/**
	 * Get all the specifications
	 *
	 * @return array
	 */
	public static function getBrandsForDropdown()
	{
		$db = BackendModel::getContainer()->get('database');

		$items = (array)$db->getPairs('SELECT i.id, i.title
			 FROM catalog_brands AS i
			 GROUP BY i.id
			 ORDER BY i.sequence', array(BL::getWorkingLanguage()));

		return $items;
	}

	/**
	 * Fetch a category
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getBrand($id)
	{
		return (array)BackendModel::getContainer()->get('database')->getRecord('SELECT i.*
			 FROM catalog_brands AS i
			 WHERE i.id = ?', array((int)$id));
	}

	/**
	 * Retrieve the unique URL for an item
	 *
	 * @param string $url
	 * @param int [optional] $id    The id of the item to ignore.
	 * @return string
	 */
	public static function getURL($url, $id = null)
	{
		$url = \SpoonFilter::urlise((string)$url);
		$db = BackendModel::getContainer()->get('database');

		// new item
		if($id === null)
		{
			// already exists
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_products AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1', array(BL::getWorkingLanguage(), $url))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURL($url);
			}
		}
		else
		{
			// current item should be excluded
			// already exists
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_products AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1', array(BL::getWorkingLanguage(), $url, $id))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURL($url, $id);
			}
		}

		return $url;
	}

	/**
	 * Retrieve the unique URL for a specification
	 *
	 * @param string $url
	 * @param int [optional] $id The id of the specification to ignore.
	 * @return string
	 */
	public static function getURLForSpecification($url, $id = null)
	{
		$url = \SpoonFilter::urlise((string)$url);
		$db = BackendModel::getContainer()->get('database');

		// new specification
		if($id === null)
		{
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_specifications AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1', array(BL::getWorkingLanguage(), $url))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForSpecification($url);
			}
		}

		// current specification should be excluded
		else
		{
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_specifications AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1', array(BL::getWorkingLanguage(), $url, $id))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForSpecification($url, $id);
			}
		}

		return $url;
	}

	/**
	 * Retrieve the unique URL for a category
	 *
	 * @param string $url
	 * @param int [optional] $id The id of the category to ignore.
	 * @return string
	 */
	public static function getURLForCategory($url, $id = null)
	{
		$url = \SpoonFilter::urlise((string)$url);
		$db = BackendModel::getContainer()->get('database');

		// new category
		if($id === null)
		{
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1', array(BL::getWorkingLanguage(), $url))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForCategory($url);
			}
		}
		else
		{
			// current category should be excluded
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1', array(BL::getWorkingLanguage(), $url, $id))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForCategory($url, $id);
			}
		}

		return $url;
	}

	/**
	 * Retrieve the unique URL for a category
	 *
	 * @param string $url
	 * @param int [optional] $id The id of the category to ignore.
	 * @return string
	 */
	public static function getURLForBrand($url, $id = null)
	{
		$url = \SpoonFilter::urlise((string)$url);
		$db = BackendModel::getContainer()->get('database');

		// new category
		if($id === null)
		{
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_brands AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE m.url = ?
				 LIMIT 1', array($url))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForCategory($url);
			}
		}
		else
		{
			// current category should be excluded
			if((bool)$db->getVar('SELECT 1
				 FROM catalog_brands AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE m.url = ? AND i.id != ?
				 LIMIT 1', array($url, $id))
			)
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForCategory($url, $id);
			}
		}

		return $url;
	}

	/**
	 * Insert an item in the database
	 *
	 * @param array $item
	 * @return int
	 */
	public static function insert(array $item)
	{
		$item['created_on'] = BackendModel::getUTCDate();
	        $item['edited_on'] = BackendModel::getUTCDate();
		return (int)BackendModel::getContainer()->get('database')->insert('catalog_products', $item);
	}

	/**
	 * Insert a category in the database
	 *
	 * @param array $item
	 * @return int
	 */
	public static function insertCategory(array $item)
	{
		$item['created_on'] = BackendModel::getUTCDate();
	        $item['edited_on'] = BackendModel::getUTCDate();
		return BackendModel::getContainer()->get('database')->insert('catalog_categories', $item);
	}

	/**
	 * Insert a specification in the database
	 *
	 * @param array $item
	 * @return int
	 */
	public static function insertSpecification(array $item)
	{
		return BackendModel::getContainer()->get('database')->insert('catalog_specifications', $item);
	}

	/**
	 * Insert a specification value in the database
	 *
	 * @param array $item
	 * @return int
	 */
	public static function insertSpecificationValue(array $item)
	{
		return BackendModel::getContainer()->get('database')->insert('catalog_specifications_values', $item);
	}

	/**
	 * Insert a related product in the database
	 *
	 * @param string $item
	 * @return int
	 */
	private static function insertRelatedProduct($item)
	{
		return (int)BackendModel::getContainer()->get('database')->insert('catalog_related_products', $item);
	}

	/**
	 * Insert a image in the database
	 *
	 * @param string $item
	 * @return int
	 */
	private static function insertImage($item)
	{
		return (int)BackendModel::getContainer()->get('database')->insert('catalog_images', $item);
	}

	/**
	 * Insert a file in the database
	 *
	 * @param string $item
	 * @return int
	 */
	private static function insertFile($item)
	{
		return (int)BackendModel::getContainer()->get('database')->insert('catalog_files', $item);
	}

	/**
	 * Insert a video in the database
	 *
	 * @param string $item
	 * @return int
	 */
	private static function insertVideo($item)
	{
		return (int)BackendModel::getContainer()->get('database')->insert('catalog_videos', $item);
	}

	/**
	 * Insert a brand in the database
	 *
	 * @param array $item
	 * @return int
	 */
	public static function insertBrand(array $item)
	{
		$item['created_on'] = BackendModel::getUTCDate();
	        $item['edited_on'] = BackendModel::getUTCDate();
		return BackendModel::getContainer()->get('database')->insert('catalog_brands', $item);
	}

	/**
	 * Is this category allowed to be deleted?
	 *
	 * @return    bool
	 * @param    int $id The category id to check.
	 */
	public static function isCategoryAllowedToBeDeleted($id)
	{
		return !(bool)BackendModel::getContainer()->get('database')->getVar('SELECT COUNT(i.id)
											FROM catalog_products AS i
											INNER JOIN catalog_categories AS c
											WHERE i.category_id = ? OR c.parent_id = ?
											', array((int)$id, (int)$id));
	}

	/**
	 * Is this brand allowed to be deleted?
	 *
	 * @return    bool
	 * @param    int $id The category id to check.
	 */
	public static function isBrandAllowedToBeDeleted($id)
	{
		return !(bool)BackendModel::getContainer()->get('database')->getVar('SELECT COUNT(i.id)
											FROM catalog_products AS i
											INNER JOIN catalog_brands AS c
											WHERE i.brand_id = ?
											', array((int)$id));
	}

	/**
	 * Recalculate the commentcount
	 *
	 * @param array $ids The id(s) of the product wherefore the commentcount should be recalculated.
	 * @return bool
	 */
	public static function reCalculateCommentCount(array $ids)
	{
		// validate
		if(empty($ids)) return false;

		// make unique ids
		$ids = array_unique($ids);

		// get db
		$db = BackendModel::getContainer()->get('database');

		// get counts
		$commentCounts = (array)$db->getPairs('SELECT i.product_id, COUNT(i.id) AS comment_count
			 FROM catalog_comments AS i
			 INNER JOIN catalog_products AS p ON i.product_id = p.id AND i.language = p.language
			 WHERE i.status = ? AND i.product_id IN (' . implode(',', $ids) . ') AND i.language = ?
			 GROUP BY i.product_id', array('published', BL::getWorkingLanguage()));

		foreach($ids as $id)
		{
			// get count
			$count = (isset($commentCounts[$id])) ? (int)$commentCounts[$id] : 0;

			// update
			$db->update('catalog_products', array('num_comments' => $count), 'id = ? AND language = ?', array($id, BL::getWorkingLanguage()));
		}

		return true;
	}

	/**
	 * Save or update related product (values)
	 *
	 * @param int $productId The id of the item where to assign the related projects.
	 * @param array $relatedProducts The related products for the item.
	 * @param array [optional] $oRelatedProducts The related products already existing for the item. If not provided a new record will be created.
	 *
	 * @return int
	 */
	public static function saveRelatedProducts($productId, $relatedProducts, $oRelatedProducts = null)
	{
		$item['product_id'] = $productId;

		// existing related products
		if(isset($oRelatedProducts))
		{
			// insert new records
			$newRelatedProducts = array_diff($relatedProducts, $oRelatedProducts);
			foreach($newRelatedProducts AS $key => $newRelatedProduct)
			{
				$item['related_product_id'] = $newRelatedProduct;
				self::insertRelatedProduct($item);
			}

			// delete old records
			$oldRelatedProducts = array_diff($oRelatedProducts, $relatedProducts);
			foreach($oldRelatedProducts AS $key => $oldRelatedProduct)
			{
				$item['related_product_id'] = $oldRelatedProduct;
				self::deleteRelatedProduct($item['product_id'], $item['related_product_id']);
			}
		}
		else
		{
			// new related products
			// insert new records
			foreach($relatedProducts AS $key => $relatedProduct)
			{
				$item['related_product_id'] = $relatedProduct;
				self::insertRelatedProduct($item);
			}
		}
	}

	/**
	 * Save or update a image
	 *
	 * @param array $item
	 * @return int
	 */
	public static function saveImage(array $item)
	{
		// update image
		if(isset($item['id']) && self::existsImage($item['id']))
		{
			self::updateImage($item);
		}
		else
		{
			// insert image
			$item['id'] = self::insertImage($item);
		}

		BackendModel::invalidateFrontendCache('productsCache');
		return (int)$item['id'];
	}

	/**
	 * Save or update a file
	 *
	 * @param array $item
	 * @return int
	 */
	public static function saveFile(array $item)
	{
		// update file
		if(isset($item['id']) && self::existsFile($item['id']))
		{
			self::updateFile($item);
		}
		else
		{
			// insert file
			$item['id'] = self::insertFile($item);
		}

		BackendModel::invalidateFrontendCache('productsCache');
		return (int)$item['id'];
	}

	/**
	 * Save or update a video
	 *
	 * @param array $item
	 * @return int
	 */
	public static function saveVideo(array $item)
	{
		// update video
		if(isset($item['id']) && self::existsVideo($item['id']))
		{
			self::updateVideo($item);
		}
		else
		{
			// insert video
			$item['id'] = self::insertVideo($item);
		}

		BackendModel::invalidateFrontendCache('productsCache');
		return (int)$item['id'];
	}

	/**
	 * Updates an item
	 *
	 * @param array $item
	 */
	public static function update(array $item)
	{
		$item['edited_on'] = BackendModel::getUTCDate();
		BackendModel::getContainer()->get('database')->update('catalog_products', $item, 'id = ?', (int)$item['id']);
	}

	/**
	 * Update a certain category
	 *
	 * @param array $item
	 */
	public static function updateCategory(array $item)
	{
		$item['edited_on'] = BackendModel::getUTCDate();
		BackendModel::getContainer()->get('database')->update('catalog_categories', $item, 'id = ?', array($item['id']));
	}

	/**
	 * Update a certain brand
	 *
	 * @param array $item
	 */
	public static function updateBrand(array $item)
	{
		$item['edited_on'] = BackendModel::getUTCDate();
		BackendModel::getContainer()->get('database')->update('catalog_brands', $item, 'id = ?', array($item['id']));
	}

	/**
	 * Update a certain comment
	 *
	 * @param array $item The new data.
	 * @return int
	 */
	public static function updateComment(array $item)
	{
		return BackendModel::getContainer()->get('database')->update('catalog_comments', $item, 'id = ?', array((int)$item['id']));
	}

	/**
	 * Update a certain order
	 *
	 * @param array $item The new data.
	 * @return int
	 */
	public static function updateOrder(array $item)
	{
		return BackendModel::getContainer()->get('database')->update('catalog_orders', $item, 'id = ?', array((int)$item['id']));
	}

	/**
	 * Updates one or more order' status
	 *
	 * @param array $ids The id(s) of the order(s) to change the status for.
	 * @param string $status The new status.
	 */
	public static function updateOrderStatuses($ids, $status)
	{
		// make sure $ids is an array
		$ids = (array)$ids;

		// loop and cast to integers
		foreach($ids as &$id) $id = (int)$id;

		// create an array with an equal amount of questionmarks as ids provided
		$idPlaceHolders = array_fill(0, count($ids), '?');

		// get the items and their languages
		$items = (array)BackendModel::getContainer()->get('database')->getPairs('SELECT i.id, i.status
			 FROM catalog_orders AS i
			 WHERE i.id IN (' . implode(', ', $idPlaceHolders) . ')', $ids, 'id');

		// only proceed if there are items
		if(!empty($items))
		{
			// get the ids
			$itemIds = array_keys($items);

			// update records
			BackendModel::getContainer()->get('database')->execute('UPDATE catalog_orders
				 SET status = ?
				 WHERE id IN (' . implode(', ', $idPlaceHolders) . ')', array_merge(array((string)$status), $ids));
		}
	}

	/**
	 * Updates one or more comments' status
	 *
	 * @param array $ids The id(s) of the comment(s) to change the status for.
	 * @param string $status The new status.
	 */
	public static function updateCommentStatuses($ids, $status)
	{
		// make sure $ids is an array
		$ids = (array)$ids;

		// loop and cast to integers
		foreach($ids as &$id) $id = (int)$id;

		// create an array with an equal amount of questionmarks as ids provided
		$idPlaceHolders = array_fill(0, count($ids), '?');

		// get the items and their languages
		$items = (array)BackendModel::getContainer()->get('database')->getPairs('SELECT i.product_id, i.language
			 FROM catalog_comments AS i
			 WHERE i.id IN (' . implode(', ', $idPlaceHolders) . ')', $ids, 'product_id');

		// only proceed if there are items
		if(!empty($items))
		{
			// get the ids
			$itemIds = array_keys($items);

			// get the unique languages
			$languages = array_unique(array_values($items));

			// update records
			BackendModel::getContainer()->get('database')->execute('UPDATE catalog_comments
				 SET status = ?
				 WHERE id IN (' . implode(', ', $idPlaceHolders) . ')', array_merge(array((string)$status), $ids));

			// recalculate the comment count
			self::reCalculateCommentCount($itemIds);

			// invalidate the cache for blog
			foreach($languages as $language) BackendModel::invalidateFrontendCache('catalog', $language);
		}
	}

	/**
	 * Update a certain specification
	 *
	 * @param int $id
	 * @param array $item
	 */
	public static function updateSpecification($id, $item)
	{
		BackendModel::getContainer()->get('database')->update('catalog_specifications', $item, 'id = ?', array($id));
	}

	/**
	 * Update value of a certain specification
	 *
	 * @param int $specificationId
	 * @param int $productId
	 * @param array $item
	 */
	public static function updateSpecificationValue($specificationId, $productId, $item)
	{
		BackendModel::getContainer()->get('database')->update('catalog_specifications_values', $item, 'specification_id = ? AND product_id = ?', array((int)$specificationId, (int)$productId));
	}

	/**
	 * @param array $item
	 * @return int
	 */
	public static function updateImage(array $item)
	{
		BackendModel::invalidateFrontendCache('productsCache');
		return (int)BackendModel::getContainer()->get('database')->update('catalog_images', $item, 'id = ?', array($item['id']));
	}

	/**
	 * @param array $item
	 * @return int
	 */
	public static function updateFile(array $item)
	{
		BackendModel::invalidateFrontendCache('productsCache');
		return (int)BackendModel::getContainer()->get('database')->update('catalog_files', $item, 'id = ?', array($item['id']));
	}

	/**
	 * @param array $item
	 * @return int
	 */
	public static function updateVideo(array $item)
	{
		BackendModel::invalidateFrontendCache('productsCache');
		return (int)BackendModel::getContainer()->get('database')->update('catalog_videos', $item, 'id = ?', array($item['id']));
	}
}
