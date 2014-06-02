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

class CrowdFundingViewReward extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    protected $item;
    protected $form;

    protected $rewardsImagesUri;

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
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        if (!empty($this->item->id)) {
            $userId = CrowdFundingHelper::getUserIdByRewardId($this->item->id);

            $uri = JUri::getInstance();

            $this->rewardsImagesUri = $uri->toString(array("scheme", "host")) . "/" . CrowdFundingHelper::getImagesFolderUri($userId, JPATH_BASE);
        }

        // Prepare actions, behaviors, scritps and document
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
        $isNew = ($this->item->id == 0);

        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_NEW_REWARD')
            : JText::_('COM_CROWDFUNDING_EDIT_REWARD');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('reward.apply');
        JToolbarHelper::save('reward.save');

        if (!$isNew) {
            JToolbarHelper::cancel('reward.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::cancel('reward.cancel', 'JTOOLBAR_CLOSE');
        }
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
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        JHtml::_('formbehavior.chosen', 'select');

        // Add scripts
        $this->document->addScript('../media/' . $this->option . '/js/admin/' . JString::strtolower($this->getName()) . '.js');
    }
}
