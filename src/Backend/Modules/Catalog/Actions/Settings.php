<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;

/**
 * This is the settings action, it will display a form to set general catalog settings.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Settings extends BackendBaseActionEdit
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
     * Loads the settings form
     */
    private function loadForm()
    {
        // init settings form
        $this->frm = new BackendForm('settings');
        
        // add fields for pagination
        $this->frm->addDropdown('overview_number_of_items', array_combine(range(1, 30), range(1, 30)), BackendModel::getModuleSetting($this->URL->getModule(), 'overview_num_items', 10));
        $this->frm->addDropdown('recent_products_full_number_of_items', array_combine(range(1, 10), range(1, 10)), BackendModel::getModuleSetting($this->URL->getModule(), 'recent_products_full_num_items', 5));
        //$this->frm->addDropdown('recent_products_list_number_of_items', array_combine(range(1, 10), range(1, 10)), BackendModel::getModuleSetting($this->URL->getModule(), 'recent_articles_list_num_items', 5));
        
        // add fields for spam
        $this->frm->addCheckbox('spamfilter', BackendModel::getModuleSetting($this->URL->getModule(), 'spamfilter', false));
        
        // no Akismet-key, so we can't enable spam-filter
        if (BackendModel::getModuleSetting('core', 'akismet_key') == '') {
            $this->frm->getField('spamfilter')->setAttribute('disabled', 'disabled');
            $this->tpl->assign('noAkismetKey', true);
        }
        
        // add fields for comments
        $this->frm->addCheckbox('allow_comments', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_comments', false));
        $this->frm->addCheckbox('moderation', BackendModel::getModuleSetting($this->URL->getModule(), 'moderation', false));

        // add fields for notifications
        $this->frm->addCheckbox('notify_by_email_on_new_comment_to_moderate', BackendModel::getModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_comment_to_moderate', false));
        $this->frm->addCheckbox('notify_by_email_on_new_comment', BackendModel::getModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_comment', false));

        // add fields for images
        $this->frm->addText('width1', BackendModel::getModuleSetting($this->URL->getModule(), 'width1', false));
        $this->frm->addText('height1', BackendModel::getModuleSetting($this->URL->getModule(), 'height1', false));
        $this->frm->addCheckbox('allow_enlargment1', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment1', false));
        $this->frm->addCheckbox('force_aspect_ratio1', BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio1', false));
        
        $this->frm->addText('width2', BackendModel::getModuleSetting($this->URL->getModule(), 'width2', false));
        $this->frm->addText('height2', BackendModel::getModuleSetting($this->URL->getModule(), 'height2', false));
        $this->frm->addCheckbox('allow_enlargment2', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment2', false));
        $this->frm->addCheckbox('force_aspect_ratio2', BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio2', false));
        
        $this->frm->addText('width3', BackendModel::getModuleSetting($this->URL->getModule(), 'width3', false));
        $this->frm->addText('height3', BackendModel::getModuleSetting($this->URL->getModule(), 'height3', false));
        $this->frm->addCheckbox('allow_enlargment3', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment3', false));
        $this->frm->addCheckbox('force_aspect_ratio3', BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio3', false));
                
        $this->frm->addCheckbox('allow_multiple_categories', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_multiple_categories', false));
    }

    /**
     * Validates the settings form
     */
    private function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            if ($this->frm->isCorrect()) {
                // set our settings
                BackendModel::setModuleSetting($this->URL->getModule(), 'overview_num_items', (int) $this->frm->getField('overview_number_of_items')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'recent_products_full_num_items', (int) $this->frm->getField('recent_products_full_number_of_items')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'spamfilter', (bool) $this->frm->getField('spamfilter')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'allow_comments', (bool) $this->frm->getField('allow_comments')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'moderation', (bool) $this->frm->getField('moderation')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_comment_to_moderate', (bool) $this->frm->getField('notify_by_email_on_new_comment_to_moderate')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_comment', (bool) $this->frm->getField('notify_by_email_on_new_comment')->getValue());
                
                BackendModel::setModuleSetting($this->URL->getModule(), 'width1', (int) $this->frm->getField('width1')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'height1', (int) $this->frm->getField('height1')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'allow_enlargment1', (bool) $this->frm->getField('allow_enlargment1')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'force_aspect_ratio1', (bool) $this->frm->getField('force_aspect_ratio1')->getValue());
                
                BackendModel::setModuleSetting($this->URL->getModule(), 'width2', (int) $this->frm->getField('width2')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'height2', (int) $this->frm->getField('height2')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'allow_enlargment2', (bool) $this->frm->getField('allow_enlargment2')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'force_aspect_ratio2', (bool) $this->frm->getField('force_aspect_ratio2')->getValue());
                
                BackendModel::setModuleSetting($this->URL->getModule(), 'width3', (int) $this->frm->getField('width3')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'height3', (int) $this->frm->getField('height3')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'allow_enlargment3', (bool) $this->frm->getField('allow_enlargment3')->getValue());
                BackendModel::setModuleSetting($this->URL->getModule(), 'force_aspect_ratio3', (bool) $this->frm->getField('force_aspect_ratio3')->getValue());
                                
                BackendModel::setModuleSetting($this->URL->getModule(), 'allow_multiple_categories', (bool) $this->frm->getField('allow_multiple_categories')->getValue());

                // redirect to the settings page
                $this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
            }
        }
    }
}
