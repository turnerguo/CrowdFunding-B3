<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class CrowdFundingViewDashboard extends JViewLegacy
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

    protected $option;

    protected $popular;
    protected $mostFunded;
    protected $latestStarted;
    protected $latestCreated;
    protected $currency;
    protected $version;
    protected $itprismVersion;

    protected $sidebar;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state  = $this->get("State");
        $this->params = $this->state->get("params");

        $this->version = new CrowdFundingVersion();

        // Load ITPrism library version
        jimport("itprism.version");
        if (!class_exists("ITPrismVersion")) {
            $this->itprismVersion = JText::_("COM_CROWDFUNDING_ITPRISM_LIBRARY_DOWNLOAD");
        } else {
            $itprismVersion       = new ITPrismVersion();
            $this->itprismVersion = $itprismVersion->getShortVersion();
        }

        // Get popular projects.
        jimport("crowdfunding.statistics.projects.popular");
        $this->popular = new CrowdFundingStatisticsProjectsPopular(JFactory::getDbo());
        $this->popular->load(5);

        // Get popular most funded.
        jimport("crowdfunding.statistics.projects.mostfunded");
        $this->mostFunded = new CrowdFundingStatisticsProjectsMostFunded(JFactory::getDbo());
        $this->mostFunded->load(5);

        // Get latest started.
        jimport("crowdfunding.statistics.projects.latest");
        $this->latestStarted = new CrowdFundingStatisticsProjectsLatest(JFactory::getDbo());
        $this->latestStarted->load(5);

        // Get latest created.
        $this->latestCreated = new CrowdFundingStatisticsProjectsLatest(JFactory::getDbo());
        $this->latestCreated->loadByCreated(5);

        // Get currency.
        jimport("crowdfunding.currency");
        $currencyId = $this->params->get("project_currency");
        $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId, $this->params);

        // Add submenu
        CrowdFundingHelper::addSubmenu($this->getName());

        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JToolbarHelper::title(JText::_("COM_CROWDFUNDING_DASHBOARD"));

        JToolbarHelper::preferences('com_crowdfunding');
        JToolbarHelper::divider();

        // Help button
        $bar = JToolbar::getInstance('toolbar');
        $bar->appendButton('Link', 'help', JText::_('JHELP'), JText::_('COM_CROWDFUNDING_HELP_URL'));
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_DASHBOARD'));
    }
}
