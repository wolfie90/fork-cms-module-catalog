<?php

namespace Frontend\Modules\Catalog\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Language as FL;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Engine\Url as FrontendURL;
use Frontend\Modules\Tags\Engine\Model as FrontendTagsModel;
use Frontend\Modules\Tags\Engine\TagsInterface as FrontendTagsInterface;

/**
 * In this file we store all generic functions that we will be using in the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Model implements FrontendTagsInterface
{
	/**
	 * Fetches a certain item
	 *
	 * @param string $url
	 * @param int $id
	 * @return array
	 */
	public static function get($url = null, $id = null)
	{
		if(!$id)
		{
			$item = (array)FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
				 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
				 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
				 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url, m2.url AS category_url, m2.title AS category_title
				 FROM catalog_products AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 INNER JOIN catalog_categories AS c ON i.category_id = c.id
				 INNER JOIN meta AS m2 ON c.meta_id = m2.id
				 WHERE m.url = ? AND i.language = ?', array((string)$url, FRONTEND_LANGUAGE));
		}
		else
		{
			$item = (array)FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
				 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
				 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
				 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url, m2.url AS category_url, m2.title AS category_title
				 FROM catalog_products AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 INNER JOIN catalog_categories AS c ON i.category_id = c.id
				 INNER JOIN meta AS m2 ON c.meta_id = m2.id
				 WHERE i.id = ? AND i.language = ?', array((int)$id, FRONTEND_LANGUAGE));
		}

		// no results?
		if(empty($item)) return array();

		// create full url
		$item['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Detail') . '/' . $item['url'];
		$item['category_full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Category') . '/' . $item['category_url'];

		// create image
		$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM catalog_images WHERE product_id = ? ORDER BY sequence', array((int)$item['id']));
		if($img) $item['image'] = FRONTEND_FILES_URL . '/catalog/' . $item['id'] . '/200x200/' . $img['filename'];

		return $item;
	}

	/**
	 * Fetches a certain item
	 *
	 * @param int id
	 * @return array
	 */
	public static function getProductSpecifications($id)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*, c.value
			 FROM catalog_specifications AS i
			 INNER JOIN catalog_specifications_values AS c ON i.id = c.specification_id
			 WHERE c.product_id = ?', array((int)$id));

		return $items;
	}

	/**
	 * Fetches a certain item
	 *
	 * @param int id
	 * @return array
	 */
	public static function getProductsByOrder($id)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT o.*, c.title, c.price, m.url
			 FROM `catalog_orders_values` AS o
			 INNER JOIN `catalog_products` AS c ON o.product_id = c.id
			 INNER JOIN meta AS m ON c.meta_id = m.id
			 WHERE o.order_id = ?', array((int)$id));

		// calculate total amount
		foreach($items as &$item)
		{
			// get image
			$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM catalog_images WHERE product_id = ? ORDER BY sequence', array((int)$item['product_id']));
			if($img) $item['image'] = FRONTEND_FILES_URL . '/catalog/' . $item['product_id'] . '/64x64/' . $img['filename'];
			else $item['image'] = '/' . APPLICATION . '/modules/catalog/layout/images/dummy.png';

			// create full url
			$item['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Detail') . '/' . $item['url'];

			// calculate subtotal
			$productAmount = (int)$item['amount'];
			$productPrice = (int)$item['price'];
			$item['subtotal_price'] = $productAmount * $productPrice;
		}

		return $items;
	}

	/**
	 * Get all items (at least a chunk)
	 *
	 * @param int [optional] $limit The number of items to get.
	 * @param int [optional] $offset The offset.
	 * @return array
	 */
	public static function getAll($limit = 10, $offset = 0)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*, m.url
			 FROM catalog_products AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.language = ?
			 ORDER BY i.created_on ASC, i.id DESC LIMIT ?, ?', array(FRONTEND_LANGUAGE, (int)$offset, (int)$limit));

		// no results?
		if(empty($items)) return array();

		// get detail action url
		$detailUrl = FrontendNavigation::getURLForBlock('Catalog', 'Detail');

		// prepare items for search
		foreach($items as &$item)
		{
			$item['full_url'] = $detailUrl . '/' . $item['url'];
		}

		// return
		return $items;
	}

	/**
	 * Get the number of items
	 *
	 * @return int
	 */
	public static function getAllCount()
	{
		return (int)FrontendModel::getContainer()->get('database')->getVar('SELECT COUNT(i.id) AS count
			 FROM catalog_products AS i');
	}

	/**
	 * Get all category items (at least a chunk)
	 *
	 * @param int $categoryId
	 * @param int [optional] $limit The number of items to get.
	 * @param int [optional] $offset The offset.
	 * @return array
	 */
	public static function getAllByCategory($categoryId, $limit = 10, $offset = 0)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*, m.url
			 FROM catalog_products AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.category_id = ? AND i.language = ?
			 ORDER BY i.sequence ASC, i.id DESC LIMIT ?, ?', array($categoryId, FRONTEND_LANGUAGE, (int)$offset, (int)$limit));

		// no results?
		if(empty($items)) return array();

		// get detail action url
		$detailUrl = FrontendNavigation::getURLForBlock('Catalog', 'Detail');

		// prepare items for search
		foreach($items as &$item)
		{
			$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM catalog_images WHERE product_id = ? ORDER BY sequence', array((int)$item['id']));
			if($img) $item['image'] = FRONTEND_FILES_URL . '/catalog/' . $item['id'] . '/200x200/' . $img['filename'];
			else $item['image'] = '/' . APPLICATION . '/modules/catalog/layout/images/dummy.png';

			$item['full_url'] = $detailUrl . '/' . $item['url'];
		}

		// return
		return $items;
	}

	/**
	 * Get all category items within a tree structure
	 *
	 * @return array
	 */
	public static function getCategoriesTree($id = 0)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT c.id AS id, c.title, m.url, COUNT(p.id) AS total, m.data AS meta_data, parent_id
				 FROM catalog_categories AS c
				 INNER JOIN meta AS m ON c.meta_id = m.id
				 LEFT OUTER JOIN catalog_products AS p ON p.category_id = c.id
				 WHERE c.language = ?
				 GROUP BY c.id
				 ORDER BY c.sequence', array(FRONTEND_LANGUAGE), 'id');

		// init var
		$baseUrl = FrontendNavigation::getURLForBlock('Catalog', 'Category');

		// loop items and unserialize
		foreach($items as &$row)
		{
			// set image path
			$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM catalog_categories WHERE id = ?', array((int)$row['id']));
			if($img) $row['image'] = FRONTEND_FILES_URL . '/catalog/categories/' . $row['id'] . '/source/' . $img['image'];
			else $row['image'] = '/' . APPLICATION . '/modules/catalog/layout/images/dummy.png';;

			// set nested urls
			$paths = self::traverseUp($items, $row);
			if(!empty($paths))
			{
				$url = implode('/', $paths);
				$row['full_url'] = $baseUrl . '/' . $url;
			}

			if(isset($row['meta_data'])) $row['meta_data'] = @unserialize($row['meta_data']);
		}

		// convert flat array in tree
		if($id != null)
		{
			// parent id is given
			$items = self::buildTree($items, $id);
		}
		else
		{
			$items = self::buildTree($items);
		}

		return $items;
	}

	/**
	 * Fetch the list of tags for a list of items
	 *
	 * @param array $ids The ids of the items to grab.
	 * @return array
	 */
	public static function getForTags(array $ids)
	{
		// fetch items
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.title, m.url
		 FROM blog_posts AS i
		 INNER JOIN meta AS m ON m.id = i.meta_id
		 WHERE i.status = ? AND i.hidden = ? AND i.id IN (' . implode(',', $ids) . ')
		 ORDER BY i.publish_on DESC', array('active', 'N'));

		// has items
		if(!empty($items))
		{
			// init var
			$link = FrontendNavigation::getURLForBlock('Catalog', 'Detail');
			$folders = FrontendModel::getThumbnailFolders(FRONTEND_FILES_PATH . '/Catalog/Images', true);

			// reset url
			foreach($items as &$row)
			{
				$row['full_url'] = $link . '/' . $row['url'];

				// image?
				if(isset($row['image']))
				{
					foreach($folders as $folder)
					{
						$row['image_' . $folder['dirname']] = $folder['url'] . '/' . $folder['dirname'] . '/' . $row['image'];
					}
				}
			}
		}

		// return
		return $items;
	}

	/**
	 * Get the id of an item by the full URL of the current page.
	 * Selects the proper part of the full URL to get the item's id from the database.
	 *
	 * @param FrontendURL $URL The current URL.
	 * @return int
	 */
	public static function getIdForTags(FrontendURL $URL)
	{
		// select the proper part of the full URL
		$itemURL = (string)$URL->getParameter(1);

		// return the item
		return self::get($itemURL);
	}

	/**
	 * Build the category tree
	 *
	 * @param array $items
	 * @return array
	 */
	private static function buildTree($items, $id = 0)
	{
		$children = array();

		// loop parents
		foreach($items as &$item)
		{
			$children[(!empty($item['parent_id']) ? $item['parent_id'] : 0)][] = & $item;
			unset($item);
		}

		// loop children
		foreach($items as &$item)
		{
			// if children
			if(isset($children[$item['id']]))
			{
				// insert
				$item['children'] = $children[$item['id']];
			}
		}

		// check if children exists
		if(isset($children[$id]))
		{
			return $children[$id];
		}
		else
		{
			// if no children return empty array
			return array();
		}
	}

	/**
	 * Get the tree in HTML
	 *
	 * @return string
	 */
	public static function getTreeHTML($tree)
	{
		$html = '<ul>';

		// loop tree
		foreach($tree as &$item)
		{
			// set parent
			$html .= '<li><a href="' . $item['full_url'] . '">' . $item['title'] . '</a>';

			if(!empty($item['children']))
			{
				$html .= '<ul>' . self::getTreeChildren($item['children']) . '</ul>';
			}

			$html .= '</li>';
		}

		$html .= "</ul>";

		if($html == '<ul></ul>')
		{
			return array();
		}
		else
		{
			return $html;
		}
	}

	/**
	 * Get children of tree
	 *
	 * @return string
	 */
	public static function getTreeChildren($treeChildren)
	{
		$html = '';

		// loop children tree
		foreach($treeChildren as &$item)
		{

			// set children
			$html .= '<li><a href="' . $item['full_url'] . '">' . $item['title'] . '</a>';

			if(!empty($item['children']))
			{
				$html .= '<ul>' . self::getTreeChildren($item['children']) . '</ul>';
			}

			$html .= '</li>';
		}

		return $html;
	}

	/**
	 * Create paths for category urls
	 *
	 * @param array $items
	 * @param array $item
	 * @return array
	 */
	public static function traverseUp($items, $item)
	{
		$paths = array();
		while(!empty($item))
		{
			$paths[$item['id']] = $item['url'];
			if(!empty($items[$item['parent_id']]) && !isset($paths[$item['parent_id']]))
			{
				$item = $items[$item['parent_id']];
			}
			else
			{
				$paths[$item['id']] = $item['url'];
				$item = null;
			}
		}

		$paths = array_reverse($paths, true);

		return $paths;
	}

	/**
	 * Get all categories
	 *
	 * @param int $id
	 * @param string $url
	 * @return array
	 */
	public static function getAllCategories($id = 0, $url = null)
	{

		// category id given
		if($id != 0)
		{
			$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT c.id, c.title, m.url, COUNT(p.id) AS total, m.data AS meta_data
				 FROM catalog_categories AS c
				 INNER JOIN meta AS m ON c.meta_id = m.id
				 LEFT OUTER JOIN catalog_products AS p ON p.category_id = c.id
				 WHERE c.parent_id = ? AND c.language = ?
				 GROUP BY c.id
				 ORDER BY c.sequence', array($id, FRONTEND_LANGUAGE), 'id');
		}
		else
		{
			// no category given
			$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT c.id, c.title, m.url, COUNT(p.id) AS total, m.data AS meta_data, parent_id
				 FROM catalog_categories AS c
				 INNER JOIN meta AS m ON c.meta_id = m.id
				 LEFT OUTER JOIN catalog_products AS p ON p.category_id = c.id
				 WHERE c.language = ?
				 GROUP BY c.id
				 ORDER BY c.sequence', array(FRONTEND_LANGUAGE), 'id');
		}

		// loop items and unserialize
		foreach($items as &$row)
		{
			// set image path
			$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM catalog_categories WHERE id = ?', array((int)$row['id']));
			if($img) $row['image'] = FRONTEND_FILES_URL . '/catalog/categories/' . $row['id'] . '/source/' . $img['image'];
			else $row['image'] = '/' . APPLICATION . '/modules/projects/layout/images/dummy.png';;

			// create full url
			if($url != null)
			{
				$row['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Category') . '/' . $url . '/' . $row['url'];
			}
			else
			{
				$row['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Category') . '/' . $row['url'];
			}

			if(isset($row['meta_data'])) $row['meta_data'] = @unserialize($row['meta_data']);
		}

		return $items;
	}

	/**
	 * Fetches a certain category
	 *
	 * @param string $URL
	 * @return array
	 */
	public static function getCategory($URL)
	{
		$item = (array)FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url
			 FROM catalog_categories AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE m.url = ? AND i.language = ?', array((string)$URL, FRONTEND_LANGUAGE));

		// no results?
		if(empty($item)) return array();

		// create full url
		$item['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Category') . '/' . $item['url'];

		return $item;
	}

	/**
	 * Fetches a certain category
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getCategoryById($id)
	{
		$item = (array)FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url
			 FROM catalog_categories AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.id = ? AND i.language = ?', array((int)$id, FRONTEND_LANGUAGE));

		// no results?
		if(empty($item)) return array();

		// create full url
		$item['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Category') . '/' . $item['url'];

		return $item;
	}

	/**
	 * Get the number of items in a category
	 *
	 * @param int $categoryId
	 * @return int
	 */
	public static function getCategoryCount($categoryId)
	{
		return (int)FrontendModel::getContainer()->get('database')->getVar('SELECT COUNT(i.id) AS count
			 FROM catalog_products AS i
			 WHERE i.category_id = ?', array((int)$categoryId));
	}

	/**
	 * Get the comments for an item
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getComments($id)
	{
		// get the comments
		$comments = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT c.id, UNIX_TIMESTAMP(c.created_on) AS created_on, c.text, c.data,
			 c.author, c.email, c.website
			 FROM catalog_comments AS c
			 WHERE c.product_id = ? AND c.status = ? AND c.language = ?
			 ORDER BY c.id ASC', array((int)$id, 'published', FRONTEND_LANGUAGE));

		// loop comments and create gravatar id
		foreach($comments as &$row)
		{
			$row['gravatar_id'] = md5($row['email']);
		}

		// return
		return $comments;
	}

	/**
	 * Get all images for a product
	 *
	 * @param int $id
	 * @param array $settings
	 * @return array
	 */
	public static function getImages($id, $settings)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*
			 FROM catalog_images AS i
			 WHERE i.product_id = ?
			 ORDER BY i.sequence', array((int)$id));

		// init var
		$link = FrontendNavigation::getURLForBlock('Catalog', 'Category');

		// build the item url
		foreach($items as &$item)
		{
			$item['image_thumb'] = FRONTEND_FILES_URL . '/Catalog/' . $item['product_id'] . '/64x64/' . $item['filename'];
			$item['image_big'] = FRONTEND_FILES_URL . '/Catalog/' . $item['product_id'] . '/' . $settings["width1"] . 'x' . $settings["height1"] . '/' . $item['filename'];
		}

		return $items;
	}

	/**
	 * Get all videos for a product
	 *
	 * @param $id
	 * @return array
	 */
	public static function getVideos($id)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*
		 FROM catalog_videos AS i
		 WHERE i.product_id = ?
		 ORDER BY i.sequence', array((int)$id));

		// build the image thumbnail for youtube/vimeo
		foreach($items as &$item)
		{
			// YOUTUBE
			if(strpos($item['embedded_url'], 'youtube') !== false)
			{
				$ytQuery = parse_url($item['embedded_url'], PHP_URL_QUERY);
				parse_str($ytQuery, $ytData);

				if(isset($ytData['v']))
				{
					$item['video_id'] = $ytData['v'];
					$item['url'] = "http://www.youtube.com/v/" . $ytData['v'] . "?fs=1&amp;autoplay=1";
					$item['image'] = "http://i3.ytimg.com/vi/" . $ytData['v'] . "/default.jpg";
				}
				// VIMEO
			}
			else if(strpos($item['embedded_url'], 'vimeo') !== false)
			{
				$vmLink = str_replace('http://vimeo.com/', 'http://vimeo.com/api/v2/video/', $item['embedded_url']) . '.php';
				$vmData = unserialize(file_get_contents($vmLink));

				if(isset($vmData[0]['id']))
				{
					$item['video_id'] = $vmData[0]['id'];;
					$item['url'] = "http://player.vimeo.com/video/" . $vmData[0]['id'] . "?autoplay=1";
					$item['image'] = $vmData[0]['thumbnail_small'];;
				}
			}
			else
			{
				// NO YOUTUBE OR VIMEO URL GIVEN..
			}
		}

		return $items;
	}

	/**
	 * Get all files for a product
	 *
	 * @param int $id
	 * @param array $settings
	 * @return array
	 */
	public static function getFiles($id)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*
			 FROM catalog_files AS i
			 WHERE i.product_id = ?
			 ORDER BY i.sequence', array((int)$id));

		// build the item url
		foreach($items as &$item)
		{
			$item['url'] = FRONTEND_FILES_URL . '/catalog/' . $item['product_id'] . '/source/' . $item['filename'];
		}

		return $items;
	}

	/**
	 * Get order
	 *
	 * @param $id
	 * @return array
	 */
	public static function getOrder($id)
	{
		$item = (array)FrontendModel::getContainer()->get('database')->getRecord('SELECT c.*
			 FROM catalog_orders AS c', array($id), 'id');

		return $item;
	}

	/**
	 * Get related products
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getRelatedProducts($id)
	{
		$relatedIds = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*
			 FROM  catalog_related_products AS i
			 WHERE i.product_id = ?', array((int)$id));

		$relatedProducts = array();

		foreach($relatedIds as $relatedProduct)
		{
			$relatedProducts[] = self::get(null, $relatedProduct['related_product_id']);
		}

		return $relatedProducts;
	}

	/**
	 * Does the order exist?
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function existsOrder($id)
	{
		return (bool)FrontendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_orders AS i
			 WHERE i.id = ?
			 LIMIT 1', array((int)$id));
	}

	/**
	 * Do the values of the order exist?
	 *
	 * @param int $productId
	 * @param int $orderId
	 * @return bool
	 */
	public static function existsOrderValue($productId, $orderId)
	{
		return (bool)FrontendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_orders_values AS i
			 WHERE i.product_id = ? AND i.order_id = ?
			 LIMIT 1', array((int)$productId, (int)$orderId));
	}

	/**
	 * Insert a new comment
	 *
	 * @param array $comment
	 * @return int
	 */
	public static function insertComment(array $comment)
	{
		// get db
		$db = FrontendModel::getContainer()->get('database');

		// insert comment
		$comment['id'] = (int)$db->insert('catalog_comments', $comment);

		// recalculate if published
		if($comment['status'] == 'published')
		{
			// num comments
			$numComments = (int)FrontendModel::getContainer()->get('database')->getVar('SELECT COUNT(i.id) AS comment_count
				 FROM catalog_comments AS i
				 INNER JOIN catalog_products AS p ON i.product_id = p.id AND i.language = p.language
				 WHERE i.status = ? AND i.product_id = ? AND i.language = ?
				 GROUP BY i.product_id', array('published', $comment['product_id'], FRONTEND_LANGUAGE));

			// update num comments
			$db->update('catalog_products', array('num_comments' => $numComments), 'id = ?', $comment['product_id']);
		}

		return $comment['id'];
	}

	/**
	 * Insert a new order
	 *
	 * @return int
	 */
	public static function insertOrder()
	{
		$order = array();

		$order['status'] = 'pending';
		$order['date'] = FrontendModel::getUTCDate();

		// get db
		$db = FrontendModel::getContainer()->get('database');

		// insert comment
		$order['id'] = (int)$db->insert('catalog_orders', $order);

		return $order['id'];
	}

	/**
	 * Insert a order value
	 *
	 * @param array $item
	 * @return int
	 */
	public static function insertOrderValue(array $item)
	{
		// set date
		$item['date'] = FrontendModel::getUTCDate();

		// get db
		$db = FrontendModel::getContainer()->get('database');

		return $db->insert('catalog_orders_values', $item);
	}

	/**
	 * Get moderation status for an author
	 *
	 * @param string $author
	 * @param string $email
	 * @return bool
	 */
	public static function isModerated($author, $email)
	{
		return (bool)FrontendModel::getContainer()->get('database')->getVar('SELECT 1
			 FROM catalog_comments AS c
			 WHERE c.status = ? AND c.author = ? AND c.email = ?
			 LIMIT 1', array('published', (string)$author, (string)$email));
	}

	/**
	 * Notify the admin
	 *
	 * @param array $comment
	 */
	public static function notifyAdmin(array $comment)
	{
		// don't notify admin in case of spam
		if($comment['status'] == 'spam') return;

		// build data for push notification
		if($comment['status'] == 'moderation') $key = 'CATALOG_COMMENT_MOD';
		else $key = 'CATALOG_COMMENT';

		$author = $comment['author'];
		if(mb_strlen($author) > 20) $author = mb_substr($author, 0, 19) . '…';
		$text = $comment['text'];
		if(mb_strlen($text) > 50) $text = mb_substr($text, 0, 49) . '…';

		$alert = array('loc-key' => $key, 'loc-args' => array($author, $text));

		// build data
		$data = array('api' => SITE_URL . '/api/1.0', 'id' => $comment['id']);

		// push it
		FrontendModel::pushToAppleApp($alert, null, 'default', $data);

		// get settings
		$notifyByMailOnComment = FrontendModel::getModuleSetting('catalog', 'notify_by_email_on_new_comment', false);
		$notifyByMailOnCommentToModerate = FrontendModel::getModuleSetting('catalog', 'notify_by_email_on_new_comment_to_moderate', false);

		// create URLs
		$URL = SITE_URL . FrontendNavigation::getURLForBlock('Catalog', 'Detail') . '/' . $comment['product_url'] . '#comment-' . $comment['id'];
		$backendURL = SITE_URL . FrontendNavigation::getBackendURLForBlock('Comments', 'Catalog') . '#tabModeration';

		// notify on all comments
		if($notifyByMailOnComment)
		{
			// init var
			$variables = null;

			// comment to moderate
			if($comment['status'] == 'moderation')
			{
				$variables['message'] = vsprintf(FL::msg('CatalogEmailNotificationsNewCommentToModerate'), array($comment['author'], $URL, $comment['product_title'], $backendURL));
			}
			elseif($comment['status'] == 'published')
			{
				// comment was published
				$variables['message'] = vsprintf(FL::msg('CatalogEmailNotificationsNewComment'), array($comment['author'], $URL, $comment['product_title']));
			}

			// send the mail
			FrontendMailer::addEmail(FL::msg('NotificationSubject'), FRONTEND_CORE_PATH . '/layout/templates/mails/notification.tpl', $variables);
		}
		elseif($notifyByMailOnCommentToModerate && $comment['status'] == 'moderation')
		{
			// only notify on new comments to moderate and if the comment is one to moderate
			// set variables
			$variables['message'] = vsprintf(FL::msg('CatalogEmailNotificationsNewCommentToModerate'), array($comment['author'], $URL, $comment['product_title'], $backendURL));

			// send the mail
			FrontendMailer::addEmail(FL::msg('NotificationSubject'), FRONTEND_CORE_PATH . '/layout/templates/mails/notification.tpl', $variables);
		}
	}

	/**
	 * Update an order
	 *
	 * @param array $item
	 * @param int $orderId
	 * @return int
	 */
	public static function updateOrder($item, $orderId)
	{
		// set date
		$item['date'] = FrontendModel::getUTCDate();

		// get db
		$db = FrontendModel::getContainer()->get('database');

		// update
		$db->update('catalog_orders', $item, 'id = ?', array((int)$orderId));
	}

	/**
	 * Update value within a order
	 *
	 * @param array $item
	 * @param int $orderId
	 * @param int $productId
	 * @return int
	 */
	public static function updateOrderValue($item, $orderId, $productId)
	{
		// set date
		$item['date'] = FrontendModel::getUTCDate();

		// get db
		$db = FrontendModel::getContainer()->get('database');

		// update
		$db->update('catalog_orders_values', $item, 'order_id = ? AND product_id = ?', array((int)$orderId, (int)$productId));
	}

	/**
	 * Delete all spam
	 *
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

		// invalidate the cache for blog
		BackendModel::invalidateFrontendCache('catalog', BL::getWorkingLanguage());
	}

	/**
	 * Delete a value within an order
	 *
	 * @param int orderId
	 * @param int productId
	 */
	public static function deleteOrderValue($orderId, $productId)
	{
		$db = BackendModel::getContainer()->get('database');

		// update record
		$db->delete('catalog_orders_values', 'order_id = ? AND product_id = ?', array((int)$orderId, (int)$productId));

		// invalidate the cache for catalog
		BackendModel::invalidateFrontendCache('catalog', BL::getWorkingLanguage());
	}

	/**
	 * Parse the search results for this module
	 *
	 * Note: a module's search function should always:
	 *        - accept an array of entry id's
	 *        - return only the entries that are allowed to be displayed, with their array's index being the entry's id
	 *
	 *
	 * @param array $ids The ids of the found results.
	 * @return array
	 */
	public static function search(array $ids)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.id, i.title, i.summary, i.text, m.url
			 FROM catalog_products AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.language = ? AND i.id IN (' . implode(',', $ids) . ')', array(FRONTEND_LANGUAGE), 'id');

		// get detail action url
		$detailUrl = FrontendNavigation::getURLForBlock('Catalog', 'Detail');

		// prepare items for search
		foreach($items as &$item)
		{
			$item['full_url'] = $detailUrl . '/' . $item['url'];
		}

		// return
		return $items;
	}

	/**
	 * Fetches a certain brand
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getBrand($id)
	{
		$item = (array)FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url
			 FROM catalog_brands AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.id=?', array((int)$id));

		// create full url
		$item['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Brand') . '/' . $item['url'];

		return $item;
	}

	/**
	 * Fetches a certain category
	 *
	 * @param string $URL
	 * @return array
	 */
	public static function getBrandFromUrl($URL)
	{
		$item = (array)FrontendModel::getContainer()->get('database')->getRecord('SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url
			 FROM catalog_brands AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE m.url = ?', array((string)$URL));


		// no results?
		if(empty($item)) return array();

		// create full url
		$item['full_url'] = FrontendNavigation::getURLForBlock('Catalog', 'Brand') . '/' . $item['url'];


		return $item;
	}

	/**
	 * Get all category items (at least a chunk)
	 *
	 * @param int $categoryId
	 * @param int [optional] $limit The number of items to get.
	 * @param int [optional] $offset The offset.
	 * @return array
	 */
	public static function getAllByBrand($brandId, $limit = 10, $offset = 0)
	{
		$items = (array)FrontendModel::getContainer()->get('database')->getRecords('SELECT i.*, m.url
			 FROM catalog_products AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.brand_id = ? AND i.language = ?
			 ORDER BY i.sequence ASC, i.id DESC LIMIT ?, ?', array($brandId, FRONTEND_LANGUAGE, (int)$offset, (int)$limit));

		// no results?
		if(empty($items)) return array();

		// get detail action url
		$detailUrl = FrontendNavigation::getURLForBlock('Catalog', 'Detail');

		// prepare items for search
		foreach($items as &$item)
		{
			$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM catalog_images WHERE product_id = ? ORDER BY sequence', array((int)$item['id']));
			if($img) $item['image'] = FRONTEND_FILES_URL . '/catalog/' . $item['id'] . '/200x200/' . $img['filename'];
			else $item['image'] = '/' . APPLICATION . '/modules/catalog/layout/images/dummy.png';

			$item['full_url'] = $detailUrl . '/' . $item['url'];
		}

		// return
		return $items;
	}
}