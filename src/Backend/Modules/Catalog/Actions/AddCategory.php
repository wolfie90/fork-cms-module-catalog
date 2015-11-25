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
 
use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
 
/**
 * This is the add category-action, it will display a form to create a new category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class AddCategory extends BackendBaseActionAdd
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        $this->frm = new BackendForm('addCategory');
        
        $this->frm->addText('title');
        $this->frm->addImage('image');
        
        // get the categories
        $categories = BackendCatalogModel::getCategories(true);
        
        $this->frm->addDropdown('parent_id', $categories);

        $this->meta = new BackendMeta($this->frm, null, 'title', true);
        $this->meta->setURLCallback('Backend\Modules\Catalog\Engine\Model', 'getURLForCategory');
    }
    
    /**
     * Validate the form
     */
    private function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            $this->frm->cleanupFields();

            // validate fields
            $this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));
            
            if ($this->frm->getField('image')->isFilled()) {
                $this->frm->getField('image')->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly'));
                $this->frm->getField('image')->isAllowedMimeType(array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'), BL::err('JPGGIFAndPNGOnly'));
            }
            
            $this->meta->validate();

            if ($this->frm->isCorrect()) {
                // build item
                $item['title'] = $this->frm->getField('title')->getValue();
                $item['language'] = BL::getWorkingLanguage();
                $item['meta_id'] = $this->meta->save();
                $item['sequence'] = BackendCatalogModel::getMaximumCategorySequence() + 1;
                if ($this->frm->getField('parent_id')->getValue() <> 'no_category') {
                    $item['parent_id'] = $this->frm->getField('parent_id')->getValue();
                }
                
                // the image path
                $imagePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/categories';
                
                // create folders if needed
                $fs = new Filesystem();
                if (!$fs->exists($imagePath . '/source')) {
                    $fs->mkdir($imagePath . '/source');
                }
                if (!$fs->exists($imagePath . '/150x150')) {
                    $fs->mkdir($imagePath . '/150x150');
                }
                                
                // is there an image provided?
                if ($this->frm->getField('image')->isFilled()) {
                    // build the image name
                    $item['image'] = $this->meta->getUrl() . '.' . $this->frm->getField('image')->getExtension();

                    // upload the image & generate thumbnails
                    $this->frm->getField('image')->generateThumbnails($imagePath, $item['image']);
                }
                
                // save the data
                $item['id'] = BackendCatalogModel::insertCategory($item);
                
                // trigger event
                BackendModel::triggerEvent(
                    $this->getModule(),
                    'after_add_category',
                    array('item' => $item)
                );

                // everything is saved, so redirect to the overview
                $this->redirect(
                    BackendModel::createURLForAction('Categories') . '&report=added-category&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']
                );
            }
        }
    }
}
