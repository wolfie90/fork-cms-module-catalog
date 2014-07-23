<?php

namespace Frontend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Common\Cookie as Cookie;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Form as FrontendForm;
use Frontend\Core\Engine\Language as FL;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

/**
 * This is the personal-data-action (default), it will display a personal data form
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class PersonalData extends FrontendBaseBlock
{
	/**
	 * The url to checkout page
	 *
	 * @var	array
	 */
	private $checkoutUrl;

	/**
	 * The order id in cookie
	 *
	 * @var	int
	 */
	private $cookieOrderId;

	
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		
		$this->loadTemplate();
		$this->getData();
		
		$this->loadForm();
		$this->validateForm();
		
		$this->parse();
	}

	/**
	 * Load the data, don't forget to validate the incoming data
	 */
	private function getData()
	{
		// requested page
		$requestedPage = $this->URL->getParameter('page', 'int', 1);
		
		// get order
		$this->cookieOrderId = Cookie::get('order_id');
		
		// set checkout url
		$this->checkoutUrl = FrontendNavigation::getURLForBlock('Catalog', 'Checkout');
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new FrontendForm('personalDataForm');
		
		// init vars
		$email = (Cookie::exists('email')) ? Cookie::get('email') : null;
		$fname = (Cookie::exists('fname')) ? Cookie::get('fname') : null;
		$lname = (Cookie::exists('lname')) ? Cookie::get('lname') : null;
		$address = (Cookie::exists('address')) ? Cookie::get('address') : null;
		$hnumber = (Cookie::exists('hnumber')) ? Cookie::get('hnumber') : null;
		$postal = (Cookie::exists('postal')) ? Cookie::get('postal') : null;
		$hometown = (Cookie::exists('hometown')) ? Cookie::get('hometown') : null;
		
		// create elements
		$this->frm->addText('email', $email)->setAttributes(array('required' => null, 'type' => 'email'));
		$this->frm->addText('fname', $fname, null)->setAttributes(array('required' => null));
		$this->frm->addText('lname', $lname, null)->setAttributes(array('required' => null));
		$this->frm->addText('address', $address, null)->setAttributes(array('required' => null));
		$this->frm->addText('hnumber', $hnumber, null)->setAttributes(array('required' => null));
		$this->frm->addText('postal', $postal, null)->setAttributes(array('required' => null));
		$this->frm->addText('hometown', $hometown, null)->setAttributes(array('required' => null));
		
		$this->frm->addTextarea('message');
	}

	/**
	 * Validate the form
	 */ 
	private function validateForm()
	{
		// is the form submitted
		if($this->frm->isSubmitted()) {
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();
			
			// validate required fields
			$this->frm->getField('email')->isEmail(FL::err('EmailIsRequired'));
			$this->frm->getField('fname')->isFilled(FL::err('MessageIsRequired'));
			$this->frm->getField('lname')->isFilled(FL::err('MessageIsRequired'));
			$this->frm->getField('address')->isFilled(FL::err('MessageIsRequired'));
			$this->frm->getField('hnumber')->isFilled(FL::err('MessageIsRequired'));
			$this->frm->getField('postal')->isFilled(FL::err('MessageIsRequired'));
			$this->frm->getField('hometown')->isFilled(FL::err('MessageIsRequired'));
			
			// correct?
			if($this->frm->isCorrect()) {
				// build array
				$order['email'] = $this->frm->getField('email')->getValue();
				$order['fname'] = $this->frm->getField('fname')->getValue();
				$order['lname'] = $this->frm->getField('lname')->getValue();
				$order['address'] = $this->frm->getField('address')->getValue();
				$order['hnumber'] = $this->frm->getField('hnumber')->getValue();
				$order['postal'] = $this->frm->getField('postal')->getValue();
				$order['hometown'] = $this->frm->getField('hometown')->getValue();
				$order['status'] = 'moderation';
				
				// insert values in database
				FrontendCatalogModel::updateOrder($order, $this->cookieOrderId);
								
				// delete cookie
				$argument = 'order_id';
				unset($_COOKIE[(string) $argument]);
				setcookie((string) $argument, null, 1, '/');
								
				// set cookies person --> optional
				Cookie::set('email', $order['email']);
				Cookie::set('fname', $order['fname']);
				Cookie::set('lname', $order['lname']);
				Cookie::set('address', $order['address']);
				Cookie::set('hnumber', $order['hnumber']);
				Cookie::set('postal', $order['postal']);
				Cookie::set('hometown', $order['hometown']);
				Cookie::set('status', $order['status']);
				
				// trigger event
				FrontendModel::triggerEvent('Catalog', 'after_add_order', array('order' => $order));
				
				$url = FrontendNavigation::getURLForBlock('Catalog', 'OrderReceived');
				$this->redirect($url);
			}
		}
	}
	
	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// add css 
		$this->header->addCSS('/src/Frontend/Modules/' . $this->getModule() . '/Layout/Css/catalog.css');
		
		// url to checkout page
		$this->tpl->assign('checkoutUrl', $this->checkoutUrl);
		
		// parse the form
		$this->frm->parse($this->tpl);
	}
}
