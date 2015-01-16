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
use Frontend\Core\Engine\Form as FrontendForm;
use Frontend\Core\Engine\Language as FL;
use Frontend\Modules\Catalog\Engine\Model as FrontendCatalogModel;
use Frontend\Modules\Tags\Engine\Model as FrontendTagsModel;

/**
 * This is the detail-action, it will display a product
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Detail extends FrontendBaseBlock
{
    /**
     * The information about a product
     *
     * @var    array
     */
    private $record;

    /**
     * The specifications of a product
     *
     * @var    array
     */
    private $specifications;

    /**
     * The tags of a product
     *
     * @var    array
     */
    private $tags;

    /**
     * The comments of a product
     *
     * @var    array
     */
    private $comments;

    /**
     * Module settings
     *
     * @var    array
     */
    private $settings;

    /**
     * The related products
     *
     * @var    array
     */
    private $relatedProducts;

    /**
     * Videos from a product
     *
     * @var    array
     */
    private $videos;

    /**
     * Files from a product
     *
     * @var    array
     */
    private $files;

    /**
     * Images from a product
     *
     * @var    array
     */
    private $images;

    /**
     * Brand from a product
     *
     * @var    array
     */
    private $brand;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->loadTemplate();
        $this->getData();

        $this->loadForm();
        $this->validateForm();

        $this->parse();
    }

    /**
     * Get the data
     */
    private function getData()
    {
        // validate incoming parameters
        if ($this->URL->getParameter(1) === null) {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        // get information
        $this->record = FrontendCatalogModel::get($this->URL->getParameter(1));
        $this->comments = FrontendCatalogModel::getComments($this->record['id']);
        $this->specifications = FrontendCatalogModel::getProductSpecifications($this->record['id']);
        $this->tags = FrontendTagsModel::getForItem('Catalog', $this->record['id']);
        $this->settings = FrontendModel::getModuleSettings('Catalog');
        $this->files = FrontendCatalogModel::getFiles($this->record['id']);
        $this->videos = FrontendCatalogModel::getVideos($this->record['id']);
        $this->relatedProducts = FrontendCatalogModel::getRelatedProducts($this->record['id']);
        $this->brand = FrontendCatalogModel::getBrand($this->record['brand_id']);

        $this->record['allow_comments'] = ($this->record['allow_comments'] == 'Y');
        $this->record['brand'] = $this->brand;

        // reset allow comments
        if (!$this->settings['allow_comments']) {
            $this->record['allow_comments'] = false;
        }

        // check if record is not empty
        if (empty($this->record)) {
            $this->redirect(FrontendNavigation::getURL(404));
        }
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        // create form
        $this->frm = new FrontendForm('commentsForm');
        $this->frm->setAction($this->frm->getAction() . '#' . FL::act('Comment'));

        // init vars
        $author = (Cookie::exists('comment_author')) ? Cookie::get('comment_author') : null;
        $email = (Cookie::exists('comment_email') && \SpoonFilter::isEmail(Cookie::get('comment_email'))) ? Cookie::get('comment_email') : null;
        $website = (Cookie::exists('comment_website') && \SpoonFilter::isURL(Cookie::get('comment_website'))) ? Cookie::get('comment_website') : 'http://';

        // create elements
        $this->frm->addText('author', $author)->setAttributes(array('required' => null));
        $this->frm->addText('email', $email)->setAttributes(array('required' => null, 'type' => 'email'));
        $this->frm->addText('website', $website, null);
        $this->frm->addTextarea('message')->setAttributes(array('required' => null));
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        // add css
        $this->header->addCSS('/src/Frontend/Modules/' . $this->getModule() . '/Layout/Css/catalog.css');

        // add noty js
        $this->header->addJS('/src/Frontend/Modules/' . $this->getModule() . '/Js/noty/packaged/jquery.noty.packaged.min.js');

        // add into breadcrumb
        $this->breadcrumb->addElement($this->record['meta_title']);

        // hide action title
        $this->tpl->assign('hideContentTitle', true);

        // show title linked with the meta title
        $this->tpl->assign('title', $this->record['title']);

        // set meta
        $this->header->setPageTitle($this->record['meta_title'], ($this->record['meta_description_overwrite'] == 'Y'));
        $this->header->addMetaDescription($this->record['meta_description'], ($this->record['meta_description_overwrite'] == 'Y'));
        $this->header->addMetaKeywords($this->record['meta_keywords'], ($this->record['meta_keywords_overwrite'] == 'Y'));

        // advanced SEO-attributes
        if (isset($this->record['meta_data']['seo_index'])) {
            $this->header->addMetaData(array('name' => 'robots', 'content' => $this->record['meta_data']['seo_index']));
        }
        if (isset($this->record['meta_data']['seo_follow'])) {
            $this->header->addMetaData(array('name' => 'robots', 'content' => $this->record['meta_data']['seo_follow']));
        }

        // assign item information
        $this->tpl->assign('item', $this->record);

        if (!empty($this->relatedProducts)) {
            $this->tpl->assign('related', $this->relatedProducts);
        }

        $this->tpl->assign('images', $this->record['images']);
        if ($this->videos != null) {
            $this->tpl->assign('videos', $this->videos);
        }
        if ($this->files != null) {
            $this->tpl->assign('files', $this->files);
        }
        $this->tpl->assign('specifications', $this->specifications);
        $this->tpl->assign('tags', $this->tags);
        //$this->tpl->assign('related', $this->relatedProducts);

        // count comments
        $commentCount = count($this->comments);

        if ($commentCount > 1) {
            $this->tpl->assign('commentsMultiple', true);
        }

        // assign the comments
        $this->tpl->assign('commentsCount', $commentCount);
        $this->tpl->assign('comments', $this->comments);

        // parse the form
        $this->frm->parse($this->tpl);

        // some options
        if ($this->URL->getParameter('comment', 'string') == 'moderation') {
            $this->tpl->assign('commentIsInModeration', true);
        }
        if ($this->URL->getParameter('comment', 'string') == 'spam') {
            $this->tpl->assign('commentIsSpam', true);
        }
        if ($this->URL->getParameter('comment', 'string') == 'true') {
            $this->tpl->assign('commentIsAdded', true);
        }
    }

    /**
     * Validate the form
     */
    private function validateForm()
    {
        // get settings
        $commentsAllowed = (isset($this->settings['allow_comments']) && $this->settings['allow_comments']);

        // comments aren't allowed so we don't have to validate
        if (!$commentsAllowed) {
            return false;
        }

        // is the form submitted
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // does the key exists?
            if (\SpoonSession::exists('catalog_comment_' . $this->record['id'])) {
                // calculate difference
                $diff = time() - (int)\SpoonSession::get('catalog_comment_' . $this->record['id']);

                // calculate difference, it it isn't 10 seconds the we tell the user to slow down
                if ($diff < 10 && $diff != 0) {
                    $this->frm->getField('message')->addError(FL::err('CommentTimeout'));
                }
            }

            // validate required fields
            $this->frm->getField('author')->isFilled(FL::err('AuthorIsRequired'));
            $this->frm->getField('email')->isEmail(FL::err('EmailIsRequired'));
            $this->frm->getField('message')->isFilled(FL::err('MessageIsRequired'));

            // validate optional fields
            if ($this->frm->getField('website')->isFilled() && $this->frm->getField('website')->getValue() != 'http://') {
                $this->frm->getField('website')->isURL(FL::err('InvalidURL'));
            }

            // no errors?
            if ($this->frm->isCorrect()) {
                // get module setting
                $spamFilterEnabled = (isset($this->settings['spamfilter']) && $this->settings['spamfilter']);
                $moderationEnabled = (isset($this->settings['moderation']) && $this->settings['moderation']);

                // reformat data
                $author = $this->frm->getField('author')->getValue();
                $email = $this->frm->getField('email')->getValue();
                $website = $this->frm->getField('website')->getValue();
                if (trim($website) == '' || $website == 'http://') {
                    $website = null;
                }
                $text = $this->frm->getField('message')->getValue();

                // build array
                $comment['product_id'] = $this->record['id'];
                $comment['language'] = FRONTEND_LANGUAGE;
                $comment['created_on'] = FrontendModel::getUTCDate();
                $comment['author'] = $author;
                $comment['email'] = $email;
                $comment['website'] = $website;
                $comment['text'] = $text;
                $comment['status'] = 'published';
                $comment['data'] = serialize(array('server' => $_SERVER));

                // get URL for article
                $permaLink = $this->record['full_url'];
                $redirectLink = $permaLink;

                // is moderation enabled
                if ($moderationEnabled) {
                    // if the commenter isn't moderated before alter the comment status so it will appear in the moderation queue
                    if (!FrontendCatalogModel::isModerated($author, $email)) {
                        $comment['status'] = 'moderation';
                    }
                }

                // should we check if the item is spam
                if ($spamFilterEnabled) {
                    // check for spam
                    $result = FrontendModel::isSpam($text, SITE_URL . $permaLink, $author, $email, $website);

                    // if the comment is spam alter the comment status so it will appear in the spam queue
                    if ($result) {
                        $comment['status'] = 'spam';
                    }

                    // if the status is unknown then we should moderate it manually
                    elseif ($result == 'unknown') {
                        $comment['status'] = 'moderation';
                    }
                }

                // insert comment
                $comment['id'] = FrontendCatalogModel::insertComment($comment);

                // trigger event
                FrontendModel::triggerEvent('catalog', 'after_add_comment', array('comment' => $comment));

                // append a parameter to the URL so we can show moderation
                if (strpos($redirectLink, '?') === false) {
                    if ($comment['status'] == 'moderation') {
                        $redirectLink .= '?comment=moderation#' . FL::act('Comment');
                    }
                    if ($comment['status'] == 'spam') {
                        $redirectLink .= '?comment=spam#' . FL::act('Comment');
                    }
                    if ($comment['status'] == 'published') {
                        $redirectLink .= '?comment=true#comment-' . $comment['id'];
                    }
                } else {
                    if ($comment['status'] == 'moderation') {
                        $redirectLink .= '&comment=moderation#' . FL::act('Comment');
                    }
                    if ($comment['status'] == 'spam') {
                        $redirectLink .= '&comment=spam#' . FL::act('Comment');
                    }
                    if ($comment['status'] == 'published') {
                        $redirectLink .= '&comment=true#comment-' . $comment['id'];
                    }
                }

                // set title
                $comment['product_title'] = $this->record['title'];
                $comment['product_url'] = $this->record['url'];

                // notify the admin
                FrontendCatalogModel::notifyAdmin($comment);

                // store timestamp in session so we can block excessive usage
                \SpoonSession::set('catalog_comment_' . $this->record['id'], time());

                // store author-data in cookies
                try {
                    Cookie::set('comment_author', $author);
                    Cookie::set('comment_email', $email);
                    Cookie::set('comment_website', $website);
                } catch (Exception $e) {
                    // settings cookies isn't allowed, but because this isn't a real problem we ignore the exception
                }

                // redirect
                $this->redirect($redirectLink);
            }
        }
    }
}
