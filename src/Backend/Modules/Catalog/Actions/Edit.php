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
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Backend\Modules\Users\Engine\Model as BackendUsersModel;

/**
 * This is the edit-action, it will display a form with the product data to edit
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Edit extends BackendBaseActionEdit
{
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
     * All products
     *
     * @var	array
     */
    private $products;

    /**
     * All related products
     *
     * @var	array
     */
    private $relatedProducts;

    /**
     * All brands
     *
     * @var	array
     */
    private $brands;

    /**
     * Execute the action
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
        $this->id = $this->getParameter('id', 'int', null);
        
        if ($this->id == null || !BackendCatalogModel::exists($this->id)) {
            $this->redirect(
                BackendModel::createURLForAction('index') . '&error=non-existing'
            );
        }

        $this->record = BackendCatalogModel::get($this->id);
        
        $this->categories = (array) BackendCatalogModel::getCategories(true);
        $this->products = (array) BackendCatalogModel::getAll();
        $this->allProductsGroupedByCategories = (array) BackendCatalogModel::getAllProductsGroupedByCategories();
        $this->relatedProducts = (array) BackendCatalogModel::getRelatedProducts($this->id);
        $this->specifications = (array) BackendCatalogModel::getSpecifications();

        // get brands
        $this->brands = BackendCatalogModel::getBrandsForDropdown();
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        // create form
        $this->frm = new BackendForm('edit');

        $this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
        $this->frm->addEditor('summary', $this->record['summary']);
        $this->frm->addEditor('text', $this->record['text']);
        $this->frm->addText('price', $this->record['price'], null, 'inputText price', 'inputTextError price');
        $this->frm->addText('tags', BackendTagsModel::getTags($this->URL->getModule(), $this->record['id']), null, 'inputText tagBox', 'inputTextError tagBox');
        $this->frm->addDropdown('related_products', $this->allProductsGroupedByCategories, $this->relatedProducts, true);
        $this->frm->addCheckbox('allow_comments', ($this->record['allow_comments'] === 'Y' ? true : false));

        // categories
        $this->frm->addDropdown('category_id', $this->categories, $this->record['category_id']);

        $this->frm->addDropdown('brand_id', $this->brands, $this->record['brand_id']);

        $specificationsHTML = array();
        
        // specifications
        foreach ($this->specifications as $specification) {
            $specificationName = 'specification' . $specification['id'];
            
            $value = BackendCatalogModel::getSpecificationValue($specification['id'], $this->record['id']);
            
            // check if value is set
            $value = (isset($value['value']) ? $value['value'] : null);
                                    
            // @todo check if type is text or textarea..
            $specificationText = $this->frm->addText($specificationName, $value);
            $specificationHTML = $specificationText->parse();
            
            // parse specification into template
            $this->tpl->assign('id', $specification['id']);
            $this->tpl->assign('label', $specification['title']);
            $this->tpl->assign('field', $specificationHTML);
            $this->tpl->assign('spec', true);

            $specificationsHTML[]['specification'] = $this->tpl->getContent(BACKEND_MODULES_PATH . '/' . $this->getModule() . '/Layout/Templates/Specification.tpl');
        }
        
        $this->tpl->assign('specifications', $specificationsHTML);
        
        // meta object
        $this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
        
        // set callback for generating a unique URL
        $this->meta->setUrlCallback('Backend\Modules\Catalog\Engine\Model', 'getURL', array($this->record['id']));
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

        $this->tpl->assign('product', $this->record);
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

            $fields['title']->isFilled(BL::err('FieldIsRequired'));
            $fields['summary']->isFilled(BL::err('FieldIsRequired'));
            $fields['category_id']->isFilled(BL::err('FieldIsRequired'));
            if ($fields['category_id']->getValue() == 'no_category') {
                $fields['category_id']->addError(BL::err('FieldIsRequired'));
            }

            // validate meta
            $this->meta->validate();

            if ($this->frm->isCorrect()) {
                $item['id'] = $this->id;
                $item['language'] = BL::getWorkingLanguage();
                $item['price'] = $fields['price']->getValue();
                $item['title'] = $fields['title']->getValue();
                $item['summary'] = $fields['summary']->getValue();
                $item['text'] = $fields['text']->getValue();
                $item['category_id'] = $this->frm->getField('category_id')->getValue();
                $item['brand_id'] = $fields['brand_id']->getValue();
                $item['allow_comments'] = $this->frm->getField('allow_comments')->getChecked() ? 'Y' : 'N';

                $item['meta_id'] = $this->meta->save();

                BackendCatalogModel::update($item);
                $item['id'] = $this->id;
                $specificationArray = array();

                // loop trough specifications and insert values
                foreach ($this->specifications as $specification) {
                    $field = 'specification' . $specification['id'];

                    $specificationArray['value'] = $fields[$field]->getValue();
                    $specificationArray['product_id'] = $item['id'];
                    $specificationArray['specification_id'] = $specification['id'];

                    // when specification value already exists. update value
                    if (BackendCatalogModel::existsSpecificationValue($item['id'], $specification['id']) != false) {
                        // update specification with product id and value
                        BackendCatalogModel::updateSpecificationValue($specification['id'], $item['id'], $specificationArray);
                    } else {
                        // when specification value doesnt exists, insert new value
                        BackendCatalogModel::insertSpecificationValue($specificationArray);
                    }
                }
                
                // save the tags
                BackendTagsModel::saveTags($item['id'], $fields['tags']->getValue(), $this->URL->getModule());

                // add search index
                BackendSearchModel::saveIndex($this->getModule(), $item['id'], array('title' => $item['title'], 'summary' => $item['summary'], 'text' => $item['text']));

                // save related projects
                BackendCatalogModel::saveRelatedProducts($item['id'], $this->frm->getField('related_products')->getValue(), $this->relatedProducts);
                
                // trigger event
                BackendModel::triggerEvent(
                    $this->getModule(), 'after_edit', $item
                );
                
                $this->redirect(
                    BackendModel::createURLForAction('index') . '&report=edited&highlight=row-' . $item['id']
                );
            }
        }
    }
}
