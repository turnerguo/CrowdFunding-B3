<?php
/**
 * @package      CrowdFunding\Projects
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_BASE') or die;

/**
 * This class provides functionality for validation project owner.
 *
 * @package      CrowdFunding\Projects
 * @subpackage   Validators
 */
class CrowdFundingValidatorProjectOwner implements ITPrismValidatorInterface
{
    protected $db;
    protected $projectId;
    protected $userId;

    /**
     * Initialize the object.
     *
     * <code>
     * $projectId = 1;
     * $userId = 2;
     *
     * $owner = new CrowdFundingValidatorProjectOwner(JFactory::getDbo(), $projectId, $userId);
     * </code>
     *
     * @param JDatabaseDriver $db        Database object.
     * @param int             $projectId Project ID.
     * @param int             $userId    User ID.
     */
    public function __construct(JDatabaseDriver $db, $projectId, $userId)
    {
        $this->db        = $db;
        $this->projectId = $projectId;
        $this->userId    = $userId;
    }

    /**
     * Validate reward owner.
     *
     * <code>
     * $projectId = 1;
     * $userId = 2;
     *
     * $owner = new CrowdFundingValidatorProjectOwner(JFactory::getDbo(), $projectId, $userId);
     * if(!$owner->isValid()) {
     * ......
     * }
     * </code>
     *
     * @return bool
     */
    public function isValid()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = " . (int)$this->projectId)
            ->where("a.user_id = " . (int)$this->userId);

        $this->db->setQuery($query, 0, 1);
        $result = $this->db->loadResult();

        return (bool)$result;
    }
}
