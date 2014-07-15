<?php

namespace Frontend\Modules\Catalog\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a widget for the shopping cart
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendCatalogWidgetShoppingCart extends FrontendBaseWidget
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
		if(!CommonCookie::exists('order_id')){
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
