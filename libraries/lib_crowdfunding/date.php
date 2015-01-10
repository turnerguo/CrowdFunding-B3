<?php
/**
 * @package      CrowdFunding
 * @subpackage   Dates
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This is a class that provides functionality for managing dates.
 *
 * @package      CrowdFunding
 * @subpackage   Dates
 */
class CrowdFundingDate extends ITPrismDate
{
    /**
     * Calculate days left.
     *
     * <code>
     * $fundingDays = 30;
     * $fundingStart = "01-06-2014";
     * $fundingEnd   = "30-06-2014";
     *
     * $today    = new CrowdFundingDate();
     * $daysLeft = $today->calculateDaysLeft($fundingDays, $fundingStart, $fundingEnd);
     * </code>
     *
     * @param int    $fundingDays
     * @param string $fundingStart
     * @param string $fundingEnd
     *
     * @return int
     */
    public function calculateDaysLeft($fundingDays, $fundingStart, $fundingEnd)
    {
        // Calculate days left
        $today = clone $this;

        if (!empty($fundingDays)) {

            $validatorDate = new ITPrismValidatorDate($fundingStart);

            // Validate starting date.
            // If there is not starting date, set number of day.
            if (!$validatorDate->isValid($fundingStart)) {
                return (int)$fundingDays;
            }

            $endingDate = new DateTime($fundingStart);
            $endingDate->modify("+" . (int)$fundingDays . " days");

        } else {
            $endingDate = new DateTime($fundingEnd);
        }

        $interval = $today->diff($endingDate);
        $daysLeft = $interval->format("%r%a");

        if ($daysLeft < 0) {
            $daysLeft = 0;
        }

        return abs($daysLeft);
    }

    /**
     * Validate funding period.
     *
     * <code>
     * $fundingEndDate = "04-02-2014";
     * $minDays = 15;
     * $maxDays = 30;
     *
     * $dateValidator = new CrowdFundingDate($item->funding_start);
     * if (!$dateValidator->isValidPeriod($fundingEndDate, $minDays, $maxDays)) {
     * ...
     * }
     * </code>
     *
     * @param string $fundingEnd
     * @param int $minDays
     * @param int $maxDays
     *
     * @return bool
     */
    public function isValidPeriod($fundingEnd, $minDays, $maxDays)
    {
        // Funding start date
        $date         = clone $this;
        $fundingStart = $date->format("Y-m-d");

        // Funding end date
        $date         = new JDate($fundingEnd);
        $fundingEnd   = $date->format("Y-m-d");


        // Get interval between starting and ending date
        $startingDate = new JDate($fundingStart);
        $endingDate   = new JDate($fundingEnd);
        $interval     = $startingDate->diff($endingDate);

        $days = $interval->format("%r%a");

        // Validate minimum dates
        if ($days < $minDays) {
            return false;
        }

        if (!empty($maxDays) and $days > $maxDays) {
            return false;
        }

        return true;
    }
}
