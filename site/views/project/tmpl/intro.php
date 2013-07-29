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
defined('_JEXEC') or die;
?>
<?php if(!empty($this->article)) {?>

<?php if($this->params->get("project_intro_article_title", 0)){?>
<h2><?php echo $this->article->title;?></h2>
<?php }?>

<?php echo $this->article->introtext;?>
<?php echo $this->article->fulltext;?>
<?php }?>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>