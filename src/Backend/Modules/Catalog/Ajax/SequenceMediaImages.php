<?php

namespace Backend\Modules\Catalog\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;

/**
 * Alters the sequence of media images
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class SequenceMediaImages extends BackendBaseAJAXAction
{
    /**
     * Execute the action
     */
    public function execute()
    {
        // call parent, this will probably add some general CSS/JS or other required files
        parent::execute();

        // get parameters
        $newIdSequence = trim(\SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            // build item
            $item['id'] = (int) $id;

            // change sequence
            $item['sequence'] = $i + 1;

            // update sequence
            if (BackendCatalogModel::existsImage($item['id'])) {
                BackendCatalogModel::updateImage($item);
            }
        }

        // success output
        $this->output(self::OK, null, 'sequence updated');
    }
}
