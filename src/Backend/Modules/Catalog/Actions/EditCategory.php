<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;
 
use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;

/**
 * This is the edit category action, it will display a form to edit an existing category.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class EditCategory extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->getData();
        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    /**
     * Get the data
     */
    private function getData()
    {
        $this->id = $this->getParameter('id', 'int');
        if ($this->id == null || !BackendCatalogModel::existsCategory($this->id)) {
            $this->redirect(
                BackendModel::createURLForAction('categories') . '&error=non-existing'
            );
        }

        $this->record = BackendCatalogModel::getCategory($this->id);
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        // create form
        $this->frm = new BackendForm('editCategory');
        $this->frm->addText('title', $this->record['title']);
        $this->frm->addImage('image');
        $this->frm->addCheckbox('delete_image');

        //hidden values
        $categories = BackendCatalogModel::getCategories(true);
        
        $this->frm->addDropdown('parent_id', $categories, $this->record['parent_id']);

        $this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
        $this->meta->setUrlCallback('Backend\Modules\Catalog\Engine\Model', 'getURLForCategory', array($this->record['id']));
    }

    /**
     * Parse the form
     */
    protected function parse()
    {
        parent::parse();

        // assign the data
        $this->tpl->assign('item', $this->record);
        
        // is category allowed to be deleted?
        if (BackendCatalogModel::isCategoryAllowedToBeDeleted($this->id)) {
            $this->tpl->assign('showDelete', true);
        }
    }

    /**
     * Validate the form
     */
    private function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            $this->frm->cleanupFields();

            $recordId = $this->record['id'];
            $newParent = $this->frm->getField('parent_id')->getValue();
            
            if ($recordId == $newParent) {
                $this->frm->getField('parent_id')->setError(BL::err('SameCategory'));
            }
            
            // validate fields
            $this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));
            
            if ($this->frm->getField('image')->isFilled()) {
                $this->frm->getField('image')->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly'));
                $this->frm->getField('image')->isAllowedMimeType(array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'), BL::err('JPGGIFAndPNGOnly'));
            }
            
            $this->meta->validate();
            
            if ($this->frm->isCorrect()) {
                // build item
                $item['id'] = $this->id;
                $item['language'] = $this->record['language'];
                $item['title'] = $this->frm->getField('title')->getValue();
                $item['parent_id'] = $this->frm->getField('parent_id')->getValue();
                $item['meta_id'] = $this->meta->save(true);

                // the image path
                $imagePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/categories/' . $this->id;
                
                // create folders if needed
                $fs = new Filesystem();
                
                if (!$fs->exists($imagePath . '/150x150/')) {
                    $fs->mkdir($imagePath . '/150x150/');
                }
                
                if (!$fs->exists($imagePath . '/source/')) {
                    $fs->mkdir($imagePath . '/source/');
                }

                if ($this->frm->getField('delete_image')->isChecked()) {
                    BackendModel::deleteThumbnails($imagePath, $this->record['image']);
                    $item['image'] = null;
                }
                
                // image provided?
                if ($this->frm->getField('image')->isFilled()) {
                    // build the image name
                    $item['image'] = $this->meta->getUrl() . '.' . $this->frm->getField('image')->getExtension();

                    // upload the image & generate thumbnails
                    $this->frm->getField('image')->generateThumbnails($imagePath, $item['image']);
                }
                
                // update the item
                BackendCatalogModel::updateCategory($item);
                
                // trigger event
                BackendModel::triggerEvent(
                    $this->getModule(),
                    'after_edit_category',
                    array('item' => $item)
                );

                // everything is saved, so redirect to the overview
                $this->redirect(
                    BackendModel::createURLForAction('categories') . '&report=edited-category&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']
                );
            }
        }
    }
}
