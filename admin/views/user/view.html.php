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

class CrowdFundingViewUser extends JViewLegacy
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

    protected $currency;
    protected $projects;
    protected $investedAmount;
    protected $investedTransactions;
    protected $receivedAmount;
    protected $receivedTransactions;
    protected $socialProfile;
    protected $profileLink;
    protected $rewards;
    protected $returnUrl;

    protected $documentTitle;
    protected $option;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $app    = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        // Get user ID
        $userId = $app->input->getInt("id");

        $model = $this->getModel();

        $this->state = $model->getState();
        $this->item  = $model->getItem($userId);

        $this->params = JComponentHelper::getParams($this->option);

        // Get currency
        jimport("crowdfunding.currency");
        $currencyId     = $this->params->get("project_currency");
        $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId, $this->params);


        // Get number of rewards.
        jimport("crowdfunding.statistics.user");
        $statistics = new CrowdFundingStatisticsUser(JFactory::getDbo(), $this->item->id);
        $this->projects  = $statistics->getProjectsNumber();

        $amounts   = $statistics->getAmounts();

        if (!empty($amounts["invested"])) {
            $this->investedAmount = (float)$amounts["invested"]->amount;
            $this->investedTransactions = (int)$amounts["invested"]->number;
        }

        if (!empty($amounts["received"])) {
            $this->receivedAmount = (float)$amounts["received"]->amount;
            $this->receivedTransactions = (int)$amounts["received"]->number;
        }

        // Get social profile
        $socialPlatform = $this->params->get("integration_social_platform");

        if (!empty($socialPlatform)) {
            $options = array(
                "social_platform" => $socialPlatform,
                "user_id" => $this->item->id
            );

            jimport("itprism.integrate.profile.builder");
            $profileBuilder = new ITPrismIntegrateProfileBuilder($options);
            $profileBuilder->build();

            $this->socialProfile = $profileBuilder->getProfile();
            $this->profileLink   = $this->socialProfile->getLink();
        }

        jimport("crowdfunding.user.rewards");
        $this->rewards = new CrowdFundingUserRewards(JFactory::getDbo());
        $this->rewards->load($this->item->id);

        $this->returnUrl = base64_encode("index.php?option=com_crowdfunding&view=user&id=".$this->item->id);

        // Prepare actions, behaviors, scripts and document
        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_USER');

        JToolbarHelper::title($this->documentTitle);

        // Refresh page.
        $bar = JToolbar::getInstance('toolbar');
        $bar->appendButton('Link', 'refresh', JText::_("COM_CROWDFUNDING_REFRESH"), JRoute::_("index.php?option=com_crowdfunding&view=user&id=".$this->item->id));

        JToolbarHelper::cancel('user.cancel', 'JTOOLBAR_CLOSE');
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        // Scripts
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        JHtml::_('formbehavior.chosen', 'select');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . JString::strtolower($this->getName()) . '.js');
    }
}
