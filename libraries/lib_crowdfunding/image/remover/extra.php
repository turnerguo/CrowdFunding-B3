<?php
/**
 * @package      CrowdFunding\Images
 * @subpackage   Remover
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_BASE') or die;

JLoader::register("ITPrismFileInterfaceValidator", JPATH_LIBRARIES . DIRECTORY_SEPARATOR . "itprism" . DIRECTORY_SEPARATOR . "file" . DIRECTORY_SEPARATOR . "interface" . DIRECTORY_SEPARATOR . "remover.php");

/**
 * This class provides functionality for removing extra image from database.
 *
 * @package      CrowdFunding\Images
 * @subpackage   Removers
 */
class CrowdFundingImageRemoverExtra implements ITPrismFileInterfaceRemover
{
    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    protected $imageId;
    protected $imagesFolder;

    /**
     * Initialize the object.
     *
     * <code>
     * $imageId = 1;
     * $imagesFolder = "/.../folder";
     *
     * $image   = new CrowdFundingImageRemoverExtra(JFactory::getDbo(), $image, $imagesFolder);
     * $image->remove();
     * </code>
     *
     * @param JDatabaseDriver $db Database object.
     * @param int $imageId Image ID.
     * @param string $imagesFolder A path to the file.
     */
    public function __construct($db, $imageId, $imagesFolder)
    {
        $this->db           = $db;
        $this->imageId      = $imageId;
        $this->imagesFolder = $imagesFolder;
    }

    /**
     * Remove an extra image from database and file system.
     *
     * <code>
     * $imageId = 1;
     * $imagesFolder = "/.../folder";
     *
     * $image   = new CrowdFundingImageRemoverExtra(JFactory::getDbo(), $image, $imagesFolder);
     * $image->remove();
     * </code>
     */
    public function remove()
    {
        // Get the image
        $query = $this->db->getQuery(true);
        $query
            ->select("a.image, a.thumb")
            ->from($this->db->quoteName("#__crowdf_images", "a"))
            ->where("a.id = " . (int)$this->imageId);

        $this->db->setQuery($query);
        $row = $this->db->loadObject();

        if (!empty($row)) {

            // Remove the image from the filesystem
            $file = JPath::clean($this->imagesFolder . DIRECTORY_SEPARATOR . $row->image);
            if (JFile::exists($file)) {
                JFile::delete($file);
            }

            // Remove the thumbnail from the filesystem
            $file = JPath::clean($this->imagesFolder . DIRECTORY_SEPARATOR . $row->thumb);
            if (JFile::exists($file)) {
                JFile::delete($file);
            }

            // Delete the record
            $query = $this->db->getQuery(true);
            $query
                ->delete($this->db->quoteName("#__crowdf_images"))
                ->where($this->db->quoteName("id") . " = " . (int)$this->imageId);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }
}
