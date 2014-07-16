<?php

namespace Frontend\Modules\Catalog\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Common\Cookie;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

/**
 * This is a widget for the shopping cart
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class ShoppingCart extends FrontendBaseWidget
{
	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();
		$this->loadTemplate();
		$this->getData();
		$this->parse();
	}

	/**
	 * Get the data
	 */
	private function getData()
	{
		// check if cookie exists
		if(!\SpoonCookie::exists('order_id')){
			return;
		}
	}
	
	/**
	 * Parse
	 */
	private function parse()
	{
		
	}
}
