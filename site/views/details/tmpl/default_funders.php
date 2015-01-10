<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<?php
if (!empty($this->items)) {
    foreach ($this->items as $item) {

        $socialProfile = (!$this->socialProfiles or !$item->id) ? null : $this->socialProfiles->getLink($item->id);
        $socialAvatar  = (!$this->socialProfiles or !$item->id) ? $this->defaultAvatar : $this->socialProfiles->getAvatar($item->id, $this->avatarsSize);
        $socialLocation  = (!$this->socialProfiles or !$item->id) ? null : $this->socialProfiles->getLocation($item->id);
        $socialCountryCode  = (!$this->socialProfiles or !$item->id) ? null: $this->socialProfiles->getCountryCode($item->id);
        ?>
        <div class="row-fluid">

            <div class="span12 cf-funder-row">

                <div class="media">
                    <a class="pull-left cf-funder-picture"
                       href="<?php echo (!$socialProfile) ? "javascript: void(0);" : $socialProfile; ?>">
                        <img class="media-object" src="<?php echo $socialAvatar; ?>"
                             width="<?php echo $this->avatarsSize; ?>" height="<?php echo $this->avatarsSize; ?>">
                    </a>

                    <div class="media-body">

                        <div class="pull-left cf-funder-info">
                            <h5 class="media-heading">
                                <?php if (!empty($socialProfile)) { ?>
                                    <a href="<?php echo $socialProfile; ?>">
                                        <?php echo $this->escape($item->name); ?>
                                    </a>
                                <?php } else { ?>
                                    <?php echo (!$item->name) ? JText::_("COM_CROWDFUNDING_ANONYMOUS") : $this->escape($item->name); ?>
                                <?php } ?>
                            </h5>
                            <?php echo JHtml::_("crowdfunding.profileLocation", $socialLocation, $socialCountryCode); ?>
                        </div>

                        <?php if(!empty($this->displayAmounts)) { ?>
                        <div class="pull-right cf-funder-amount">
                            <?php echo $this->currency->getAmountString($item->txn_amount); ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>

            </div>

        </div>
    <?php } ?>

<?php } ?>