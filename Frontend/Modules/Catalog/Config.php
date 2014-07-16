<?php

namespace Frontend\Modules\Catalog;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Config as FrontendBaseConfig;
 
/**
 * This is the configuration-object for the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Config extends FrontendBaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'Index';

    /**
     * The disabled actions
     *
     * @var array
     */
    protected $disabledActions = array();
}
