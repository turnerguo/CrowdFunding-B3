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

class CrowdFundingViewProject extends JViewLegacy
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

    protected $form;
    protected $item;
    protected $items;

    /**
     * @var CrowdFundingCurrency
     */
    protected $currency;

    protected $userId;
    protected $disabledButton;
    protected $layout;
    protected $debugMode;
    protected $rewardsEnabled;
    protected $article;
    protected $pathwayName;
    protected $numberOfTypes;
    protected $image;
    protected $isNew;
    protected $imageFolder;
    protected $minAmount;
    protected $maxAmount;
    protected $minDays;
    protected $maxDays;
    protected $checkedDate;
    protected $checkedDays;
    protected $pitchImage;
    protected $pWidth;
    protected $pHeight;
    protected $imageSmall;
    protected $fundingDuration;
    protected $dateFormat;
    protected $dateFormatCalendar;
    protected $rewardsImagesEnabled;
    protected $rewardsImagesUri;
    protected $projectId;
    protected $images;
    protected $extraImagesUri;
    protected $rewards;

    protected $imageWidth;
    protected $imageHeight;
    protected $titleLength;
    protected $descriptionLength;
    protected $returnUrl;

    protected $wizardType;
    protected $layoutsBasePath;
    protected $layoutData = array();
    protected $sixSteps = false;
    protected $statistics = array();

    protected $option;

    protected $pageclass_sfx;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->getCmd("option");

        $this->layoutsBasePath = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . "/layouts");
    }

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->userId = JFactory::getUser()->get("id");
        if (!$this->userId) {
            $this->setLayout("intro");
        }

        $this->disabledButton = "";

        $this->layout = $this->getLayout();

        switch ($this->layout) {

            case "funding":
                $this->prepareFunding();
                break;

            case "story":
                $this->prepareStory();
                break;

            case "rewards":
                $this->prepareRewards();
                break;

            case "extras":
                $this->prepareExtras();
                break;

            case "manager":
                $this->prepareManager();
                break;

            case "intro":
                $this->prepareIntro();
                break;

            default: // Basic data for project
                $this->prepareBasic();
                break;
        }

        // Get wizard type
        $this->wizardType = $this->params->get("project_wizard_type", "five_steps");
        $this->sixSteps   = (strcmp("six_steps", $this->wizardType) != 0) ? false : true;

        $this->layoutData = array(
            "layout"  => $this->layout,
            "item_id" => (!empty($this->item->id)) ? $this->item->id : 0
        );

        $this->prepareDebugMode();
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Check the system for debug mode
     */
    protected function prepareDebugMode()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Check for maintenance (debug) state
        $params          = $this->state->get("params");
        $this->debugMode = $params->get("debug_project_adding_disabled", 0);
        if ($this->debugMode) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if (!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $app->enqueueMessage($msg, "notice");

            $this->disabledButton = 'disabled="disabled"';
        }
    }

    /**
     * Check the system for debug mode
     */
    protected function prepareProjectType()
    {
        // Get project type and check for enabled rewards.
        $this->rewardsEnabled = true;

        if (!empty($this->item->type_id)) {
            jimport("crowdfunding.type");

            $type = new CrowdFundingType(JFactory::getDbo());
            $type->load($this->item->type_id);

            if ($type->getId() and !$type->isRewardsEnabled()) {
                $this->rewardsEnabled = false;
                $this->disabledButton = 'disabled="disabled"';
            }
        }
    }

    /**
     * Display default page
     */
    protected function prepareIntro()
    {
        $model        = JModelLegacy::getInstance("Intro", "CrowdFundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdFundingModelIntro */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $state = $model->getState();
        $this->state = $state;

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        $articleId     = $this->params->get("project_intro_article", 0);
        $this->article = $model->getItem($articleId);

        $this->pathwayName = JText::_("COM_CROWDFUNDING_START_PROJECT_BREADCRUMB");
    }

    protected function prepareBasic()
    {
        $model = JModelLegacy::getInstance("Project", "CrowdFundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdFundingModelProject */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $state = $model->getState();
        $this->state = $state;

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        // Get item
        $itemId     = $this->state->get('project.id');
        $this->item = $model->getItem($itemId, $this->userId);

        // Set a flag that describes the item as new.
        $this->isNew = false;
        if (!$this->item->id) {
            $this->isNew = true;
        }

        $this->form = $model->getForm();

        // Get types
        jimport("crowdfunding.types");
        $types               = CrowdFundingTypes::getInstance(JFactory::getDbo());
        $this->numberOfTypes = count($types);

        // Prepare images
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");
        $this->imageSmall  = $this->item->get("image_small");

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_BASIC");

    }

    protected function prepareFunding()
    {
        $model = JModelLegacy::getInstance("Funding", "CrowdFundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdFundingModelFunding */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $state = $model->getState();
        $this->state = $state;

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        // Get item
        $itemId     = $this->state->get('funding.id');
        $this->item = $model->getItem($itemId, $this->userId);

        $this->form = $model->getForm();

        // Get currency
        jimport("crowdfunding.currency");
        $currencyId     = $this->params->get("project_currency");
        $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId, $this->params);

        // Set minimum values - days, amount,...
        $this->minAmount = $this->params->get("project_amount_minimum", 100);
        $this->maxAmount = $this->params->get("project_amount_maximum");

        $this->minDays = $this->params->get("project_days_minimum", 30);
        $this->maxDays = $this->params->get("project_days_maximum");

        // Prepare funding duration type
        $this->prepareFundingDurationType();

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_FUNDING");
    }

    protected function prepareFundingDurationType()
    {
        $this->fundingDuration = $this->params->get("project_funding_duration");

        switch ($this->fundingDuration) {

            case "days": // Only days type is enabled
                $this->checkedDays = 'checked="checked"';
                break;

            case "date": // Only date type is enabled
                $this->checkedDate = 'checked="checked"';
                break;

            default: // Both ( days and date ) types are enabled

                $this->checkedDays = 0;
                $this->checkedDate = "";

                jimport("itprism.validator.date");
                $dateValidator = new ITPrismValidatorDate($this->item->funding_end);

                if (!empty($this->item->funding_days)) {
                    $this->checkedDays = 'checked="checked"';
                    $this->checkedDate = '';
                } elseif ($dateValidator->isValid($this->item->funding_end)) {
                    $this->checkedDays = '';
                    $this->checkedDate = 'checked="checked"';
                }

                // If missing both, select days.
                if (!$this->checkedDays and !$this->checkedDate) {
                    $this->checkedDays = 'checked="checked"';
                }
                break;

        }
    }

    protected function prepareStory()
    {
        $model = JModelLegacy::getInstance("Story", "CrowdFundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdFundingModelStory */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $state = $model->getState();
        $this->state = $state;

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        // Get item
        $itemId     = $this->state->get('story.id');
        $this->item = $model->getItem($itemId, $this->userId);

        $this->form   = $model->getForm();

        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");
        $this->pitchImage  = $this->item->get("pitch_image");

        $this->pWidth  = $this->params->get("pitch_image_width", 600);
        $this->pHeight = $this->params->get("pitch_image_height", 400);

        // Prepare extra images folder
        if ($this->params->get("extra_images", 0) and !empty($this->userId)) {

            jimport('joomla.filesystem.folder');

            $userDestinationFolder = CrowdFundingHelper::getImagesFolder($this->userId);
            if (!JFolder::exists($userDestinationFolder)) {
                CrowdFundingHelper::createFolder($userDestinationFolder);
            }

            jimport("crowdfunding.images");
            $this->images = new CrowdFundingImages(JFactory::getDbo());
            $this->images->load($itemId);

            $this->extraImagesUri = CrowdFundingHelper::getImagesFolderUri($this->userId);
        }

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_STORY");
    }

    protected function prepareRewards()
    {
        $model = JModelLegacy::getInstance("Rewards", "CrowdFundingModel", $config = array('ignore_request' => false));

        // Initialise variables
        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $state = $model->getState();
        $this->state = $state;

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        $this->projectId = $this->state->get("rewards.id");

        $this->items = $model->getItems($this->projectId);

        // Get project and validate it
        jimport("crowdfunding.project");
        $project = CrowdFundingProject::getInstance(JFactory::getDbo(), $this->projectId);
        $project = $project->getProperties();

        $this->item = JArrayHelper::toObject($project);
        if (!$this->item->id or ($this->item->user_id != $this->userId)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"));
        }

        // Create a currency object.
        jimport("crowdfunding.currency");
        $currencyId     = $this->params->get("project_currency");
        $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId, $this->params);

        // Get date format
        $this->dateFormat         = CrowdFundingHelper::getDateFormat();
        $this->dateFormatCalendar = CrowdFundingHelper::getDateFormat(true);
        $js                       = '
	        // Rewards calendar date format.
            var projectWizard = {
                dateFormat: "' . $this->dateFormatCalendar . '"
            };
        ';
        $this->document->addScriptDeclaration($js);

        // Prepare rewards images.
        $this->rewardsImagesEnabled = $this->params->get("rewards_images", 0);
        $this->rewardsImagesUri     = CrowdFundingHelper::getImagesFolderUri($this->userId);

        $this->prepareProjectType();

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_REWARDS");
    }

    protected function prepareManager()
    {
        $model = JModelLegacy::getInstance("Manager", "CrowdFundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdFundingModelManager */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $this->state = $model->getState();

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        $this->imageWidth  = $this->params->get("image_width", 200);
        $this->imageHeight = $this->params->get("image_height", 200);
        $this->titleLength       = $this->params->get("discover_title_length", 0);
        $this->descriptionLength = $this->params->get("discover_description_length", 0);

        // Get the folder with images
        $this->imageFolder = $params->get("images_directory", "images/crowdfunding");

        // Filter the URL.
        $uri = JUri::getInstance();

        $filter    = JFilterInput::getInstance();
        $this->returnUrl = $filter->clean($uri->toString());

        // Get item
        $itemId     = $this->state->get('manager.id');

        // Create a currency object.
        jimport("crowdfunding.currency");
        $currencyId     = $this->params->get("project_currency");
        $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId, $this->params);

        $this->item = $model->getItem($itemId, $this->userId);

        if (empty($this->item->id)) {

            $app = JFactory::getApplication();
            /** $app JApplicationSite */

            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_SOMETHING_WRONG"), "notice");
            $app->redirect(JRoute::_(CrowdFundingHelperRoute::getDiscoverRoute()));
            return;
        }

        jimport("crowdfunding.statistics.project");
        $statistics = new CrowdFundingStatisticsProject(JFactory::getDbo(), $this->item->id);
        $this->statistics = array(
            "updates"  => $statistics->getUpdatesNumber(),
            "comments" => $statistics->getCommentsNumber(),
            "funders"  => $statistics->getTransactionsNumber(),
        );

        // Get rewards
        jimport("crowdfunding.rewards");
        $this->rewards = new CrowdFundingRewards(JFactory::getDbo());
        $this->rewards->load($this->item->id);

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_MANAGER");
    }

    protected function prepareExtras()
    {
        $model = JModelLegacy::getInstance("Extras", "CrowdFundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdFundingModelManager */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $this->state = $model->getState();

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        // Get item
        $itemId     = $this->state->get('extras.id');
        $this->item = $model->getItem($itemId, $this->userId);
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_EXTRAS");

        // Events
        JPluginHelper::importPlugin('crowdfunding');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onExtrasDisplay', array('com_crowdfunding.project.extras', &$this->item, &$this->params));

        $this->item->event                   = new stdClass();
        $this->item->event->onExtrasDisplay = trim(implode("\n", $results));
    }

    /**
     * Prepares the document
     */
    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page suffix
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        $menus = $app->getMenu();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $menu->title);
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_RAISE_DEFAULT_PAGE_TITLE'));
        }

        // Prepare page title
        $title = $menu->title;
        if (!$title) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0)) { // Set site name if it is necessary ( the option 'sitename' = 1 )
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } else { // Item title to the browser title.
            if (!empty($this->item)) {
                $title .= " | " . $this->escape($this->item->title);
            }
        }
        $this->document->setTitle($title);

        // Meta Description
        $this->document->setDescription($this->params->get('menu-meta_description'));

        // Meta keywords
        $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));

        // Add current layout into breadcrumbs.
        $pathway = $app->getPathway();
        $pathway->addItem($this->pathwayName);

        // Styles

        // Load bootstrap navbar styles
        if ($this->params->get("bootstrap_navbar", false)) {
            JHtml::_("itprism.ui.bootstrap_navbar");
        }

        // Scripts
        JHtml::_('bootstrap.framework');
//        JHtml::_('bootstrap.tooltip');

        if ($this->params->get("enable_chosen", 1)) {
            JHtml::_('formbehavior.chosen', '.cf-advanced-select');
        }

        JHtml::_('behavior.keepalive');

        switch ($this->layout) {

            case "rewards":

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_REWARD');
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE');
                JText::script('COM_CROWDFUNDING_SELECT_IMAGE');

                // Scripts

                if ($this->params->get("rewards_images", 0)) {
                    JHtml::_('itprism.ui.bootstrap_filestyle');
                }

                JHtml::_('itprism.ui.pnotify');
                JHtml::_("itprism.ui.joomla_helper");
                $this->document->addScript('media/' . $this->option . '/js/site/project_rewards.js');

                break;

            case "funding":
                JHtml::_('itprism.ui.parsley');
                $this->document->addScript('media/' . $this->option . '/js/site/project_funding.js');

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED');

                break;

            case "story":

                // Scripts
                JHtml::_('itprism.ui.bootstrap_fileuploadstyle');

                if ($this->params->get("extra_images", 0)) {
                    JHtml::_('itprism.ui.fileupload');
                    JHtml::_('itprism.ui.pnotify');
                    JHtml::_("itprism.ui.joomla_helper");
                }

                $this->document->addScript('media/' . $this->option . '/js/site/project_story.js');

                break;

            case "manager":

                $this->document->addScript('media/' . $this->option . '/js/site/project_manager.js');

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_QUESTION_LAUNCH_PROJECT');
                JText::script('COM_CROWDFUNDING_QUESTION_STOP_PROJECT');

                break;

            case "extras":

                break;

            default: // Basic

                // Scripts
                JHtml::_('itprism.ui.bootstrap_fileuploadstyle');
                JHtml::_('itprism.ui.bootstrap_maxlength');
                JHtml::_('itprism.ui.bootstrap_typeahead');
                JHtml::_('itprism.ui.parsley');
                $this->document->addScript('media/' . $this->option . '/js/site/project_basic.js');

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED');
                break;
        }
    }
}
