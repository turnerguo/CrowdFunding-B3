<?php
/**
 * @package      CrowdFunding
 * @subpackage   Constants
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * CrowdFunding constants
 *
 * @package      CrowdFunding
 * @subpackage   Constants
 */
class CrowdFundingConstants
{
    // Payment session
    const PAYMENT_SESSION_CONTEXT = "payment_session_project";
    const CROPPED_IMAGES_CONTEXT  = "cropped_images_project";
    const TEMPORARY_IMAGE_CONTEXT = "temporary_image_project";

    // States
    const PUBLISHED   = 1;
    const UNPUBLISHED = 0;
    const TRASHED     = -2;

    // Mail modes - html and plain text.
    const MAIL_MODE_HTML  = true;
    const MAIL_MODE_PLAIN = false;

    // Logs
    const ENABLE_SYSTEM_LOG  = true;
    const DISABLE_SYSTEM_LOG = false;

    // Project states
    const APPROVED     = 1;
    const NOT_APPROVED = 0;

    // Other states
    const SENT     = 1;
    const NOT_SENT = 0;

    // Filters
    const FILTER_STARTED_SOON = 1;
    const FILTER_ENDING_SOON = 2;
    const FILTER_SUCCESSFULLY_COMPLETED = 1;

    // Featured
    const FEATURED = 1;
    const NOT_FEATURED = 0;

    // Ordering
    const ORDER_BY_ORDERING = 0;
    const ORDER_BY_NAME = 1;
    const ORDER_BY_CREATED_DATE = 2;
    const ORDER_BY_START_DATE = 3;
    const ORDER_BY_END_DATE = 4;
    const ORDER_BY_POPULARITY = 5;
    const ORDER_BY_FUNDING = 6;
    const ORDER_BY_FANS = 7;

    const ORDER_BY_LOCATION_NAME = 10;
    const ORDER_BY_NUMBER_OF_PROJECTS = 20;

    // Categories
    const CATEGORY_ROOT = 1;
}
