<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;

/**
 * This is the add-action, it will display a form to create a new product
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Add extends BackendBaseActionAdd
{
    /**
     * The product id
     *
     * @var	int
     */
    private $id;

    /**
     * All categories
     *
     * @var	array
     */
    private $categories;

    /**
     * Products grouped by categories
     *
     * @var	array
     */
    private $allProductsGroupedByCategories;

    /**
     * All specifications
     *
     * @var	array
     */
    private $specifications;

    /**
     * All brands
     *
     * @var	array
     */
    private $brands;

    /**
     * Execute the actions
     */
    public function execute()
    {
        parent::execute();

        $this->loadData();
        $this->loadForm();

        $this->validateForm();

        $this->parse();
        $this->display();
    }

    /**
     * Load the item data
     */
    protected function loadData()
    {
        $this->id = $this->getParameter('product_id', 'int', null);

        if ($this->id != null) {
            $this->record = BackendCatalogModel::get($this->id);
        }

        // get categories
        $this->categories = BackendCatalogModel::getCategories(true);

        // Get all products grouped by categories
        $this->allProductsGroupedByCategories = BackendCatalogModel::getAllProductsGroupedByCategories();

        // get specifications
        $this->specifications = BackendCatalogModel::getSpecifications();

        // get brands
        $this->brands = BackendCatalogModel::getBrandsForDropdown();
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        $this->frm = new BackendForm('add');

        // product fields
        $this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
        $this->frm->addEditor('summary');
        $this->frm->addEditor('text');
        $this->frm->addText('price', null, null, 'inputText price', 'inputTextError price');
        $this->frm->addCheckbox('allow_comments', true);
        $this->frm->addText('tags', null, null, 'inputText tagBox', 'inputTextError tagBox');
        $this->frm->addDropdown('related_products', $this->allProductsGroupedByCategories, null, true);


        $this->frm->addDropdown('category_id', $this->categories, \SpoonFilter::getGetValue('category', null, null, 'int'));
        $this->frm->addDropdown('brand_id', $this->brands);
        $this->frm->getField('brand_id')->setDefaultElement('');

        $specificationsHTML = array();

        // specifications
        foreach ($this->specifications as $specification) {
            $specificationName = 'specification' . $specification['id'];

            // @todo check if type is text or textarea..
            $specificationText = $this->frm->addText($specificationName);
            $specificationHTML = $specificationText->parse();
            
            // parse specification into template
            $this->tpl->assign('id', $specification['id']);
            $this->tpl->assign('label', $specification['title']);
            $this->tpl->assign('field', $specificationHTML);
            $this->tpl->assign('spec', true);

            $specificationsHTML[]['specification'] = $this->tpl->getContent(BACKEND_MODULES_PATH . '/' . $this->getModule() . '/Layout/Templates/Specification.tpl');
        }
        
        $this->tpl->assign('specifications', $specificationsHTML);
                                                
        // meta
        $this->meta = new BackendMeta($this->frm, null, 'title', true);
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        parent::parse();
                
        // get url
        $url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
        $url404 = BackendModel::getURL(404);
        
        // parse additional variables
        if ($url404 != $url) {
            $this->tpl->assign('detailURL', SITE_URL . $url);
        }
    }

    /**
     * Validate the form
     */
    protected function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            $this->frm->cleanupFields();

            // validation
            $fields = $this->frm->getFields();
                        
            // required fields
            $fields['title']->isFilled(BL::err('FieldIsRequired'));
            $fields['summary']->isFilled(BL::err('FieldIsRequired'));
            $fields['category_id']->isFilled(BL::err('FieldIsRequired'));
            if ($fields['category_id']->getValue() == 'no_category') {
                $fields['category_id']->addError(BL::err('FieldIsRequired'));
            }
            
            // validate meta
            $this->meta->validate();

            if ($this->frm->isCorrect()) {
                // build the item
                $item['language'] = BL::getWorkingLanguage();
                $item['title'] = $fields['title']->getValue();
                $item['price'] = $fields['price']->getValue();
                $item['summary'] = $fields['summary']->getValue();
                $item['text'] = $fields['text']->getValue();
                $item['allow_comments'] = $fields['allow_comments']->getChecked() ? 'Y' : 'N';
                $item['num_comments'] = 0;
                $item['sequence'] = BackendCatalogModel::getMaximumSequence() + 1;
                $item['category_id'] = $fields['category_id']->getValue();
                $item['brand_id'] = $fields['brand_id']->getValue();
                $item['meta_id'] = $this->meta->save();

                // insert it
                $item['id'] = BackendCatalogModel::insert($item);
                
                $specificationArray = array();
                
                // loop trough specifications and insert values
                foreach ($this->specifications as $specification) {
                    // build the specification
                    $specificationArray['product_id'] = $item['id'];
                    $specificationArray['specification_id'] = $specification['id'];

                    $field = 'specification' . $specification['id'];
                    
                    // check if there is an value
                    if ($fields[$field]->getValue() != null) {
                        $specificationArray['value'] = $fields[$field]->getValue();
                        
                        // insert specification with product id and value
                        BackendCatalogModel::insertSpecificationValue($specificationArray);
                    }
                }
                
                // save the tags
                BackendTagsModel::saveTags($item['id'], $fields['tags']->getValue(), $this->URL->getModule());
                
                // save the related products
                BackendCatalogModel::saveRelatedProducts($item['id'], $this->frm->getField('related_products')->getValue());
                
                // add search index
                BackendSearchModel::saveIndex($this->getModule(), $item['id'], array('title' => $item['title'], 'summary' => $item['summary'], 'text' => $item['text']));

                // trigger event
                BackendModel::triggerEvent($this->getModule(), 'after_add', $item);
                
                // redirect page
                $this->redirect(
                    BackendModel::createURLForAction('index') . '&report=added&highlight=row-' . $item['id']
                );
            }
        }
    }
}
