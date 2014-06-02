<?php
/**
 * @package      CrowdFunding\Images
 * @subpackage   Validator
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_BASE') or die;

JLoader::register(
    "ITPrismFileInterfaceValidator",
    JPATH_LIBRARIES . DIRECTORY_SEPARATOR . "itprism" . DIRECTORY_SEPARATOR . "file" . DIRECTORY_SEPARATOR . "interface" . DIRECTORY_SEPARATOR . "validator.php"
);

/**
 * This class provides functionality validation image owner.
 *
 * @package      CrowdFunding\Images
 * @subpackage   Validator
 */
class CrowdFundingImageValidatorOwner implements ITPrismFileInterfaceValidator
{
    protected $db;
    protected $imageId;
    protected $userId;

    /**
     * Initialize the object.
     *
     * <code>
     * $imageId = 1;
     * $userId = 2;
     *
     * $image   = new CrowdFundingImageValidatorOwner(JFactory::getDbo(), $image, $userId);
     * </code>
     *
     * @param JDatabaseDriver $db Database object.
     * @param int $imageId Image ID.
     * @param int $userId User ID.
     */
    public function __construct($db, $imageId, $userId)
    {
        $this->db      = $db;
        $this->imageId = $imageId;
        $this->userId  = $userId;
    }

    /**
     * Validate image owner.
     *
     * <code>
     * $imageId = 1;
     * $userId = 2;
     *
     * $image   = new CrowdFundingImageValidatorOwner(JFactory::getDbo(), $image, $userId);
     * $image->validate();
     * </code>
     *
     * @throws RuntimeException
     */
    public function validate()
    {
        $subQuery = $this->db->getQuery(true);
        $subQuery
            ->select("b.project_id")
            ->from($this->db->quoteName("#__crowdf_images", "b"))
            ->where("b.id = " . (int)$this->imageId);

        $query = $this->db->getQuery(true);
        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = (" . $subQuery . ")")
            ->where("a.user_id = " . (int)$this->userId);

        $this->db->setQuery($query, 0, 1);
        $result = $this->db->loadResult();

        if (!$result) {
            throw new RuntimeException(JText::_("LIB_CROWDFUNDING_INVALID_PROJECT"));
        }
    }
}
