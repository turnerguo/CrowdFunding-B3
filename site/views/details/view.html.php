<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class CrowdFundingViewDetails extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $item;

    protected $imageFolder;
    protected $screen;
    protected $items;
    protected $form;
    protected $userId;
    protected $isOwner;
    protected $avatarsSize;
    protected $socialProfiles;
    protected $defaultAvatar;
    protected $onCommentAfterDisplay;
    protected $commentsEnabled;
    protected $currency;
    protected $displayAmounts;

    protected $option;

    protected $pageclass_sfx;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get model state.
        $this->state  = $this->get('State');
        $this->item   = $this->get("Item");

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        $model  = $this->getModel();
        $userId = JFactory::getUser()->get("id");

        if (!$this->item or $model->isRestricted($this->item, $userId)) {

            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));

            return;
        }

        // Get rewards of the project
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        // Prepare the link that points to project page
        $host             = JUri::getInstance()->toString(array("scheme", "host"));
        $this->item->link = $host . JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug));

        // Prepare the link that points to project image
        $this->item->link_image = $host . "/" . $this->imageFolder . "/" . $this->item->image;

        // Get the current screen
        $this->screen = $app->input->getCmd("screen", "home");

        $this->prepareDocument();

        // Import content plugins
        JPluginHelper::importPlugin('content');

        switch ($this->screen) {

            case "updates":
                $this->prepareUpdatesScreen();
                break;

            case "comments":
                $this->prepareCommentsScreen();
                break;

            case "funders":
                $this->prepareFundersScreen();
                break;

            default: // Home
                break;
        }

        // Events
        $dispatcher        = JEventDispatcher::getInstance();
        $this->item->event = new stdClass();
        $offset            = 0;

        $results                                 = $dispatcher->trigger('onContentBeforeDisplay', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
        $this->item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results                                 = $dispatcher->trigger('onContentAfterDisplayMedia', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
        $this->item->event->onContentAfterDisplayMedia = trim(implode("\n", $results));

        $results                                  = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
        $this->item->event->onContentAfterDisplay = trim(implode("\n", $results));

        // Count hits
        $model->hit($this->item->id);

        $this->version    = new CrowdFundingVersion();

        parent::display($tpl);
    }

    protected function prepareUpdatesScreen()
    {
        $model       = JModelLegacy::getInstance("Updates", "CrowdFundingModel", $config = array('ignore_request' => false));
        $this->items = $model->getItems();
        $this->form  = $model->getForm();

        $this->userId  = JFactory::getUser()->id;
        $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;

        // Get users IDs
        $usersIds = array();
        foreach ($this->items as $item) {
            $usersIds[] = $item->user_id;
        }

        // Prepare integration. Load avatars and profiles.
        $this->prepareIntegration($usersIds, $this->params);

        // Scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('itprism.ui.pnotify');

        JHtml::_("itprism.ui.joomla_helper");

        $this->document->addScript('media/' . $this->option . '/js/site/updates.js');
    }

    protected function prepareCommentsScreen()
    {
        $this->commentsEnabled = $this->params->get("comments_enabled", 1);

        // Initialize default comments functionality.
        if ($this->commentsEnabled) {
            $model       = JModelLegacy::getInstance("Comments", "CrowdFundingModel", $config = array('ignore_request' => false));
            $this->items = $model->getItems();
            $this->form  = $model->getForm();

            $this->userId  = JFactory::getUser()->get("id");
            $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;

            // Get users IDs
            $usersIds = array();
            foreach ($this->items as $item) {
                $usersIds[] = $item->user_id;
            }

            // Prepare integration. Load avatars and profiles.
            $this->prepareIntegration($usersIds, $this->params);

            // Scripts
            JHtml::_('behavior.keepalive');
            JHtml::_('behavior.formvalidation');
            JHtml::_('itprism.ui.pnotify');

            JHtml::_("itprism.ui.joomla_helper");

            $this->document->addScript('media/' . $this->option . '/js/site/comments.js');
        }

        // Trigger comments plugins.
        $dispatcher        = JEventDispatcher::getInstance();

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.comments', &$this->item, &$this->params));
        $this->onCommentAfterDisplay = trim(implode("\n", $results));
    }

    protected function prepareFundersScreen()
    {
        $model       = JModelLegacy::getInstance("Funders", "CrowdFundingModel", $config = array('ignore_request' => false));
        $this->items = $model->getItems();

        // Get users IDs
        $usersIds = array();
        foreach ($this->items as $item) {
            $usersIds[] = $item->id;
        }

        // Create a currency object if I have to display funders amounts.
        $this->displayAmounts = $this->params->get("funders_display_amounts", 0);
        if ($this->displayAmounts) {
            jimport("crowdfunding.currency");
            $currencyId = $this->params->get("project_currency");
            $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId);
        }

        // Prepare integration. Load avatars and profiles.
        $this->prepareIntegration($usersIds, $this->params);
    }

    /**
     * Prepare the document
     */
    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite * */

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta description
        $this->document->setDescription($this->item->short_desc);

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $app->getPathWay();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 32);
        $pathway->addItem($currentBreadcrumb, '');

        // Add styles

        // Load bootstrap media styles
        if ($this->params->get("bootstrap_mediacomponent", false)) {
            JHtml::_("itprism.ui.bootstrap_mediacomponent");
        }

        // Add scripts
        JHtml::_('jquery.framework');
    }

    private function preparePageHeading()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_DETAILS_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
//        $title = $this->params->get('page_title', $this->item->title);
        $title = $this->item->title;

        switch ($this->screen) {

            case "updates":
                $title .= " | " . JText::_("COM_CROWDFUNDING_UPDATES");
                break;

            case "comments":
                $title .= " | " . JText::_("COM_CROWDFUNDING_COMMENTS");
                break;

            case "funders":
                $title .= " | " . JText::_("COM_CROWDFUNDING_FUNDERS");
                break;

        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

    }

    /**
     * Prepare social profiles
     *
     * @param array     $usersIds
     * @param Joomla\Registry\Registry $params
     *
     * @todo Move it to a trait when traits become mass.
     */
    protected function prepareIntegration($usersIds, $params)
    {
        $usersIds = array_filter($usersIds);

        // Get a social platform for integration
        $socialPlatform = $params->get("integration_social_platform");

        $this->avatarsSize = $params->get("integration_avatars_size", 50);

        $this->socialProfiles        = null;
        $this->defaultAvatar         = $params->get("integration_avatars_default", "/media/com_crowdfunding/images/no-profile.png");

        // If there is now users, do not continue.
        if (!$usersIds) {
            return;
        }

        // Load the class
        if (!empty($socialPlatform) or !empty($avatarsService)) {
            jimport("itprism.integrate.profiles");
        }

        // Load the social profiles
        if (!empty($socialPlatform)) {
            $this->socialProfiles = ITPrismIntegrateProfiles::factory($socialPlatform, $usersIds);
        }
    }
}
