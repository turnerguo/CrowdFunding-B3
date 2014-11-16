<?php
/**
 * @package      CrowdFunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

?>
<?php if (count($rewards) > 0) { ?>
    <div class="cfrewards<?php echo $moduleclassSfx; ?>">

        <div class="reward_title center"><?php echo JText::_("MOD_CROWDFUNDINGREWARDS_PLEDGE_REWARDS"); ?></div>
        <?php foreach ($rewards as $reward) { ?>
            <div class="reward">
                <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug(), "default", $reward["id"])); ?>">
    			<span class="ramount">
    			<?php
                $amount = $currency->getAmountString($reward["amount"]);
                echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_INVEST_MORE", $amount); ?>
    			</span>
                    <span class="rtitle"><?php echo htmlspecialchars($reward["title"], ENT_QUOTES, "UTF-8"); ?></span>
                    <span class="rdesc"><?php echo htmlspecialchars($reward["description"], ENT_QUOTES, "UTF-8"); ?></span>
                </a>

                <?php if ($additionalInfo) { ?>
                <hr />
                <?php if ($params->get("display_funders", 0)) { ?>
                <div class="cf-rewards-backers"><?php echo JText::plural("MOD_CROWDFUNDINGREWARDS_BACKERS", $reward["funders"]); ?></div>
                <?php } ?>

                <?php if ($params->get("display_claimed", 0) and !empty($reward["distributed"])) { ?>
                <div class="cf-rewards-claimed">
                    <?php
                    if ($reward["distributed"] < $reward["number"]) {
                        echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_CLAIMED", $reward["distributed"], $reward["number"]);
                    } else {
                        echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_CLAIMED_ALL_DONE", $reward["distributed"], $reward["number"]);
                    }
                    ?>
                </div>
                <?php } ?>

                <?php
                if ($params->get("display_delivery_date", 0)) {
                    $deliveryDate = new ITPrismValidatorDate($reward["delivery"]);
                    if ($deliveryDate->isValid()) {
                        echo '<div class="cf-rewards-delivery">' . JText::sprintf("MOD_CROWDFUNDINGREWARDS_ESTIMATED_DELIVERY", JHtml::_('date', $reward["delivery"], JText::_('DATE_FORMAT_LC3'))). '</div>';
                    }
                }?>
                <?php } ?>

            </div>

        <?php } ?>


    </div>
<?php } ?>