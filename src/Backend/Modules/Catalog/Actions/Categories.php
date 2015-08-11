<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;

/**
 * This is the categories-action, it will display the overview of categories
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Categories extends BackendBaseActionIndex
{
    /**
     * The category where is filtered on
     *
     * @var    array
     */
    private $category;

    /**
     * The id of the category where is filtered on
     *
     * @var    int
     */
    private $categoryId;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        // set category id
        $this->categoryId = \SpoonFilter::getGetValue('category', null, null, 'int');
        if ($this->categoryId == 0) {
            $this->categoryId = null;
        } else {
            // get category
            $this->category = BackendCatalogModel::getCategory($this->categoryId);

            // reset
            if (empty($this->category)) {
                // reset GET to trick Spoon
                $_GET['category'] = null;

                // reset
                $this->categoryId = null;
            }
        }

        $this->loadDataGrid();
        $this->loadFilterForm();
        $this->parse();
        $this->display();
    }

    /**
     * Load the dataGrid
     */
    private function loadDataGrid()
    {
        // filter on category?
        if ($this->categoryId != null) {
            // create datagrid
            $this->dataGrid = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_CATEGORIES_WITH_CATEGORYID, array($this->categoryId, BL::getWorkingLanguage()));

            // set the URL
            $this->dataGrid->setURL('&amp;category=' . $this->categoryId, true);
        } else {
            $this->dataGrid = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_CATEGORIES, BL::getWorkingLanguage());
        }

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditCategory')) {
            $this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit_category') . '&amp;id=[id]');

            $this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_category') . '&amp;id=[id]', BL::lbl('Edit'));
        }

        // sequence
        $this->dataGrid->enableSequenceByDragAndDrop();
        $this->dataGrid->setAttributes(array('data-action' => 'SequenceCategories'));
    }

    /**
     * Parse & display the page
     */
    protected function parse()
    {
        $this->tpl->assign('dataGrid', (string)$this->dataGrid->getContent());
    }

    private function loadFilterForm()
    {
        // get categories
        $categories = BackendCatalogModel::getCategories(true);

        // multiple categories?
        if (count($categories) > 1) {
            // create form
            $frm = new BackendForm('filter', null, 'get', false);

            // create element
            $frm->addDropdown('category', $categories, $this->categoryId);
//			$frm->getField('category')->setDefaultElement('');

            // parse the form
            $frm->parse($this->tpl);
        }

        // parse category
        if (!empty($this->category)) {
            $this->tpl->assign('filterCategory', $this->category);
        }
    }
}
