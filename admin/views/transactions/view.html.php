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
jimport('joomla.application.categories');

class CrowdFundingViewTransactions extends JViewLegacy
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

    protected $items;
    protected $pagination;

    protected $currencies;
    protected $enabledSpeceficPlugins;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
    protected $sortFields;

    protected $sidebar;

    /**
     * Payment plugins, which provides capture and void functionality.
     *
     * @var array
     */
    protected $speceficPlugins = array("paypalexpress");

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params = $this->state->get("params");

        // Get currencies
        foreach ($this->items as $item) {
            $currencies[] = $item->txn_currency;
            $currencies   = array_unique($currencies);
        }

        if (!empty($currencies)) {
            jimport("crowdfunding.currencies");
            $this->currencies = new CrowdFundingCurrencies(JFactory::getDbo());
            $this->currencies->loadByAbbr($currencies);
        }

        // Get enabled specefic plugins.
        jimport("itprism.extensions");
        $extensions                   = new ITPrismExtensions(JFactory::getDbo(), $this->speceficPlugins);
        $this->enabledSpeceficPlugins = $extensions->getEnabled();

        // Add submenu
        CrowdFundingHelper::addSubmenu($this->getName());

        // Prepare sorting data
        $this->prepareSorting();

        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Prepare sortable fields, sort values and filters.
     */
    protected function prepareSorting()
    {
        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

        if ($this->saveOrder) {
            $this->saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName() . 'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }

        $this->sortFields = array(
            'b.name'             => JText::_('COM_CROWDFUNDING_BENEFICIARY'),
            'e.name'             => JText::_('COM_CROWDFUNDING_SENDER'),
            'c.title'            => JText::_('COM_CROWDFUNDING_PROJECT'),
            'a.txn_amount'       => JText::_('COM_CROWDFUNDING_AMOUNT'),
            'a.txn_date'         => JText::_('COM_CROWDFUNDING_DATE'),
            'a.service_provider' => JText::_('COM_CROWDFUNDING_PAYMENT_GETAWAY'),
            'a.id'               => JText::_('JGRID_HEADING_ID')
        );
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        // Create object Filters and load some filters options.
        jimport("crowdfunding.filters");
        $filters = new CrowdFundingFilters(JFactory::getDbo());

        // Get payment services.
        $paymentServices = $filters->getPaymentServices();
        JHtmlSidebar::addFilter(
            JText::_('COM_CROWDFUNDING_SELECT_PAYMENT_SERVICES'),
            'filter_payment_service',
            JHtml::_('select.options', $paymentServices, 'value', 'text', $this->state->get('filter.payment_service'), true)
        );

        // Get payment statuses.
        $paymentStatuses = $filters->getPaymentStatuses();
        JHtmlSidebar::addFilter(
            JText::_('COM_CROWDFUNDING_SELECT_PAYMENT_STATUS'),
            'filter_payment_status',
            JHtml::_('select.options', $paymentStatuses, 'value', 'text', $this->state->get('filter.payment_status'), true)
        );

        // Get reward states.
        $rewardDistributionStatuses = $filters->getRewardDistributionStatuses();
        JHtmlSidebar::addFilter(
            JText::_('COM_CROWDFUNDING_SELECT_REWARD_STATUS'),
            'filter_reward_state',
            JHtml::_('select.options', $rewardDistributionStatuses, 'value', 'text', $this->state->get('filter.reward_state'), true)
        );

        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_TRANSACTIONS_MANAGER'));
        JToolbarHelper::editList('transaction.edit');

        // Add actions used for specefic payment plugins.
        if (!empty($this->enabledSpeceficPlugins)) {
            JToolbarHelper::divider();

            // Add custom buttons
            $bar = JToolbar::getInstance('toolbar');
            $bar->appendButton('Confirm', JText::_("COM_CROWDFUNDING_QUESTION_CAPTURE"), 'checkin', JText::_("COM_CROWDFUNDING_CAPTURE"), 'payments.docapture', true);
            $bar->appendButton('Confirm', JText::_("COM_CROWDFUNDING_QUESTION_VOID"), 'cancel-circle', JText::_("COM_CROWDFUNDING_VOID"), 'payments.dovoid', true);
        }

        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_("COM_CROWDFUNDING_DELETE_ITEMS_QUESTION"), "transactions.delete");
        JToolbarHelper::divider();
        JToolbarHelper::custom('transactions.backToDashboard', "dashboard", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_TRANSACTIONS_MANAGER'));

        // Scripts
        JHtml::_('behavior.multiselect');

        JHtml::_('bootstrap.tooltip');
        JHtml::_('formbehavior.chosen', 'select');

        JHtml::_('itprism.ui.joomla_list');
    }
}
