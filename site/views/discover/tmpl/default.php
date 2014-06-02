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
$itemSpan = (!empty($this->numberInRow)) ? round(12 / $this->numberInRow) : 4;
?>
<div class="cfdiscover<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>

    <?php if (empty($this->items)) { ?>
        <p class="alert alert-warning"><?php echo JText::_("COM_CROWDFUNDING_NO_ITEMS_MATCHING_QUERY"); ?></p>
    <?php } ?>

    <?php if (!empty($this->items)) { ?>
        <ul class="thumbnails">
            <?php foreach ($this->items as $item) {

                $projectStateCSS = JHtml::_("crowdfunding.styles", $item, $this->params);

                $raised = $this->currency->getAmountString($item->funded);

                // Prepare the value that I am going to display
                $fundedPercents = JHtml::_("crowdfunding.funded", $item->funded_percents);

                // Prepare social profile.
                if (!empty($this->displayCreator)) {
                    $socialProfile = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($item->user_id);
                    $profileName   = JHtml::_("crowdfunding.socialProfileLink", $socialProfile, $item->user_name);
                }
                ?>
                <li class="span<?php echo $itemSpan; ?>">
                    <div class="thumbnail cf-project <?php echo $projectStateCSS; ?> ">
                        <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($item->slug, $item->catslug)); ?>">
                            <?php if (!$item->image) { ?>
                                <img src="<?php echo "media/com_crowdfunding/images/no_image.png"; ?>"
                                     alt="<?php echo $item->title; ?>" width="<?php echo $this->imageWidth; ?>"
                                     height="<?php echo $this->imageHeight; ?>">
                            <?php } else { ?>
                                <img src="<?php echo $this->imageFolder . "/" . $item->image; ?>"
                                     alt="<?php echo $item->title; ?>" width="<?php echo $this->imageWidth; ?>"
                                     height="<?php echo $this->imageHeight; ?>">
                            <?php } ?>
                        </a>

                        <div class="caption">
                            <h3>
                                <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($item->slug, $item->catslug)); ?>">
                                    <?php echo JHtmlString::truncate($item->title, $this->titleLength, true, false); ?>
                                </a>
                            </h3>
                            <?php if (!empty($this->displayCreator)) { ?>
                             <div class="font-xxsmall">
                                <?php echo JText::sprintf("COM_CROWDFUNDING_BY_S", $profileName); ?>
                             </div>
                            <?php } ?>

                            <?php if ($this->params->get("discover_display_description", true)) { ?>
                                <p><?php echo JHtmlString::truncate($item->short_desc, $this->descriptionLength, true, false); ?></p>
                            <?php } ?>
                        </div>

                        <div class="cf-caption-info absolute-bottom">
                            <?php echo JHtml::_("crowdfunding.progressbar", $fundedPercents, $item->days_left, $item->funding_type); ?>
                            <div class="row-fluid">
                                <div class="span4">
                                    <div class="bolder"><?php echo $item->funded_percents; ?>%</div>
                                    <div class="uppercase"><?php echo JText::_("COM_CROWDFUNDING_FUNDED"); ?></div>
                                </div>
                                <div class="span4">
                                    <div class="bolder"><?php echo $raised; ?></div>
                                    <div class="uppercase"><?php echo JText::_("COM_CROWDFUNDING_RAISED"); ?></div>
                                </div>
                                <div class="span4">
                                    <div class="bolder"><?php echo $item->days_left; ?></div>
                                    <div class="uppercase"><?php echo JText::_("COM_CROWDFUNDING_DAYS_LEFT"); ?></div>
                                </div>
                            </div>
                        </div>

                    </div>

                </li>
            <?php } ?>
        </ul>
    <?php } ?>
    <div class="clearfix"></div>

    <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) { ?>
            <p class="counter">
                <?php echo $this->pagination->getPagesCounter(); ?>
            </p>
        <?php } ?>

        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>

    <div class="clearfix">&nbsp;</div>
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>