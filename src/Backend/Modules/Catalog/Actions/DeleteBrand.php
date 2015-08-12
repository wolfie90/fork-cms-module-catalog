<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;

/**
 * This action will delete a brand
 *
 * @author Waldo Cosman <waldo@comsa.be>
 */
class DeleteBrand extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id == null || !BackendCatalogModel::existsBrand($this->id)) {
            $this->redirect(BackendModel::createURLForAction('brands') . '&error=non-existing');
        }

        // fetch the brand
        $this->record = (array)BackendCatalogModel::getBrand($this->id);

        // delete item
        BackendCatalogModel::deleteBrand($this->id);

        // trigger event
        BackendModel::triggerEvent($this->getModule(), 'after_delete_brand', array('item' => $this->record));

        // brand was deleted, so redirect
        $this->redirect(BackendModel::createURLForAction('brands') . '&report=deleted-brand&var=' . urlencode($this->record['title']));
    }
}
