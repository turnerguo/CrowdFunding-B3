<?php
/**
 * @package         CrowdFunding
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

jimport('itprism.init');
jimport('crowdfunding.init');

/**
 * This plugin send notification mails to the administrator.
 *
 * @package        CrowdFunding
 * @subpackage     Plugins
 */
class plgContentCrowdFundingAdminMail extends JPlugin
{
    /**
     * @var ITPrismLog
     */
    protected $log;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    public function init()
    {
        // Prepare log object
        $registry = JRegistry::getInstance("com_crowdfunding");
        /** @var $registry Joomla\Registry\Registry */

        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");

        // Create log object
        $this->log = new ITPrismLog();

        // Set database writer.
        $this->log->addWriter(new ITPrismLogWriterDatabase(JFactory::getDbo(), $tableName));

        // Set file writer.
        if (!empty($fileName)) {
            $file = JPath::clean(JFactory::getApplication()->get("log_path") . DIRECTORY_SEPARATOR . $fileName);
            $this->log->addWriter(new ITPrismLogWriterFile($file));
        }

        // Load language
        $this->loadLanguage();
    }

    /**
     * This method sends notification mail to the administrator when project owner publish a project.
     *
     * @param string $context
     * @param array $ids
     * @param int $state
     *
     * @return bool|null
     */
    public function onContentChangeState($context, $ids, $state)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        if (strcmp("com_crowdfunding.project", $context) != 0) {
            return null;
        }

        // Initialize plugin
        $this->init();

        // Check for enabled option for sending mail.
        // when user publish a project.
        $emailId = $this->params->get("send_when_published", 0);
        if (!$emailId) {
            return null;
        }

        JArrayHelper::toInteger($ids);

        if (!empty($ids) and $state == CrowdFundingConstants::PUBLISHED) {

            $projects = $this->getProjectsData($ids);

            if (!$projects) {
                return false;
            }

            // Load class CrowdFundingEmail.
            jimport("crowdfunding.email");

            foreach ($projects as $project) {

                // Send email to the administrator.
                $result = $this->sendMail($project, $emailId);

                // Check for error.
                if ($result !== true) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * This method sends notification mail to the administrator when someone create a project.
     *
     * @param string                   $context
     * @param CrowdFundingTableProject $project
     * @param boolean                  $isNew
     *
     * @return null|boolean
     */
    public function onContentAfterSave($context, &$project, $isNew)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        if (strcmp("com_crowdfunding.project", $context) != 0) {
            return null;
        }

        // Initialize plugin
        $this->init();

        // Check for enabled option for sending mail
        // when user create a project.
        $emailId = $this->params->get("send_when_create", 0);
        if (!$emailId) {
            return null;
        }

        if (!empty($project->id) and $isNew) {

            // Load class CrowdFundingEmail.
            jimport("crowdfunding.email");

            // Send email to the administrator.
            $return = $this->sendMail($project, $emailId);

            // Check for error.
            if ($return !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Load data about projects
     *
     * @param array $ids
     *
     * @return array
     */
    private function getProjectsData($ids)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select("a.title");
        $query->select($query->concatenate(array("a.id", "a.alias"), "-") . " AS slug");
        $query->select($query->concatenate(array("b.id", "b.alias"), "-") . " AS catslug");

        $query
            ->from($db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($db->quoteName("#__categories", "b") . " ON a.catid = b.id")
            ->where("a.id IN (" . implode(",", $ids) . ")");

        $db->setQuery($query);
        $results = $db->loadObjectList();

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    protected function sendMail($project, $emailId)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Send mail to the administrator
        if (!$emailId) {
            return false;
        }

        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));

        $emailMode = $this->params->get("email_mode", "plain");

        // Prepare data for parsing
        $data = array(
            "site_name"  => $app->get("sitename"),
            "site_url"   => JUri::root(),
            "item_title" => $project->title,
            "item_url"   => $website . JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($project->slug, $project->catslug)),
        );

        $email = new CrowdFundingEmail();
        $email->setDb(JFactory::getDbo());
        $email->load($emailId);

        if (!$email->getSenderName()) {
            $email->setSenderName($app->get("fromname"));
        }
        if (!$email->getSenderEmail()) {
            $email->setSenderEmail($app->get("mailfrom"));
        }

        // Prepare recipient data.
        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        $recipientId = $componentParams->get("administrator_id");
        if (!empty($recipientId)) {
            $recipient = JFactory::getUser($recipientId);
            $recipientName = $recipient->get("name");
            $recipientMail = $recipient->get("email");
        } else {
            $recipientName = $app->get("fromname");
            $recipientMail = $app->get("mailfrom");
        }

        // Prepare data for parsing
        $data["sender_name"]     = $email->getSenderName();
        $data["sender_email"]    = $email->getSenderEmail();
        $data["recipient_name"]  = $recipientName;
        $data["recipient_email"] = $recipientMail;

        $email->parse($data);
        $subject = $email->getSubject();
        $body    = $email->getBody($emailMode);

        $mailer = JFactory::getMailer();
        if (strcmp("html", $emailMode) == 0) { // Send as HTML message
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingConstants::MAIL_MODE_HTML);
        } else { // Send as plain text.
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, CrowdFundingConstants::MAIL_MODE_PLAIN);
        }

        // Log the error.
        if ($result !== true) {
            $this->log->add(
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_SEND_MAIL_ADMIN", $mailer->ErrorInfo),
                "PLG_CONTENT_ADMIN_EMAIL_ERROR"
            );

            return false;
        }

        return true;
    }
}
