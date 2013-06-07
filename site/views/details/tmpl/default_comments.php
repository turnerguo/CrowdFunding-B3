<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
// no direct access

defined('_JEXEC') or die;?>

<?php if($this->userId) { ?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="commentsForm" id="crowdf-comments-form" class="form-validate" autocomplete="off">
        
    <?php echo $this->form->getLabel('comment'); ?>
    <?php echo $this->form->getInput('comment'); ?>
        
    <?php echo $this->form->getInput('id'); ?>
    <?php echo $this->form->getInput('project_id'); ?>
        
    <input type="hidden" name="task" value="comment.save" />
    <?php echo JHtml::_('form.token'); ?>
    
    <div class="clearfix"></div>
    <button type="submit" class="button"><?php echo JText::_("COM_CROWDFUNDING_SEND")?></button>
    <button type="submit" class="button" id="cf-comments-reset"><?php echo JText::_("COM_CROWDFUNDING_RESET")?></button>
    
</form>
<div class="hr margin-tb-15px"></div>
<?php } ?>
<?php if(!empty($this->items)) {
    
    foreach($this->items as $item ) {

        // Do not display items that are not published, it the person is not thier owner.
        if(!$item->published AND ( $item->user_id != $this->userId) ) {
            continue;
        }
        
        $user = JFactory::getUser($item->user_id);
        $socialProfile  = JHtml::_("crowdfunding.socialProfile", $this->socialPlatform, $user);
        $socialAvatar   = JHtml::_("crowdfunding.socialAvatar", $this->avatars, $user, "media/com_crowdfunding/images/no-profile.png");
        
?>
    <div class="row-fluid cf-comment-item" id="comment<?php echo $item->id;?>">
        
        <div class="media">
            <a class="pull-left" href="<?php echo (!$socialProfile) ? "javascript: void(0);" : $socialProfile;?>">
                <img class="media-object" src="<?php echo $socialAvatar;?>">
            </a>
            
            <div class="media-body">
            	<div class="cf-info-bar"> 
            		<div class="pull-left">
            		    <?php if(!$socialProfile) { ?>
            		    <?php echo $item->author; ?>
            		    <?php } else {?>
            		    <a href="<?php echo $socialProfile;?>"><?php echo $item->author; ?></a>
            		    <?php } ?> | <?php echo JHTML::_('date', $item->record_date,JText::_('DATE_FORMAT_LC3')); ?>
            			<?php if(!$item->published AND ( $item->user_id == $this->userId) ) {?>
                    		<p class="message"><?php echo JText::_("COM_CROWDFUNDING_COMMENT_NOT_APPROVED");?></p>
                        <?php }?>
            		</div>
                	<?php if($this->userId == $item->user_id ) {?>
                	<div class="pull-right">
                		<a href="javascript: void(0);" class="btn btn-mini comedit_btn" data-id="<?php echo $item->id;?>"><?php echo JText::_("COM_CROWDFUNDING_EDIT");?></a>
                		<a href="javascript: void(0);" class="btn btn-mini btn-danger comremove_btn" data-id="<?php echo $item->id;?>"><?php echo JText::_("COM_CROWDFUNDING_DELETE");?></a>
                	</div>
                	<?php }?>
                	
                	<div class="clearfix"></div>
            	</div>
            	<p><?php echo nl2br($item->comment);?></p>
        	</div>
    	</div>
    	
    </div>
    
<?php }?>
    
<input type="hidden" value="<?php echo JText::_("COM_CROWDFUNDING_QUESTION_REMOVE_COMMENT");?>" id="cf-hidden-question" />
<?php }?>