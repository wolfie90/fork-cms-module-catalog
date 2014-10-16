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
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;

/**
 * This is the personal-data-action (default), it will display a personal data form
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class OrderReceived extends FrontendBaseBlock
{
    /**
     * The url for catalog index
     *
     * @var    array
     */
    private $catalogUrl;

    /**
     * First name of the person that submitted the order
     *
     * @var string
     */
    private $firstName;

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
        // requested page
        $requestedPage = $this->URL->getParameter('page', 'int', 1);
        $this->firstName = Cookie::get('fname');
        $this->catalogUrl = FrontendNavigation::getURLForBlock('Catalog');
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        $this->tpl->assign('catalogUrl', $this->catalogUrl);
        $this->tpl->assign('firstName', $this->firstName);
    }
}
