<?php
/**
 * @package      CrowdFunding
 * @subpackage   Version
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides information about extension version.
 *
 * @package      CrowdFunding
 * @subpackage   Version
 */
class CrowdFundingVersion
{
    /**
     * Extension name
     *
     * @var string
     */
    public $product = 'CrowdFunding';

    /**
     * Main Release Level
     *
     * @var integer
     */
    public $release = '1';

    /**
     * Sub Release Level
     *
     * @var integer
     */
    public $devLevel = '8.1';

    /**
     * Release Type
     *
     * @var integer
     */
    public $releaseType = 'Lite';

    /**
     * Development Status
     *
     * @var string
     */
    public $devStatus = 'Stable';

    /**
     * Date
     *
     * @var string
     */
    public $releaseDate = '01 October, 2014';

    /**
     * License
     *
     * @var string
     */
    public $license = '<a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GNU/GPL</a>';

    /**
     * Copyright Text
     *
     * @var string
     */
    public $copyright = '&copy; 2014 ITPrism. All rights reserved.';

    /**
     * URL to the extension page.
     *
     * @var string
     */
    public $url = '<a href="http://itprism.com/free-joomla-extensions/ecommerce-gamification/crowdfunding-collective-raising-capital" target="_blank">CrowdFunding</a>';

    /**
     * Backlink of the extension.
     *
     * @var string
     */
    public $backlink = '<div style="width:100%; text-align: left; font-size: xx-small; margin-top: 10px;"><a href="http://itprism.com/free-joomla-extensions/ecommerce-gamification/crowdfunding-collective-raising-capital" target="_blank">Joomla! crowdfunding</a></div>';

    /**
     * Developer website.
     *
     * @var string
     */
    public $developer = '<a href="http://itprism.com" target="_blank">ITPrism</a>';

    /**
     * Build long format of the version text.
     *
     * @return string Long format version.
     */
    public function getLongVersion()
    {
        return
            $this->product . ' ' . $this->release . '.' . $this->devLevel . ' ' .
            $this->devStatus . ' ' . $this->releaseDate;
    }

    /**
     * Build medium format of the version text.
     *
     * @return string Medium format version.
     */
    public function getMediumVersion()
    {
        return
            $this->release . '.' . $this->devLevel . ' ' .
            $this->releaseType . ' ( ' . $this->devStatus . ' )';
    }

    /**
     *  Build short format of the version text.
     *
     * @return string Short version format.
     */
    public function getShortVersion()
    {
        return $this->release . '.' . $this->devLevel;
    }
}
