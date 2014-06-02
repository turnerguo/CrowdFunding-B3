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

jimport('joomla.application.component.modelitem');

class CrowdFundingModelProjectItem extends JModelItem
{
    protected $item = array();

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdFundingTableProject  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdFundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     */
    protected function populateState()
    {
        $app    = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $params = $app->getParams();

        // Set the parameters.
        $this->setState('params', $params);
    }

    /**
     * Method to get an object.
     *
     * @param    integer   $id
     * @param    integer   $userId
     *
     * @return    CrowdFundingTableProject|null
     */
    public function getItem($id, $userId)
    {
        $storedId = $this->getStoreId($id.$userId);

        if (!isset($this->item[$storedId])) {

            $keys = array(
                "id" => $id,
                "user_id" => $userId
            );

            // Get a level row instance.
            $table = $this->getTable();
            $table->load($keys);

            // Convert to the JObject before adding other data.
            $properties = $table->getProperties();
            $this->item[$storedId] = JArrayHelper::toObject($properties, 'JObject');
        }

        return $this->item[$storedId];
    }

    /**
     * Publish or not an item. If state is going to be published,
     * we have to calculate end date.
     *
     * @param integer $itemId
     * @param integer $userId
     * @param integer $state
     *
     * @throws Exception
     */
    public function saveState($itemId, $userId, $state)
    {
        $keys = array(
            "id"      => $itemId,
            "user_id" => $userId
        );

        /** @var $row CrowdFundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        // Prepare data only if the user publish the project.
        if ($state == CrowdFundingConstants::PUBLISHED) {
            $this->prepareTable($row);
        }

        $row->set("published", (int)$state);
        $row->store();

        // Trigger the event

        $context = $this->option . '.project';
        $pks     = array($row->get("id"));

        // Include the content plugins for the change of state event.
        JPluginHelper::importPlugin('content');

        // Trigger the onContentChangeState event.
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger("onContentChangeState", array($context, $pks, $state));

        if (in_array(false, $results, true)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_CHANGE_STATE"));
        }

    }

    /**
     * This method calculate start date and validate funding period.
     *
     * @param CrowdFundingTableProject $table
     *
     * @throws Exception
     */
    protected function prepareTable(&$table)
    {
        // Calculate start and end date if the user publish a project for first time.
        $fundingStartDate = new ITPrismValidatorDate($table->funding_start);
        if (!$fundingStartDate->isValid($table->funding_start)) {

            $fundindStart         = new JDate();
            $table->funding_start = $fundindStart->toSql();

            // If funding type is "days", calculate end date.
            if ($table->get("funding_days")) {
                $fundingStartDate = new CrowdFundingDate($table->get("funding_start"));
                $endDate = $fundingStartDate->calculateEndDate($table->get("funding_days"));
                $table->set("funding_end", $endDate->format("Y-m-d"));
            }

        }

        // Get parameters
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $params = $app->getParams();
        /** @var  $params Joomla\Registry\Registry */

        $minDays = $params->get("project_days_minimum", 15);
        $maxDays = $params->get("project_days_maximum");

        // If there is an ending date, validate the period.
        $fundingEndDate = new ITPrismValidatorDate($table->get("funding_end"));
        if ($fundingEndDate->isValid()) {

            $fundingStartDate = new CrowdFundingDate($table->get("funding_start"));
            if (!$fundingStartDate->isValidPeriod($table->get("funding_end"), $minDays, $maxDays)) {

                if (!empty($maxDays)) {
                    throw new RuntimeException(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS", $minDays, $maxDays));
                } else {
                    throw new RuntimeException(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS", $minDays));
                }
            }

        }

    }

    /**
     * This method counts the rewards of the project.
     *
     * @param  integer $itemId Project id
     *
     * @return number
     */
    protected function countRewards($itemId)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__crowdf_rewards", "a"))
            ->where("a.project_id = " . (int)$itemId);

        $db->setQuery($query);
        $result = $db->loadResult();

        return (int)$result;
    }
}
