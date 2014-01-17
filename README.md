CrowdFunding for Joomla! 
==========================
( Version 1.6 )
- - -

CrowdFunding is a platform that provides functionality for creating collective funding websites, powered by Joomla!

Changelog
-----------

###v1.6

* Improved PayPal payment plugin. 
    * Added options for selecting locale and button type - "buy now", "pay now" and donate.
    * Added event "onTransactionChangeState". 
* Added statistical information.
* Added images to rewards.
* Added new data to the countries.
* Improved payments.
    * Added new events for managing payments.
    * Added event "onTransactionChangeState" to all payment plugins.
* Fixed some issues.

###v1.5

* Added section where the administrator will be able to prepare emails for sending in specific cases.
* Added new options
    * date format
    * display creator
* Added functionality for downloading log files.
* Added CSS classes "cf-project-active" and "cf-project-completed" on views Discover and Featured. They are based on current state (days left) of the project. Designers can use them to customize projects easily, on those pages.
* Added project types.  
* Integrated with EasySocial.
* Improved

###v1.4.4

* Added Logs Manager.
* Added functionality for deleting projects if they have not been funded.
* Improved Locations. Now, you can export states.
* Fixed some issues.

###v1.4.3

* Added wizard type for the payment process - "Three Steps" and "Four Steps".
* Fixed an issue when a project is saved without category. 
* Improved some language strings.

###v1.4.2

* Fixed collation of some columns in table "countries". 

###v1.4.1

* Fixed some issues.
* Improved

###v1.4

* Added a new event "onTransactionChangeStatus" to plugins with type CrowdFundingPayment. Now, when the administrator change the status, that event will be triggered.
* Improved the payment process. Some payment plugins were improved too. 
* It was added feature, anonymous users to be able to donate.
* Added ability for uploading many images to projects.
* CrowdFundingCurrency class was refactored. It was implemented [The NumberFormatter class] (http://www.php.net/manual/en/class.numberformatter.php).
* Fixed some issues.
* Improved

###v1.3.1

* Fixed database query.

###v1.3

* Integrated with JomSocial and Kunena.
* Improved integration.
    * Added option for avatar size.
    * Added option for default avatar picture.
* Added countries and states
* Improved import and export functionality.
* Fixed loading locations lag.
* Now, rewards are optional. You are able to publish project without rewards.
* Improved usability of the wizard used for project creating.
* Fixed [issue #29] (https://github.com/ITPrism/CrowdFunding/issues/29). Now, rewards are set as trashed, it they are part of transaction.
* Removed some plugins from the package. The plugins are "Search - CrowdFunding", "Content - CrowdFunding - Manager" and "CrowdFunding Info". 
* Developed some modules - "CrowdFunding Search", "CrowdFunding Latest", "CrowdFunding Popular". 
* Developed some payment plugins
    * Bank Transfer
    * Mollie iDEAL

NOTE: All new extensions and the removed from the package are available for downloading on [CrowdFunding extension page] (http://itprism.com/free-joomla-extensions/ecommerce-gamification/crowdfunding-collective-raising-capital).

###v1.2.1

* Added to the package the plugins "Content - CrowdFunding - User Mail" and "Content - CrowdFunding - Admin Mail".
* Fixed some issues.

###v1.2

* Added some new options. Now you are able to manage those features better.
    * maximum amount
    * maximum days
    * duration type
    * funding type
* The box with project state information moved to module "CrowdFunding Info"
* The box with rewards moved to module "CrowdFunding Rewards"
* The box with project details moved to module "CrowdFunding Details"
* Added option to set number of project in row.
* Improved responsive design
* [[#12]](https://github.com/ITPrism/CrowdFunding/issues/12 "Date display issue") Fixed the date issue
* Added plugins used for sending mails to administrator and user
    * Content - CrowdFunding - User Mail ( It sends notification mail to the administrator when a user creates or publishes a project. )
    * Content - CrowdFunding - Admin Mail ( It sends notification mail to a user when the administrator approves a project. )
    * Content - CrowdFunding - Manager ( It adds functionality for managing project on details page. It also display statistical information about it. )
* It was added some plugin events
    * onContentAfterSave
    * onContentChangeState
* Added option for Terms Of Use on the page, where user create a project.
* Added rewards manager
* Added filters on backend
* Added filters on discover page
* Now, the owner of the projects can review them even they are not approved.
* Improved the plugin "Content - CrowdFunding - Share"
* Added view "Featured"
* Moved "Discover" options from component config to menu item options.
* Fixed some issues

###v1.1.3

* Fixed date
* Fixed issue with white spaces in payment plugins.
* Added default picture, which will be displayed if missing one.

###v1.1.2

* Added a SEO option for project title.
* Fixed an issue with routing inner categories.
* Fixed some issues

###v1.1.1

* Fixed an issue [[#11]](https://github.com/ITPrism/CrowdFunding/pull/11 "small change to use title for project alias")
* Added option to search plugin that enables or disables searching in area.

###v1.1

* Fixed issue with routing of multilevel categories
* Added option for selection category when adding a menu item.
* Now it works with ITPrism Library 1.2.
* Improved integration. Now it works with Gravatar too.
* Improved routers.
* Improved plugins.
* Improved backend - transactions, projects,...
* Included search plugin ( plg_search_crowdfunding ).
* Included plugin that gives additiona information ( plg_content_crowdfundinginfo ).
* Added avatars to comments, updates and funders list.
* Now, you are able to use Vimeo video.
* Added "Send to Friend" form.
* Fixed some issues.
